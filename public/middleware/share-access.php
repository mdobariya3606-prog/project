<?php 
$user = $helper->getUserById($_SESSION['user']['id'])->fetch_assoc();

if ($user['can_share'] == 'NO') {
    die("You don't have permission to share file access.");
}
?>