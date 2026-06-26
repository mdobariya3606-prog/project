<?php
require '../session.php';
require '../middleware/auth.php';

if (!$_SESSION['admin']) {
    die('403 forbidden');
}
?>