<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="ctx" value="${pageContext.request.contextPath}"/>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Profiles</title>
  <link rel="stylesheet" href="${ctx}/portal.css">
</head>
<body>
<header>
  <strong>Student Profiles</strong>
  <span>Logged in as <b><c:out value="${sessionScope.user}"/></b> &nbsp;|&nbsp; <a href="${ctx}/logout">Logout</a></span>
</header>

<main>
  <form method="get" action="${ctx}/students" class="search">
    <input type="text" name="q" value="<c:out value='${q}'/>" placeholder="Search name or roll number">
    <button>Search</button>
  </form>

  <%-- Conditional: show a message when the list is empty --%>
  <c:if test="${empty students}"><p class="empty">No students found.</p></c:if>

  <div class="grid">
    <%-- Loop 1: every student --%>
    <c:forEach var="s" items="${students}">
      <section class="profile">
        <div class="top">
          <c:choose>
            <%-- Image tag points to the SERVLET, not to a file path --%>
            <c:when test="${not empty s.profile}">
              <img class="avatar" src="${ctx}/file?id=${s.profile.id}" alt="Photo of <c:out value='${s.name}'/>">
            </c:when>
            <c:otherwise><div class="avatar none">No photo</div></c:otherwise>
          </c:choose>
          <div>
            <h2><c:out value="${s.name}"/></h2>
            <p><b>Roll:</b> <c:out value="${s.rollNo}"/><br>
               <b>Course:</b> <c:out value="${s.course}"/><br>
               <b>Email:</b> <c:out value="${s.email}"/></p>
          </div>
        </div>

        <h3>Documents</h3>
        <ul class="docs">
          <%-- Loop 2: every file of this student (profile photo is shown above, so skip it) --%>
          <c:set var="docCount" value="0"/>
          <c:forEach var="f" items="${s.files}">
            <c:if test="${f.category != 'profile'}">
              <c:set var="docCount" value="${docCount + 1}"/>
              <li>
                <c:choose>
                  <c:when test="${f.image}">
                    <a href="${ctx}/file?id=${f.id}" target="_blank">
                      <img class="doc" src="${ctx}/file?id=${f.id}" alt="<c:out value='${f.originalName}'/>"></a>
                  </c:when>
                  <c:otherwise>
                    <span class="pdf">PDF</span>
                  </c:otherwise>
                </c:choose>
                <div>
                  <a href="${ctx}/file?id=${f.id}" target="_blank"><c:out value="${f.originalName}"/></a><br>
                  <small><c:out value="${f.category}"/> &middot; ${f.sizeKb} KB &middot; ${f.uploadedAt}</small>
                </div>
              </li>
            </c:if>
          </c:forEach>
          <c:if test="${docCount == 0}"><li class="none">No documents uploaded</li></c:if>
        </ul>
      </section>
    </c:forEach>
  </div>
</main>
</body>
</html>
