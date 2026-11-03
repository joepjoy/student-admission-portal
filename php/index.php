<?php
// index.php - READ: list all students (with search) + profile thumbnail
require 'config.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT s.*, (SELECT file_path FROM student_files f
                      WHERE f.student_id = s.id AND f.category = 'profile' LIMIT 1) AS photo
        FROM students s";
$params = [];
if ($q !== '') {                       // search by name / roll no (prepared => safe)
    $sql .= ' WHERE s.name LIKE ? OR s.roll_no LIKE ?';
    $params = ["%$q%", "%$q%"];
}
$sql .= ' ORDER BY s.id DESC';
$st = $pdo->prepare($sql);
$st->execute($params);
$students = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin - Students</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><strong>Student Admin Panel</strong><a href="add.php">+ Add Student</a></header>
<div class="wrap"><div class="box">
  <?php flash(); ?>
  <form method="get" style="margin-bottom:16px">
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search name or roll number"
           style="width:70%;display:inline-block"> <button class="btn">Search</button>
  </form>
  <table>
    <tr><th>Photo</th><th>Roll No</th><th>Name</th><th>Course</th><th>Actions</th></tr>
    <?php foreach ($students as $s): ?>
      <tr>
        <td><?php if ($s['photo']): ?>
              <img class="thumb" src="uploads/<?= h($s['photo']) ?>" alt="photo">
            <?php endif; ?></td>
        <td><?= h($s['roll_no']) ?></td>
        <td><?= h($s['name']) ?></td>
        <td><?= h($s['course']) ?></td>
        <td>
          <a class="btn" href="view.php?id=<?= (int)$s['id'] ?>">View</a>
          <a class="btn green" href="edit.php?id=<?= (int)$s['id'] ?>">Edit</a>
          <!-- DELETE uses POST (never GET) so links/crawlers can't delete data -->
          <form method="post" action="delete.php" style="display:inline"
                onsubmit="return confirm('Delete this student and all their files?')">
            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
            <button class="btn del">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$students): ?><tr><td colspan="5">No students found.</td></tr><?php endif; ?>
  </table>
</div></div>
</body></html>
