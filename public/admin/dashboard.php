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
                    <th>Status</th>
                    <th>Functions</th>
                </tr>
                <?php if (mysqli_num_rows($users) > 0) {
                    while ($user = mysqli_fetch_assoc($users)) {
                        if ($user['role'] !== 'ADMIN') { ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td class="name"><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['status']; ?></td>
                                <td>
                                    <a href="../admin/change-status.php?id=<?php echo $user['id']; ?>">Change status</a>
                                </td>
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
                </tr>
                <?php while ($storage = $storages->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $storage['owner_id']; ?></td>
                        <td><?php echo round($storage['total'] / 1024, 2); ?></td>
                        <td><?php echo round($storage['total'] / 1024 / 1024, 2); ?></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td>Total Usage</td>
                    <td><?php echo round($total / 1024, 2) ?></td>
                    <td><?php echo round($total / 1024 / 1024, 2) ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>