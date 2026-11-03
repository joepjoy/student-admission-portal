<?php
// add.php - CREATE: insert student + upload files
// Also receives the POST from frontend/registration.html (extra fields like password are ignored).
require 'config.php';
require 'upload.php';

$errors = [];
$student = $_POST;
$title = 'Add Student';
$isEdit = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validate_student($_POST);              // server-side validation (never trust the client)

    // Duplicate roll number check
    if (!$errors) {
        $chk = $pdo->prepare('SELECT COUNT(*) FROM students WHERE roll_no = ?');
        $chk->execute([trim($_POST['roll_no'])]);
        if ($chk->fetchColumn() > 0) $errors[] = 'Roll number already exists.';
    }

    if (!$errors) {
        // Transaction: if any upload fails we roll back so no half-saved student remains
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO students (name, roll_no, email, course, phone) VALUES (?,?,?,?,?)')
            ->execute([trim($_POST['name']), trim($_POST['roll_no']), trim($_POST['email']),
                       trim($_POST['course']), trim($_POST['phone'] ?? '') ?: null]);
        $id = (int)$pdo->lastInsertId();

        foreach (['profile' => 'profile_image', 'id_proof' => 'id_proof', 'certificate' => 'certificate'] as $cat => $field) {
            if ($err = save_upload($pdo, $id, $cat, $field)) $errors[] = $err;
        }

        if ($errors) {
            // remove any files already stored, then undo the DB inserts
            delete_files($pdo, 'student_id = ?', [$id]);
            $pdo->rollBack();
        } else {
            $pdo->commit();
            flash('Student added successfully.');
            header('Location: index.php');
            exit;
        }
    }
}
require 'form.php';
