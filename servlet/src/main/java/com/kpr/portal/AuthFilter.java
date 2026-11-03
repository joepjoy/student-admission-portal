package com.kpr.portal;

import javax.servlet.*;
import javax.servlet.http.*;
import java.io.IOException;

/**
 * Runs before /students and /file. If the visitor has no logged-in session,
 * they are redirected to the login page, so nobody can open profiles
 * or stream files by typing the URL directly.
 */
public class AuthFilter implements Filter {

    @Override
    public void doFilter(ServletRequest req, ServletResponse res, FilterChain chain)
            throws IOException, ServletException {
        HttpServletRequest request = (HttpServletRequest) req;
        HttpServletResponse response = (HttpServletResponse) res;

        HttpSession session = request.getSession(false);          // false = don't create a new one
        boolean loggedIn = session != null && session.getAttribute("user") != null;

        if (!loggedIn) {
            response.sendRedirect(request.getContextPath() + "/login.jsp?msg=Please+log+in");
            return;
        }

        // Stop the browser from showing cached private pages after logout (Back button)
        response.setHeader("Cache-Control", "no-store, no-cache, must-revalidate");
        response.setHeader("Pragma", "no-cache");
        chain.doFilter(req, res);
    }
}
