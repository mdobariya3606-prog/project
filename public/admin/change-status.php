<?php 
require '../session.php';
require '../middleware/auth.php';
require '../../config/bootstrap.php';
require '../middleware/admin.php';
require '../functions/Helper.php';
/** @var mysqli $conn */

$helper = new Helper($conn);
$id = $_GET['id'];

$stmt = $conn->prepare("UPDATE user_info SET status = IF(status = 'ACTIVE', 'INACTIVE', 'ACTIVE') WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

$user = $helper->getUserById($id);
$row = $user->fetch_assoc();

echo $row['status'];
?>