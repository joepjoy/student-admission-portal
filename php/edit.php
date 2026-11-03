<?php
// edit.php - UPDATE: change student details and/or add/replace files
require 'config.php';
require 'upload.php';

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$st->execute([$id]);
$student = $st->fetch();
if (!$student) { flash('Student not found.', 'err'); header('Location: index.php'); exit; }

$errors = [];
$title = 'Edit Student';
$isEdit = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student = array_merge($student, $_POST);          // keep typed values if validation fails
    $errors = validate_student($_POST);

    if (!$errors) {                                     // roll no must stay unique (ignore this student)
        $chk = $pdo->prepare('SELECT COUNT(*) FROM students WHERE roll_no = ? AND id <> ?');
        $chk->execute([trim($_POST['roll_no']), $id]);
        if ($chk->fetchColumn() > 0) $errors[] = 'Roll number already used by another student.';
    }

    if (!$errors) {
        $pdo->prepare('UPDATE students SET name=?, roll_no=?, email=?, course=?, phone=? WHERE id=?')
            ->execute([trim($_POST['name']), trim($_POST['roll_no']), trim($_POST['email']),
                       trim($_POST['course']), trim($_POST['phone'] ?? '') ?: null, $id]);

        foreach (['profile' => 'profile_image', 'id_proof' => 'id_proof', 'certificate' => 'certificate'] as $cat => $field) {
            if ($err = save_upload($pdo, $id, $cat, $field)) $errors[] = $err;
        }
        if (!$errors) {
            flash('Student updated successfully.');
            header('Location: view.php?id=' . $id);
            exit;
        }
    }
}
require 'form.php';
