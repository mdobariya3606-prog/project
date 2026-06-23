<link rel="stylesheet" href="../css/style.css">

<?php

require '../session.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
require '../middleware/admin.php';
require '../include/header.php';
/** @var mysqli $conn */

$helper = new Helper($conn);

$users = $helper->getAllUsers();
$storages = $helper->getStoragePerUser();
$total = $helper->getTotalStorage();
function checkStorage($usage)
{
    if ($usage >= 300) {
        echo '🔴';
    } else if ($usage >= 100) {
        echo '🟡';
    } else {
        echo '🟢';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>

<body>
    <div class="container">
        <div class="dashboard">
            <h2>Users</h2>
            <table class="user-table">
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Can Share</th>
                    <th>Status</th>
                    <th>Registered at</th>
                </tr>
                <?php if (mysqli_num_rows($users) > 0) {
                    while ($user = mysqli_fetch_assoc($users)) {
                        if ($user['role'] !== 'ADMIN') { ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td class="name"><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['status']; ?></td>
                                <td><?php echo $user['can_share']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            </tr>
                <?php }
                    }
                } ?>
            </table>


            <h2>Storage Usage per user</h2>
            <table class="user-table">
                <tr>
                    <th>User id</th>
                    <th>Storage - KB</th>
                    <th>Storage - MB</th>
                    <th>Status</th>
                </tr>
                <?php while ($storage = $storages->fetch_assoc()) {
                    if ($storage['user_id'] != 1) { ?>
                        <tr>
                            <td><?php echo $storage['user_id']; ?></td>
                            <td><?php echo round($storage['total'] / 1024, 2); ?></td>
                            <td><?php echo round($storage['total'] / (1024 * 1024), 2); ?></td>
                            <td><b><?php checkStorage($storage['total'] / (1024 * 1024)); ?></b></td>
                        </tr>
                <?php }
                } ?>

                <tr>
                    <td><b>Total Usage</b></td>
                    <td><b><?php echo round($total / 1024, 2) ?></b></td>
                    <td><b><?php echo round($total / 1024 / 1024, 2) ?></b></td>
                </tr>
            </table>

        </div>
    </div>
</body>

</html>