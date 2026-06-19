<link rel="stylesheet" href="../css/style.css">
<?php

require '../session.php';
/** @var mysqli $conn */
require '../../config/bootstrap.php';
require '../functions/Helper.php';

$otp = $password = $passwordErr = "";
$helper = new Helper($conn);

if (!isset($_SESSION['otp'])) {
    die('403 forbidden');
}

if (!$helper->existsByOtp($_SESSION['otp'])) {
    die('wrong otp');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp = $_SESSION['otp'];
    $password = $_POST['password'];

    if (empty($password)) {
        $passwordErr = 'required';
    }

    if (empty($passwordErr)) {
        $sql = 'select u.* from password_reset p join user_info u on p.user_id = u.id where p.otp = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = 'update user_info set password = ? where id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $hash, $row['id']);
        $stmt->execute();

        session_destroy();
        $helper->logAction($row['id'], 'PASSWORD_RESET');

        $sql = 'delete from password_reset where otp = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $_SESSION['otp']);
        $stmt->execute();

        header('Location: ../auth/login.php');
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="forget-pass">
        <form action="<?php echo $_SERVER['PHP_SELF'] . "?otp=$otp"; ?>" method="post">
            <span class="error"><?php echo $passwordErr; ?></span>
            <input type="password" name="password" id="password" placeholder="New Password">
            <button type="submit">reset-password</button>
        </form>
    </div>
</body>

</html>
