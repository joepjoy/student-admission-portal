<?php
// delete.php - DELETE: remove student, their files on disk, and metadata rows
require 'config.php';
require 'upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Use POST'); }

$id = (int)($_POST['id'] ?? 0);
delete_files($pdo, 'student_id = ?', [$id]);          // physical files first
$del = $pdo->prepare('DELETE FROM students WHERE id = ?');   // FK cascade clears any leftover rows
$del->execute([$id]);

flash($del->rowCount() ? 'Student deleted.' : 'Student not found.', $del->rowCount() ? 'ok' : 'err');
header('Location: index.php');
