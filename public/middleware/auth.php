<?php

require '../session.php';
if (empty($_SESSION)) {
    header("Location: ../auth/login.php");
    exit;
}
?>