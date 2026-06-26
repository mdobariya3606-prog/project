<link rel="stylesheet" href="../css/style.css">
<?php

require '../session.php';
require '../middleware/auth.php';
require '../middleware/status.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
require '../include/header.php';
/** @var mysqli $conn */

$helper = new Helper($conn);
$oldPassword = $oldPasswordErr = $password = $passwordErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $oldPassword = $helper->validate($_POST['oldPassword']);
    $password = $helper->validate($_POST['password']);

    $passwordErr = $helper->checkRequire($password);

    if (empty($passwordErr) && strlen($password) < 5) {
        $passwordErr = "minimum 5 characters required";
    }

    $oldPasswordErr = $helper->checkRequire($oldPassword);

    if (empty($oldPasswordErr) && empty($passwordErr)) {

        if (password_verify($password, $_SESSION['user']['password'])) {
            $passwordErr = "New password must be different from old password";
        } else {

            if (password_verify($oldPassword, $_SESSION['user']['password'])) {
                $conn->begin_transaction();
                try {

                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare('update user_info set password = ? where id = ?');
                    if (!$stmt) {
                        throw new Exception($conn->error);
                    }
                    $stmt->bind_param('si', $hash, $_SESSION['user']['id']);
                    if (!$stmt->execute()) {
                        throw new Exception($stmt->error);
                    }

                    $helper->logAction($_SESSION['user']['id'], 'PASSWORD_CHANGE');

                    $_SESSION['user']['password'] = $hash;

                    $conn->commit();
                    header('Location: ../user/profile.php');
                    exit;
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            } else {
                $oldPasswordErr = "wrong password";
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
    <title>Change password</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="change-pass">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <span class="error"><?php echo htmlspecialchars($oldPasswordErr); ?></span>
            <input type="password" name="oldPassword" id="oldPassword" placeholder="Old Password">

            <span class="error"><?php echo htmlspecialchars($passwordErr); ?></span>
            <input type="password" name="password" id="password" placeholder="New Password">

            <button type="submit">change-password</button>
        </form>
    </div>
</body>

</html>