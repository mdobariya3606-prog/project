<?php
require '../session.php';
require '../functions/Helper.php';

/** @var mysqli $conn */
$helper = new Helper($conn);

require '../middleware/auth.php';
require '../middleware/file.php';
require '../middleware/status.php';
require '../../config/bootstrap.php';

$id = $_GET['id'];
$result = $helper->getDocumentById($id);
$file = $result->fetch_assoc();


$path = '../../uploads/' . $helper->getFolderPath($_SESSION['folder']['id']) . '/' . $file['file_name'] . '.' . $file['extension'];


if (file_exists($path)) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '.' . $file['extension'] . '"');
    header('Content-Length: ' . filesize($path));

    readfile($path);
    $helper->logDocument($_SESSION['user']['id'], $file['document_id'], 'DOWNLOAD');
    exit;
}
