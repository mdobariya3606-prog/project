<?php
require '../session.php';
/** @var mysqli $conn */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
    throw new Exception('Invalid id');
}

$stmt = $conn->prepare('select type as permission from document_user_permission where user_id = ? and document_id = ?');
if (!$stmt) {
    throw new Exception($conn->error);
}
$stmt->bind_param('ii', $_SESSION['user']['id'], $id);
if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}
$result = $stmt->get_result();
$file = $result->fetch_assoc();

if (!$_SESSION['admin'] && (empty($file) || $file['permission'] !== "ALL")) {
    die('unauthorized');
}
