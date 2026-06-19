<?php
require '../session.php';

/** @var mysqli $conn */
/** @var $mail */
require '../../config/bootstrap.php';
require '../functions/Helper.php';

$helper = new Helper($conn);
$email = $emailErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $emailErr = $helper->checkRequire($email);

    if (empty($emailErr)) {
        $result = $helper->getUserByEmail($email);

        if ($result->num_rows == 0) {
            $emailErr = "email does not exists";
        } else {
            $user = $result->fetch_assoc();

            $otp = random_int(100000, 999999);
            $helper->sendPasswordEmail($user, $mail);
            $_SESSION['otp-sent'] = true;

            header("Location: ../functions/verify-otp.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forger Password</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="forget-pass">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <span class="error"><?php echo $emailErr; ?></span>
            <input type="email" name="email" id="email" placeholder="Enter Email">
            
            <button type="submit">send code</button>
        </form>
    </div>
</body>

</html>