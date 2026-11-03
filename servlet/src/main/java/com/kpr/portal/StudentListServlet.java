package com.kpr.portal;

import javax.servlet.ServletException;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.util.*;

/**
 * GET /students : loads every student and their files with JDBC,
 * puts the list in the request, and forwards to the JSP that displays it.
 */
public class StudentListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String q = req.getParameter("q");                      // optional search text
        boolean hasQuery = q != null && !q.isBlank();

        // LinkedHashMap keeps students in query order, lookup by id is O(1)
        Map<Integer, Student> byId = new LinkedHashMap<>();

        String studentSql = "SELECT id, name, roll_no, email, course FROM students"
                + (hasQuery ? " WHERE name LIKE ? OR roll_no LIKE ?" : "") + " ORDER BY roll_no";
        String fileSql = "SELECT id, student_id, category, original_name, file_type, file_size, uploaded_at "
                + "FROM student_files ORDER BY uploaded_at DESC";

        try (Connection con = DBUtil.getConnection(getServletContext())) {

            // 1) students
            try (PreparedStatement ps = con.prepareStatement(studentSql)) {
                if (hasQuery) {
                    ps.setString(1, "%" + q.trim() + "%");
                    ps.setString(2, "%" + q.trim() + "%");
                }
                try (ResultSet rs = ps.executeQuery()) {
                    while (rs.next()) {
                        Student s = new Student();
                        s.setId(rs.getInt("id"));
                        s.setName(rs.getString("name"));
                        s.setRollNo(rs.getString("roll_no"));
                        s.setEmail(rs.getString("email"));
                        s.setCourse(rs.getString("course"));
                        byId.put(s.getId(), s);
                    }
                }
            }

            // 2) their files, attached to the right student
            try (PreparedStatement ps = con.prepareStatement(fileSql);
                 ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    Student owner = byId.get(rs.getInt("student_id"));
                    if (owner == null) continue;               // student filtered out by search
                    StudentFile f = new StudentFile();
                    f.setId(rs.getInt("id"));
                    f.setCategory(rs.getString("category"));
                    f.setOriginalName(rs.getString("original_name"));
                    f.setFileType(rs.getString("file_type"));
                    f.setFileSize(rs.getInt("file_size"));
                    f.setUploadedAt(rs.getTimestamp("uploaded_at"));
                    owner.getFiles().add(f);
                }
            }
        } catch (SQLException e) {
            throw new ServletException("Could not load students", e);
        }

        req.setAttribute("students", new ArrayList<>(byId.values()));
        req.setAttribute("q", hasQuery ? q.trim() : "");
        req.getRequestDispatcher("/WEB-INF/views/students.jsp").forward(req, resp);
    }
}
