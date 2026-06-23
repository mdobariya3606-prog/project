<link rel="stylesheet" href="../css/style.css">

<?php

require '../session.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
require '../middleware/admin.php';
require '../include/header.php';
/** @var mysqli $conn */

$helper = new Helper($conn);

$users = $helper->getAllUsers();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
    if (empty($_POST['user_ids'])) {
        $error = "select any user.";
    } else {
        $ids = implode(',', $_POST['user_ids']);
    
        $sql = mysqli_query($conn, "delete from user_info where id in ($ids)");

        foreach($_POST['user_ids'] as $key => $id) {
            $directory = '../../uploads/user/' . $id;
            rmdir($directory);
            if ($_SESSION['user']['id'] == $id) {
                session_destroy();
            }
        }
        header("Location: dashboard.php");
    }
}
?>

