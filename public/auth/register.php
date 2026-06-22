<?php
include '../session.php';
/** @var mysqli $conn */
include '../../config/bootstrap.php';
include '../functions/Helper.php';

$helper = new Helper($conn);
$name = $nameErr = $email = $emailErr = $password = $passwordErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $helper->validate($_POST['name']);
    $email = $helper->validate($_POST['email']);
    $password = $helper->validate($_POST['password']);

    $nameErr = $helper->checkRequire($name);
    $emailErr = $helper->checkRequire($email);
    $passwordErr = $helper->checkRequire($password);

    if (!preg_match('/^[a-zA-Z ]*$/', $name)) {
        $nameErr = 'only character allowed';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = 'wrong email format';
    }

    if (strlen($password) < 5) {
        $passwordErr = "minimum 5 character required";
    }

    if (empty($nameErr) && empty($emailErr) && empty($passwordErr)) {

        $user = $helper->getUserByEmail($email);

        if ($user->num_rows != 0) {
            $emailErr = 'email already exists';
        } else {
            $helper->addUser($name, $email, $password);

            $user = mysqli_fetch_assoc($helper->getUserByEmail($email));
            $helper->logAction($user['id'], 'REGISTER');

            header("Location: login.php");
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
            <input type="text" name="name" id="name" value="<?php echo $name ?>" placeholder="Name">

            <span class="error"><?php echo $emailErr; ?></span>
            <input type="email" name="email" id="email" value="<?php echo $email ?>" placeholder="Email">

            <span class="error"><?php echo $passwordErr; ?></span>
            <input type="password" name="password" id="password" value="<?php echo $password ?>" placeholder="Password">

            <button type="submit">Register</button>

            <pre><label>Already have an account?</label> <a href="login.php" class="link">Login</a></pre>
        </form>
    </div>

</body>

</html>