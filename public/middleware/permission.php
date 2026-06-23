<?php
require '../session.php';
/** @var mysqli $conn */

$id = $_GET['id'];

$stmt = $conn->prepare('select type as permission from document_user_permission where user_id = ? and document_id = ?');
$stmt->bind_param('ii', $_SESSION['user']['id'], $id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();

if ($_SESSION['admin']) {
} else if (empty($file) || $file['permission'] != "ALL") {
    die('unauthorized1');
}