<?php
require '../session.php';
require '../middleware/auth.php';
require '../middleware/status.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
require '../include/header.php';
/** @var mysqli $conn */

$helper = new Helper($conn);
$name = $nameErr = $email = $emailErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $helper->validate($_POST['name']);
    $email = $helper->validate($_POST['email']);

    $nameErr = $helper->checkRequire($name);
    $emailErr = $helper->checkRequire($email);

    if (empty($nameErr) && !preg_match('/^[a-zA-Z ]*$/', $name)) {
        $nameErr = 'only character allowed';
    } elseif (empty($emailErr) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = 'wrong email format';
    }

    if (empty($nameErr) && empty($emailErr)) {

        $result = $helper->getUserByEmail($email);

        if ($result->num_rows !== 0 && $_SESSION['user']['email'] !== $email) {
            $emailErr = 'email already exists';
        } else {
            $conn->begin_transaction();
            try {
                $helper->updateUser($_SESSION['user']['id'], $name, $email);
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;

                $helper->logAction($_SESSION['user']['id'], 'UPDATE_PROFILE');

                $conn->commit();
                header("Location: ../user/profile.php");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
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
    <title>Update Profile</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="auth-form">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

            <span class="error"><?php echo htmlspecialchars($nameErr); ?></span>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name ?: $_SESSION['user']['name']); ?>" placeholder="Name">

            <span class="error"><?php echo htmlspecialchars($emailErr); ?></span>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email ?: $_SESSION['user']['email']); ?>" placeholder="Email">

            <button type="submit">Update profile</button>
        </form>
    </div>

</body>

</html>