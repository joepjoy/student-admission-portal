<?php
// upload.php - secure file-upload handler used by add.php and edit.php

// Allowed types per category: MIME => extension, plus max size in bytes
const UPLOAD_RULES = [
    'profile'     => ['mimes' => ['image/jpeg' => 'jpg', 'image/png' => 'png'],
                      'max'   => 2 * 1024 * 1024],
    'id_proof'    => ['mimes' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'],
                      'max'   => 5 * 1024 * 1024],
    'certificate' => ['mimes' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'],
                      'max'   => 5 * 1024 * 1024],
];

/**
 * Validates and stores one uploaded file, then inserts its metadata row.
 * Returns null on success (or when no file was chosen), otherwise an error string.
 */
function save_upload(PDO $pdo, int $studentId, string $category, string $field): ?string {
    // No file chosen -> nothing to do
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;

    $f = $_FILES[$field];
    $rule = UPLOAD_RULES[$category];

    // 1. PHP-level upload errors (e.g. exceeds php.ini limit)
    if ($f['error'] !== UPLOAD_ERR_OK)
        return "Upload failed for $category (error code {$f['error']}).";

    // 2. Size check
    if ($f['size'] > $rule['max'])
        return ucfirst($category) . ' file too large (max ' . ($rule['max'] / 1048576) . ' MB).';

    // 3. Real MIME type from file contents (not the user-supplied name/type)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($f['tmp_name']);
    if (!isset($rule['mimes'][$mime]))
        return ucfirst($category) . ' must be ' . strtoupper(implode('/', $rule['mimes'])) . ' only.';

    // 4. Build a unique name so existing files are never overwritten.
    //    random_bytes makes collisions practically impossible; the loop is a safety net.
    $ext = $rule['mimes'][$mime];
    do {
        $stored = $studentId . '_' . $category . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    } while (file_exists(UPLOAD_DIR . $stored));

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $stored))
        return 'Could not save the uploaded file.';

    // 5. A student has only ONE profile image: remove the old one when replacing
    if ($category === 'profile') delete_files($pdo, "student_id = ? AND category = 'profile'", [$studentId]);

    // 6. Store metadata: path, type, size, upload date (auto) linked to student
    $pdo->prepare('INSERT INTO student_files (student_id, category, original_name, file_path, file_type, file_size)
                   VALUES (?,?,?,?,?,?)')
        ->execute([$studentId, $category, basename($f['name']), $stored, $mime, $f['size']]);
    return null;
}

/** Deletes files from disk AND their metadata rows matching a WHERE clause. */
function delete_files(PDO $pdo, string $where, array $params): void {
    $st = $pdo->prepare("SELECT id, file_path FROM student_files WHERE $where");
    $st->execute($params);
    foreach ($st->fetchAll() as $row) {
        $path = UPLOAD_DIR . basename($row['file_path']);      // basename blocks path traversal
        if (is_file($path)) unlink($path);
        $pdo->prepare('DELETE FROM student_files WHERE id = ?')->execute([$row['id']]);
    }
}
