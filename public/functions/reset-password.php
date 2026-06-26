<link rel="stylesheet" href="../css/style.css">
<?php

require '../session.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
/** @var mysqli $conn */

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
    $password = $helper->validate($_POST['password']);

    if (empty($password) || strlen($password) < 5) {
        $passwordErr = "Minumum 5 character required";
    }

    if (empty($passwordErr)) {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare('select u.* from password_reset p join user_info u on p.user_id = u.id where p.otp = ?');
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            $stmt->bind_param('s', $otp);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $result = $stmt->get_result();
    
            $row = $result->fetch_assoc();
    
            $hash = password_hash($password, PASSWORD_DEFAULT);
    
            $stmt = $conn->prepare('update user_info set password = ? where id = ?');
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            $stmt->bind_param('si', $hash, $row['id']);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
    
            $helper->logAction($row['id'], 'PASSWORD_RESET');
    
            $stmt = $conn->prepare('delete from password_reset where otp = ?');
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            $stmt->bind_param('s', $otp);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
    
            $conn->commit();
            
            session_destroy();
            header('Location: ../auth/login.php');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
        
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
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <span class="error"><?php echo htmlspecialchars($passwordErr); ?></span>
            <input type="password" name="password" id="password" placeholder="New Password">
            <button type="submit">reset-password</button>
        </form>
    </div>
</body>

</html>