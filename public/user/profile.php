<link rel="stylesheet" href="../css/style.css">
<?php
require '../session.php';
require '../middleware/auth.php';
require '../middleware/status.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
include '../include/header.php';

/** @var mysqli $conn */

$helper = new Helper($conn);
$result = $helper->getUserById($_SESSION['user']['id']);
if ($result->num_rows === 0) {
    throw new Exception('User not found');
}

$user = $result->fetch_assoc();
$storage = $helper->getStorageById($user['id']);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>

<body>
    <div class="container">
        <h2>Profile </h2>
        <table class="profile-table">
            <tr>
                <td>Id</td>
                <td><?php echo $user['id']; ?></td>
            </tr>
            <tr>
                <td>Name</td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
            </tr>
            <tr>
                <td>Storage usage - KB</td>
                <td><?php echo round($storage / 1024, 2); ?></td>
            </tr>
            <tr>
                <td>Storage usage - MB</td>
                <td><?php echo round($storage / (1024 * 1024), 2); ?>MB</td>
            </tr>
        </table>
        <a href="../user/update-profile.php" class="btn-update">Update profile</a>
    </div>
</body>

</html>