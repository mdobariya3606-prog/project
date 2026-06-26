<?php
require '../session.php';
require '../../config/bootstrap.php';
require '../middleware/admin.php';
require '../functions/Helper.php';
/** @var mysqli $conn */

$helper = new Helper($conn);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
    throw new Exception('Invalid id');
}

$stmt = $conn->prepare("update user_info set can_share = IF(can_share = 'YES', 'NO', 'YES') where id = ?");
if (!$stmt) {
    throw new Exception($conn->error);
}
$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}

$result = $helper->getUserById($id);

if ($result->num_rows === 0) {
    throw new Exception('User not found');
}
$user = $result->fetch_assoc();

echo $user['can_share'];
