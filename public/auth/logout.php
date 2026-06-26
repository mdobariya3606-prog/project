<link rel="stylesheet" href="../css/style.css">
<?php
include '../session.php';
require '../../config/bootstrap.php';
include '../functions/Helper.php';
/** @var mysqli $conn */

$helper = new Helper($conn);
try {
    $helper->isLoggedOut();
} catch (Exception $e) {
    echo $e->getMessage();
}

/** @var mysqli $conn */
include '../../config/bootstrap.php';

$userId = $_SESSION['user']['id'];
session_destroy();

$helper->logAction($userId, 'LOGOUT');

header('Location: login.php');
exit;
