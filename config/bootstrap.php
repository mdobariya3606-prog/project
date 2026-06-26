<?php

date_default_timezone_set('Asia/Kolkata');
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

require __DIR__ . '/conn.php';
require __DIR__ . '/email.php';

?>