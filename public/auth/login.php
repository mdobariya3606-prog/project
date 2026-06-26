<?php
include '../session.php';
include '../../config/bootstrap.php';
include '../functions/Helper.php';
/** @var mysqli $conn */

$helper = new Helper($conn);
$helper->alreadyLoggedIn();

$email = $emailErr = $password = $passwordErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $helper->validate($_POST['email']);
    $password = $helper->validate($_POST['password']);

    $emailErr = $helper->checkRequire($email);
    $passwordErr = $helper->checkRequire($password);

    if (empty($emailErr) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = 'wrong email format';
    }

    if (strlen($password) < 5) {
        if (empty($passwordErr)) {
            $passwordErr = "minimum 5 character required";
        }
    }

    if (empty($emailErr) && empty($passwordErr)) {

        $result = $helper->getUserByEmail($email);

        if ($result->num_rows === 0) {
            $emailErr = 'wrong credentials';
        } else {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {

                require '../middleware/status.php';

                $email = $row['email'];

                session_regenerate_id(true);
                $_SESSION['user'] = $row;

                $_SESSION['admin'] = $_SESSION['user']['role'] === 'ADMIN';

                if (!$_SESSION['admin']) {
                    $folder = mysqli_query($conn, 'select * from user_folder where parent_id = 1 and user_id = ' . $row['id'])->fetch_assoc();
                } else {
                    $folder = mysqli_query($conn, 'select * from user_folder where user_id = ' . $row['id'])->fetch_assoc();
                }

                if(!$folder) {
                    throw new Exception('user folder not found');
                }

                $_SESSION['folder'] = $folder;

                $helper->logAction($_SESSION['user']['id'], 'LOGIN');

                if ($_SESSION['admin']) {
                    header('Location: ../admin/dashboard.php');
                    exit;
                } else {
                    header("Location: ../user/dashboard.php");
                    exit;
                }
            } else {
                $emailErr = "wrong credentials";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="auth-form">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

            <span class="error"><?php echo $emailErr; ?></span>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email">

            <span class="error"><?php echo $passwordErr; ?></span>
            <input type="password" name="password" id="password" placeholder="Password">

            <button type="submit">Login</button>
            <pre><a href="../functions/forget-password.php" class="link">forgot password?</a></pre>

            <pre><label>Don't have an account?</label> <a href="register.php" class="link">Register</a></pre>
        </form>
    </div>

</body>

</html>