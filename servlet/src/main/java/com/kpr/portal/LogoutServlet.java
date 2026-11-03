package com.kpr.portal;

import javax.servlet.http.*;
import java.io.IOException;

/** GET /logout : destroys the session and returns to the login page. */
public class LogoutServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        HttpSession session = req.getSession(false);
        if (session != null) session.invalidate();          // removes the "user" attribute
        resp.sendRedirect(req.getContextPath() + "/login.jsp?msg=You+have+been+logged+out");
    }
}
