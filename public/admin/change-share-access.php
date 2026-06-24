<?php
require '../session.php';
require '../../config/bootstrap.php';
require '../middleware/admin.php';
require '../functions/Helper.php';
/** @var mysqli $conn */

$helper = new Helper($conn);
$id = $_GET['id'];

$stmt = $conn->prepare("UPDATE user_info SET can_share = IF(can_share = 'YES', 'NO', 'YES') WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

$result = $helper->getUserById($id);
$user = $result->fetch_assoc();

echo $user['can_share'];