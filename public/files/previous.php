<?php
require '../session.php';
require '../../config/bootstrap.php';
/** @var mysqli $conn */;

if (
    ($_SESSION['admin'] && $_SESSION['folder']['parent_id'] === null)
    || !$_SESSION['admin'] && $_SESSION['folder']['parent_id'] === 1
) {
    header("Location: ../files/all-files.php");
    exit;
}
$stmt = $conn->prepare('select * from user_folder where id = ?');
$stmt->bind_param('i', $_SESSION['folder']['parent_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['folder'] = $result->fetch_assoc();
}

header("Location: ../files/all-files.php");
exit;