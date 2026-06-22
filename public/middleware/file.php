<?php
require '../session.php';
require '../helper.php';
require '../middleware/auth.php';
/** @var mysqli $conn */

$id = $_GET['id'];
$user_id = $_SESSION['user']['id'];
$result = $helper->getDocumentById($id);

if ($result->num_rows == 0) {
    include '../include/header.php';
    die("no such file exists");
}

$file = $result->fetch_assoc();
$stmt = $conn->prepare('select * from document_user_permission where user_id = ? and document_id = ?');
$stmt->bind_param('ii', $user_id, $id);
$stmt->execute();
$result = $stmt->get_result();

$havePermission = ($user_id == $_SESSION['user']['id'] && $result->num_rows != 0) ? true : false;

if (!$havePermission && !$_SESSION['admin']) {
    include '../include/header.php';
    die('unauthorized');
}