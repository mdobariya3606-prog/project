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

try {

    $stmt = $conn->prepare('select folder_id from document_info where document_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doc = $result->fetch_assoc();

    $path = '../../uploads/' . $helper->getFolderPath($doc['folder_id']) . '/' . $file['file_name'] . '.' . $file['extension'];
} catch (Exception $e) {
    echo $e->getMessage();
}


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
