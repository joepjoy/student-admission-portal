<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Login</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/portal.css">
</head>
<body class="center">
  <form class="login" method="post" action="${pageContext.request.contextPath}/login">
    <h1>Student Portal</h1>
    <%-- c:out escapes HTML so the msg parameter cannot inject script (XSS) --%>
    <c:if test="${not empty param.msg}"><div class="note"><c:out value="${param.msg}"/></div></c:if>
    <label>Username</label>
    <input type="text" name="username" required autofocus>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
    <p class="hint">Demo: admin / admin123</p>
  </form>
</body>
</html>
