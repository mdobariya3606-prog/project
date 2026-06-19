<?php
require '../session.php';
require 'auth.php';

if (!$_SESSION['admin']) {
    die('403 forbidden');
}
?>