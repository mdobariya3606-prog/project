<?php
use Dotenv\Dotenv;
date_default_timezone_set('Asia/Kolkata');
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

ini_set('display_error', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../log/error.log');

error_reporting(E_ALL);

set_exception_handler(function(Throwable $e) {
    error_log($e);
    http_response_code(500);
    echo '<h3>Something went wrong, Please try again later.</h3>';

    // echo "<script>console.log($e)</script>";
});

require __DIR__ . '/conn.php';
require __DIR__ . '/email.php';

?>