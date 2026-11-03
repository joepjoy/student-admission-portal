<?php
// view.php - READ one student with all uploaded files + metadata
require 'config.php';

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$st->execute([$id]);
$s = $st->fetch();
if (!$s) { flash('Student not found.', 'err'); header('Location: index.php'); exit; }

$fs = $pdo->prepare('SELECT * FROM student_files WHERE student_id = ? ORDER BY uploaded_at DESC');
$fs->execute([$id]);
$files = $fs->fetchAll();
$profile = null;
foreach ($files as $f) if ($f['category'] === 'profile') { $profile = $f; break; }
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student - <?= h($s['name']) ?></title><link rel="stylesheet" href="style.css"></head>
<body>
<header><strong>Student Details</strong><a href="index.php">&larr; Back to list</a></header>
<div class="wrap"><div class="box">
  <?php flash(); ?>
  <div class="row">
    <div style="flex:0 0 160px;min-width:160px">
      <?php if ($profile): ?>
        <img class="photo" src="uploads/<?= h($profile['file_path']) ?>" alt="profile">
      <?php else: ?><em>No profile image</em><?php endif; ?>
    </div>
    <div>
      <h2 style="margin-top:0"><?= h($s['name']) ?></h2>
      <p><b>Roll No:</b> <?= h($s['roll_no']) ?><br>
         <b>Email:</b> <?= h($s['email']) ?><br>
         <b>Course:</b> <?= h($s['course']) ?><br>
         <b>Phone:</b> <?= h($s['phone'] ?: '-') ?><br>
         <b>Registered:</b> <?= h($s['created_at']) ?></p>
      <a class="btn green" href="edit.php?id=<?= (int)$s['id'] ?>">Edit</a>
    </div>
  </div>

  <h3>Uploaded Files</h3>
  <table>
    <tr><th>Category</th><th>File</th><th>Type</th><th>Size</th><th>Uploaded</th></tr>
    <?php foreach ($files as $f): ?>
      <tr>
        <td><?= h(str_replace('_', ' ', $f['category'])) ?></td>
        <td><a href="uploads/<?= h($f['file_path']) ?>" target="_blank"><?= h($f['original_name']) ?></a></td>
        <td><?= h($f['file_type']) ?></td>
        <td><?= round($f['file_size'] / 1024, 1) ?> KB</td>
        <td><?= h($f['uploaded_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$files): ?><tr><td colspan="5">No files uploaded.</td></tr><?php endif; ?>
  </table>
</div></div>
</body></html>
