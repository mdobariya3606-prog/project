<?php
require '../session.php';
require '../../config/bootstrap.php';
/** @var mysqli $conn */

$stmt = $conn->prepare("select * from user_info where id = ?");
if (!$stmt) {
    throw new Exception($conn->error);
}
$stmt->bind_param('i', $_SESSION['user']['id']);
if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['status'] === 'INACTIVE') {
    session_destroy();
    die("Your account has been deactivated, please reach out the admin.");
}
