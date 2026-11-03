package com.kpr.portal;

import javax.servlet.ServletContext;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/** Opens a JDBC connection using the settings stored in web.xml (context-params). */
public final class DBUtil {
    private DBUtil() {}

    public static Connection getConnection(ServletContext ctx) throws SQLException {
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");   // load MySQL driver
        } catch (ClassNotFoundException e) {
            throw new SQLException("MySQL JDBC driver not found", e);
        }
        return DriverManager.getConnection(
                ctx.getInitParameter("jdbcUrl"),
                ctx.getInitParameter("dbUser"),
                ctx.getInitParameter("dbPassword"));
    }
}
