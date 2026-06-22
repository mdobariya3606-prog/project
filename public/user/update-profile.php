<?php
require '../session.php';
require '../middleware/auth.php';
require '../../config/bootstrap.php';
include '../functions/Helper.php';
include '../include/header.php';
/** @var mysqli $conn */

$helper = new Helper($conn);
$name = $nameErr = $email = $emailErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $helper->validate($_POST['name']);
    $email = $helper->validate($_POST['email']);

    $nameErr = $helper->checkRequire($name);
    $emailErr = $helper->checkRequire($email);

    if (!preg_match('/^[a-zA-Z ]*$/', $name)) {
        $nameErr = 'only character allowed';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = 'wrong email format';
    }

    if (empty($nameErr) && empty($emailErr)) {

        $result = $helper->getUserByEmail($email);
        $user = $result->fetch_assoc();

        if ($result->num_rows != 0 && $_SESSION['user']['email'] != $email) {
            $emailErr = 'email already exists';
        } else {
            $helper->updateUser($_SESSION['user']['id'], $name, $email);
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;

            $helper->logAction($_SESSION['user']['id'], 'UPDATE_PROFILE');

            header("Location: ../user/profile.php");
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="auth-form">
        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">

            <span class="error"><?php echo $nameErr; ?></span>
            <input type="text" name="name" id="name" value="<?php echo $_SESSION['user']['name']; ?>" placeholder="Name">

            <span class="error"><?php echo $emailErr; ?></span>
            <input type="email" name="email" id="email" value="<?php echo $_SESSION['user']['email'] ?>" placeholder="Email">

            <button type="submit">Update profile</button>
        </form>
    </div>

</body>

</html>