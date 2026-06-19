<link rel="stylesheet" href="../css/style.css">
<?php
include '../session.php';
/** @var mysqli $conn */
require '../../config/bootstrap.php';
include '../functions/Helper.php';

$helper = new Helper($conn);
$helper->isLoggedOut();

/** @var mysqli $conn */
include '../../config/bootstrap.php';

$userId = $_SESSION['user']['id'];
session_destroy();

$helper->logAction($userId, 'LOGOUT');

header('Location: login.php');
