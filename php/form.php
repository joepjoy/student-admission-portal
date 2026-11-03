<?php
// form.php - shared HTML form (included by add.php and edit.php)
// Expects: $student (array), $errors (array), $title (string), $isEdit (bool)
$courses = ['B.E. CSE', 'B.E. ECE', 'B.E. EEE', 'B.E. MECH', 'B.Tech IT'];
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title) ?></title><link rel="stylesheet" href="style.css"></head>
<body>
<header><strong><?= h($title) ?></strong><a href="index.php">&larr; Back to list</a></header>
<div class="wrap"><div class="box">
  <?php if ($errors): ?>
    <div class="msg err"><ul><?php foreach ($errors as $e) echo '<li>' . h($e) . '</li>'; ?></ul></div>
  <?php endif; ?>

  <!-- enctype multipart/form-data is REQUIRED for file uploads -->
  <form method="post" enctype="multipart/form-data">
    <label>Full Name *</label>
    <input type="text" name="name" value="<?= h($student['name'] ?? '') ?>" required>
    <label>Roll Number *</label>
    <input type="text" name="roll_no" value="<?= h($student['roll_no'] ?? '') ?>" required>
    <label>Email *</label>
    <input type="email" name="email" value="<?= h($student['email'] ?? '') ?>" required>
    <label>Course *</label>
    <select name="course" required>
      <option value="">-- Select --</option>
      <?php foreach ($courses as $c): ?>
        <option <?= ($student['course'] ?? '') === $c ? 'selected' : '' ?>><?= h($c) ?></option>
      <?php endforeach; ?>
    </select>
    <label>Phone (10 digits)</label>
    <input type="text" name="phone" value="<?= h($student['phone'] ?? '') ?>">

    <label>Profile Image (JPG/PNG, max 2 MB)<?= $isEdit ? ' - leave empty to keep current' : '' ?></label>
    <input type="file" name="profile_image" accept=".jpg,.jpeg,.png">
    <label>ID Proof (JPG/PNG/PDF, max 5 MB)</label>
    <input type="file" name="id_proof" accept=".jpg,.jpeg,.png,.pdf">
    <label>Certificate (JPG/PNG/PDF, max 5 MB)</label>
    <input type="file" name="certificate" accept=".jpg,.jpeg,.png,.pdf">

    <p><button class="btn"><?= $isEdit ? 'Update' : 'Save' ?> Student</button></p>
  </form>
</div></div>
</body></html>
