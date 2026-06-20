<link rel="stylesheet" href="../css/style.css">

<?php

require '../session.php';
/** @var mysqli $conn */
require '../../config/bootstrap.php';
require '../functions/Helper.php';
require '../middleware/admin.php';
require '../include/header.php';

$helper = new Helper($conn);

$users = $helper->getAllUsers();
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
                    <th class="name">Name</th>
                    <th class="email">Email</th>
                    <th class="status">Status</th>
                    <th class="functions">Functions</th>
                </tr>
                <tr>
                    <?php if (mysqli_num_rows($users) > 0) {
                        while ($user = mysqli_fetch_assoc($users)) {
                            if ($user['role'] !== 'ADMIN') { ?>
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
        </div>
    </div>
</body>

</html>