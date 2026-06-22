<?php
require '../session.php';
require '../middleware/file.php';
require '../../config/bootstrap.php';
/** @var mysqli $conn */

$id = $_GET['id'];
$result = $helper->getDocumentById($id);
$file = $result->fetch_assoc();

if ($file['owner_id'] == 1) {
    $path = '../../uploads/admin/' . $file['file_name'] . '.' . $file['extension'];
} else {
    $path = '../../uploads/user/' . $file['owner_id'] . '/' . $file['file_name'] . '.' . $file['extension'];
}

if (file_exists($path)) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '.' . $file['extension'] . '"');
    header('Content-Length: ' . filesize($path));

    readfile($path);
    exit;
}
