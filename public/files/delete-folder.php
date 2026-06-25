<?php 
require '../session.php';
require '../middleware/auth.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
/** @var mysqli $conn */;
$helper = new Helper($conn);

$id = $_GET['id'];
if (!empty($id)) {
    $helper->deleteFolder($id);
    echo 'success';
}
?>