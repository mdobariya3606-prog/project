<link rel="stylesheet" href="../css/style.css">
<?php

require '../session.php';
require '../middleware/auth.php';
/** @var mysqli $conn */
require '../../config/bootstrap.php';
require '../functions/Helper.php';
require '../include/header.php';

$helper = new Helper($conn);
$oldPassword = $oldPasswordErr = $password = $passwordErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $oldPassword = $_POST['oldPassword'];
    $password = $_POST['password'];

    if (strlen($password) < 5) {
        $passwordErr = "minimum 5 characters required";
    }

    if (empty($passwordErr)) {
        $passwordErr = $helper->checkRequire($password);
    }

    $oldPasswordErr = $helper->checkRequire($oldPassword);

    if (empty($oldPasswordErr) && empty($passwordErr)) {

        if (password_verify($oldPassword, $_SESSION['user']['password'])) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = 'update user_info set password = ? where id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $hash, $_SESSION['user']['id']);
            $stmt->execute();

            $helper->logAction($_SESSION['user']['id'], 'PASSWORD_RESET');

            $_SESSION['user']['password'] = $hash;

            header('Location: ../user/profile.php');
        } else {
            $oldPasswordErr = "wrong password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change password</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="change-pass">
        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
            <span class="error"><?php echo $oldPasswordErr; ?></span>
            <input type="password" name="oldPassword" id="oldPassword" placeholder="Old Password" value="<?php echo $oldPassword; ?>">

            <span class="error"><?php echo $passwordErr; ?></span>
            <input type="password" name="password" id="password" placeholder="New Password" value="<?php echo $password; ?>">
            <button type="submit">change-password</button>
        </form>
    </div>
</body>

</html>