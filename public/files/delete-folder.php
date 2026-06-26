<?php
require '../session.php';
require '../middleware/auth.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
/** @var mysqli $conn */;
$helper = new Helper($conn);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id == null) {
    die('invalid folder');
}

$user_id = $_SESSION['user']['id'];

if (!empty($id)) {
    $stmt = $conn->prepare('select user_id from user_folder where id = ?');
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die('folder not found');
    }

    $row = $result->fetch_assoc();
    if ($row['user_id'] != $user_id) {
        die('unauthorized');
    }

    $helper->deleteFolder($id);
    echo 'success';
}
