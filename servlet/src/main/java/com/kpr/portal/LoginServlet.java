package com.kpr.portal;

import javax.servlet.ServletException;
import javax.servlet.http.*;
import java.io.IOException;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.sql.*;

/** Handles POST /login : checks username + SHA-256 password hash against the users table. */
public class LoginServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws IOException, ServletException {
        String username = req.getParameter("username");
        String password = req.getParameter("password");

        if (username == null || password == null || username.isBlank() || password.isEmpty()) {
            fail(req, resp, "Username and password are required");
            return;
        }

        // PreparedStatement => the '?' placeholders prevent SQL injection
        String sql = "SELECT id FROM users WHERE username = ? AND password_hash = ?";
        try (Connection con = DBUtil.getConnection(getServletContext());
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setString(1, username.trim());
            ps.setString(2, sha256(password));

            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    // Discard any old session (prevents session fixation), then start a fresh one
                    HttpSession old = req.getSession(false);
                    if (old != null) old.invalidate();
                    HttpSession session = req.getSession(true);
                    session.setAttribute("user", username.trim());
                    session.setMaxInactiveInterval(15 * 60);        // 15 minutes
                    resp.sendRedirect(req.getContextPath() + "/students");
                } else {
                    fail(req, resp, "Invalid username or password");
                }
            }
        } catch (SQLException e) {
            throw new ServletException("Database error during login", e);
        }
    }

    private void fail(HttpServletRequest req, HttpServletResponse resp, String msg) throws IOException {
        resp.sendRedirect(req.getContextPath() + "/login.jsp?msg=" + URLEncoder.encode(msg, StandardCharsets.UTF_8));
    }

    /** Returns the lowercase hex SHA-256 of the text (same format stored in schema.sql). */
    static String sha256(String text) {
        try {
            byte[] d = MessageDigest.getInstance("SHA-256").digest(text.getBytes(StandardCharsets.UTF_8));
            StringBuilder sb = new StringBuilder();
            for (byte b : d) sb.append(String.format("%02x", b));
            return sb.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new IllegalStateException(e);
        }
    }
}
