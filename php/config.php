<?php
// config.php - DB connection + shared helpers (included by every page)
session_start();

const DB_HOST = 'localhost';
const DB_NAME = 'student_portal';
const DB_USER = 'root';
const DB_PASS = '';                       // change to your MySQL password

// Upload folder (outside web-root is safer; kept inside for the demo).
// The Servlet in Q3 reads files from this same folder.
const UPLOAD_DIR = __DIR__ . '/uploads/';

// PDO gives prepared statements => protection against SQL injection
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

// Escape output to prevent XSS
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// One-time flash message shown after redirects
function flash($msg = null, $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return; }
    if (!empty($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="msg ' . h($t) . '">' . h($m) . '</div>';
    }
}

// Validate the common student fields; returns array of error strings
function validate_student(array $d): array {
    $e = [];
    if (!preg_match('/^[A-Za-z .]{3,100}$/', trim($d['name'] ?? '')))   $e[] = 'Name must be 3-100 letters.';
    if (!preg_match('/^\d{2}[A-Za-z]{2,4}\d{3}$/', trim($d['roll_no'] ?? ''))) $e[] = 'Roll number format must be like 21CS001.';
    if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL))          $e[] = 'Invalid email address.';
    if (trim($d['course'] ?? '') === '')                                $e[] = 'Course is required.';
    if (($d['phone'] ?? '') !== '' && !preg_match('/^\d{10}$/', $d['phone'])) $e[] = 'Phone must be 10 digits.';
    return $e;
}
