<?php 
require 'functions/Helper.php';
require '../../config/bootstrap.php';
/** @var mysqli $conn */

if (!isset($helper)) {
    $helper = new Helper($conn);
}
?>