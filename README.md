# Student Admission Portal - Web Technology Assignment II (U21CS501)

Individual assignment. Put your **name / roll number** here before submitting: **JOE P JOY (24CS084)**

## Folder structure

```
db/schema.sql                         DB schema script (3 tables + demo data)
frontend/                             Q1  registration.html, style.css, validate.js
php/                                  Q2  config, upload, index, add, edit, view, delete, form, style.css, uploads/
servlet/                              Q3  Maven web app
  pom.xml
  src/main/java/com/kpr/portal/       DBUtil, Student, StudentFile, AuthFilter, LoginServlet,
                                      LogoutServlet, StudentListServlet, FileServlet
  src/main/webapp/login.jsp, portal.css
  src/main/webapp/WEB-INF/web.xml     deployment descriptor (servlets, filter, session config, DB settings)
  src/main/webapp/WEB-INF/views/students.jsp
```

## How to run

### 1. Database
```bash
mysql -u root -p < db/schema.sql
```

### 2. Q1 + Q2 (XAMPP / any Apache + PHP 8)
1. Copy `php/` to `C:\xampp\htdocs\student_admin\` and `frontend/` to `C:\xampp\htdocs\frontend\`
   (the form posts to `../php/add.php`, so keep `frontend` and the PHP folder side by side - or rename the folder in the form's `action`).
2. Start Apache + MySQL. Edit DB password in `php/config.php` if needed.
3. Open `http://localhost/frontend/registration.html` (Q1) and `http://localhost/student_admin/index.php` (Q2).
4. Ensure `extension=fileinfo` is enabled in `php.ini` (default in XAMPP).

### 3. Q3 (Tomcat 9 + Maven)
1. In `servlet/src/main/webapp/WEB-INF/web.xml` set `dbPassword` and `uploadDir` (the PHP `uploads` folder, e.g. `C:/xampp/htdocs/student_admin/uploads`).
2. `cd servlet && mvn clean package` -> copy `target/student-portal.war` to Tomcat's `webapps/`.
3. Open `http://localhost:8080/student-portal/` and log in with **admin / admin123**.
   (Tomcat 10+ uses `jakarta.*` instead of `javax.*`; use Tomcat 9 as written.)

## Explanation of the code

### Q1 - Registration form (HTML / CSS / JS)
* `registration.html` - semantic form with name, roll number, email, course (dropdown), password + confirm, and file upload. `novalidate` lets our JavaScript show styled messages instead of browser pop-ups.
* `style.css` - CSS variables for the colour theme, two-column CSS Grid that collapses to one column below 600 px (`@media`), focus/valid/invalid styles for inputs, button hover effects, password strength bar.
* `validate.js` - one validator function per field: required check, regex for name / roll number (`21CS001`), email format, password strength (8+ chars, upper, lower, digit, symbol - shown live in a meter), confirm-password match, and file type (JPG/PNG) and size (<= 2 MB). Fields validate on blur and re-validate as the user fixes them; on submit all validators run and the first invalid field is focused.

### Q2 - PHP + MySQL CRUD with file handling
| Page | Operation | Notes |
|---|---|---|
| `index.php` | Read (list/search) | LIKE search with prepared statement, profile thumbnail via subquery |
| `add.php` | Create | server-side validation, duplicate roll-no check, DB transaction with rollback if an upload fails |
| `edit.php` | Update | roll no unique except self, can add/replace files |
| `view.php` | Read (single) | profile image + table of every uploaded file with type, size, upload date and link |
| `delete.php` | Delete | POST only; deletes the files on disk, then the row (FK `ON DELETE CASCADE` also cleans metadata) |

* **Uploads (`upload.php`)** - checks PHP error code, size limit (2 MB profile / 5 MB documents), the *real* MIME type with `finfo` (not the client-supplied one), then stores the file under a random unique name (`<id>_<category>_<random>.<ext>`) after checking `file_exists`, so **nothing is ever overwritten** and original names cannot cause path attacks.
* **Metadata** - each file gets a row in `student_files` (`student_id` FK, `category`, `original_name`, `file_path`, `file_type`, `file_size`, `uploaded_at` auto timestamp).
* **Security** - PDO prepared statements (SQL injection), `htmlspecialchars` on all output (XSS), `.htaccess` in `uploads/` stops script execution.

### Q3 - Servlet / JDBC / JSP portal
* `LoginServlet` - verifies username + SHA-256 hash with a `PreparedStatement`, invalidates the old session and creates a new one (prevents session fixation), 15-minute timeout.
* `AuthFilter` - mapped to `/students` and `/file` in `web.xml`; no session -> redirect to login. Adds no-cache headers so the Back button cannot show private pages after logout.
* `StudentListServlet` - JDBC fetches students (+ optional search) and all file records, links each file to its student, stores the list in the request, forwards to the JSP (which sits under `WEB-INF/views`, so it cannot be opened directly).
* `FileServlet` - `GET /file?id=N` looks up the stored file name and MIME type by id, resolves it inside the configured upload folder (blocks `../`), sets `Content-Type`/`Content-Length`, and streams the bytes. The browser never learns the disk path.
* `students.jsp` - JSTL: `c:forEach` over students and, nested, over each student's files; `c:choose/c:when` shows the profile photo or a placeholder and an image thumbnail or "PDF" badge; `c:if` handles the empty list. Every image is `<img src="file?id=...">`, i.e. served by the servlet. `c:out` escapes data.
* `LogoutServlet` - invalidates the session.

## Screenshots to include (take these in your browser)
1. `registration.html` - empty form, then with validation errors, then password strength "Strong", then mobile width (F12 -> device toolbar).
2. PHP `index.php` (list), `add.php` (form + an error such as wrong file type / too large), `view.php` (with files table), `edit.php`, delete confirmation.
3. phpMyAdmin showing `students` and `student_files` rows.
4. Tomcat `login.jsp`, `students` page with photos/documents, and opening `/students` while logged out redirecting to login.

## Conclusion
This project combines a validated, responsive front end with a PHP/MySQL admin panel and a Java Servlet/JSP portal that share one database and one upload folder. Files are checked for size and real type, saved under unique names, and tracked in MySQL, so records and documents stay linked. The servlet layer adds JDBC retrieval, file streaming and session-based access control. Together they show how the same data can be managed in PHP and served securely in Java.

## GitHub
Repository link: https://github.com/joepjoy/student-admission-portal
