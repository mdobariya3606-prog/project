<?php

require '../session.php';
require '../middleware/auth.php';

/** @var mysqli $conn */

$document_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($document_id === false || $document_id === null) {
    throw new Exception('Invalid document id');
}

$user_id = $_SESSION['user']['id'];

$result = $helper->getDocumentById($document_id);

if ($result->num_rows === 0) {
    die('No such file exists');
}

$file = $result->fetch_assoc();

$stmt = $conn->prepare('
    select id
    from document_user_permission
    where user_id = ? and document_id = ?');

if (!$stmt) {
    throw new Exception($conn->error);
}

$stmt->bind_param('ii', $user_id, $document_id);

if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}

$result = $stmt->get_result();

$havePermission = ($result->num_rows > 0);

if (
    !$havePermission &&
    !$_SESSION['admin'] &&
    $file['owner_id'] != $user_id
) {
    die('unauthorized');
}
