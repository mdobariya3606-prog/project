<link rel="stylesheet" href="../css/style.css">

<?php
require '../session.php';
require '../middleware/auth.php';
require '../middleware/status.php';
require '../functions/Helper.php';
require '../../config/bootstrap.php';
/** @var mysqli $conn */

$helper = new Helper($conn);

if (empty($_SESSION)) {
    die('403 forbidden');
}

$otp = $otpErr = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];
    $otpErr = $helper->checkRequire($otp);

    if (empty($otpErr)) {
        $sql = 'select * from password_reset where otp = ? and expires_at > now()';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $otp);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $otpErr = 'wrong otp';
        } else {
            $_SESSION['otp'] = $otp;
            header("Location: reset-password.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="forget-pass">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <span class="error"><?php echo $otpErr; ?></span>
            <input type="text" name="otp" id="otp" placeholder="Otp">

            <button type="submit">reset-password</button>
        </form>
    </div>
</body>

</html>