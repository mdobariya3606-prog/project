<?php
require '../session.php';
require '../../config/bootstrap.php';
/** @var mysqli $conn */;

$id = $_GET['id'];
$stmt = $conn->prepare('select * from user_folder where id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $folder = $result->fetch_assoc();
    $_SESSION['folder'] = $folder;

    header('Location: ../files/all-files.php');
    exit;
}
