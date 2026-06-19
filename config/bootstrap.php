<?php

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

require 'conn.php';
require 'email.php';

?>