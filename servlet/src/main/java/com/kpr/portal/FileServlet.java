package com.kpr.portal;

import javax.servlet.ServletException;
import javax.servlet.http.*;
import java.io.*;
import java.nio.file.*;
import java.sql.*;

/**
 * GET /file?id=NN : streams a stored image/document to the browser.
 * Used by <img src="file?id=NN"> and by download links in the JSP.
 * The browser never sees the real disk path - only the database id.
 */
public class FileServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        int id;
        try {
            id = Integer.parseInt(req.getParameter("id"));
        } catch (NumberFormatException e) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid file id");
            return;
        }

        String storedName = null, mime = null, originalName = null;
        try (Connection con = DBUtil.getConnection(getServletContext());
             PreparedStatement ps = con.prepareStatement(
                     "SELECT file_path, file_type, original_name FROM student_files WHERE id = ?")) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    storedName = rs.getString("file_path");
                    mime = rs.getString("file_type");
                    originalName = rs.getString("original_name");
                }
            }
        } catch (SQLException e) {
            throw new ServletException("Database error", e);
        }

        if (storedName == null) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND, "No such file record");
            return;
        }

        // Path.getFileName() strips any "../" so a tampered DB value cannot escape the upload folder
        Path dir = Paths.get(getServletContext().getInitParameter("uploadDir")).toAbsolutePath().normalize();
        Path file = dir.resolve(Paths.get(storedName).getFileName()).normalize();

        if (!file.startsWith(dir) || !Files.isRegularFile(file)) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND, "File missing on disk");
            return;
        }

        resp.setContentType(mime);
        resp.setContentLengthLong(Files.size(file));
        // inline => display in the browser (images, PDFs); filename used if the user saves it
        resp.setHeader("Content-Disposition",
                "inline; filename=\"" + originalName.replaceAll("[\\r\\n\"]", "_") + "\"");
        resp.setHeader("X-Content-Type-Options", "nosniff");

        // Copy the file to the response in 8 KB chunks
        try (InputStream in = Files.newInputStream(file); OutputStream out = resp.getOutputStream()) {
            byte[] buf = new byte[8192];
            int n;
            while ((n = in.read(buf)) != -1) out.write(buf, 0, n);
        }
    }
}
