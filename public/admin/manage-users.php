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
$error = "";

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
        <div class="delete-users">
            <h2>Users</h2>
            <span class="error"><?php echo $error; ?></span>
            <form action="../admin/delete-user.php" method="post">
                <table class="user-table">
                    <tr>
                        <th class="check">Select Users</th>
                        <th class="name">Name</th>
                        <th class="email">Email</th>
                    </tr>
                    <tr>
                        <?php
                        if (mysqli_num_rows($users) > 0) {
                            while ($user = mysqli_fetch_assoc($users)) {
                                if ($user['role'] !== 'ADMIN') { ?>
                                    <td><input type="checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>" id=""></td>
                                    <td class="name"><?php echo $user['name']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                    </tr>
        <?php }
                            }
                        } ?>
                </table>
                <button type="submit" class="btn-delete" onclick="return confirm('Sure to delete?')">Delete</button>
            </form>

            <h2>Manage Access</h2>
            <table class="user-table">
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Can Share</th>
                    <th>Change status</th>
                    <th>Change share access</th>
                </tr>
                <?php
                $users = $helper->getAllUsers();
                if (mysqli_num_rows($users) > 0) {
                    while ($user = mysqli_fetch_assoc($users)) {
                        if ($user['role'] !== 'ADMIN') { ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td class="name"><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['status']; ?></td>
                                <td><?php echo $user['can_share']; ?></td>
                                <td>
                                    <a href="../admin/change-status.php?id=<?php echo $user['id']; ?>">Change status</a>
                                </td>
                                <td>
                                    <a href="../admin/change-share-access.php?id=<?php echo $user['id']; ?>">Change Share Access</a>
                                </td>
                            </tr>
                <?php }
                    }
                } ?>
            </table>

        </div>
    </div>
</body>

</html>