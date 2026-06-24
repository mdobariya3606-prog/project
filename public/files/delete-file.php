<link rel="stylesheet" href="../css/style.css">
<?php
require '../session.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
/** @var mysqli $conn */
$helper = new Helper($conn);
require '../middleware/auth.php';
require '../middleware/status.php';
require '../middleware/permission.php';
require '../middleware/file.php';
include '../include/header.php';

$id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare('select type as permission from document_user_permission where user_id = ? and document_id = ?');
$stmt->bind_param('ii', $user_id, $id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();

$result = $helper->getDocumentById($id);

if ($result->num_rows == 0) {
    die("no such file exists");
}
$file = $result->fetch_assoc();

if ($file['owner_id'] == 1) {
    $path = '../../uploads/admin/' . $file['file_name'] . '.' . $file['extension'];
} else {
    $path = '../../uploads/user/' . $file['owner_id'] . '/' . $file['file_name'] . '.' . $file['extension'];
}

if (file_exists($path)) {
    if (unlink($path)) {
        $helper->deleteDocument($id);
        header('Location: all-files.php');
    };
}
