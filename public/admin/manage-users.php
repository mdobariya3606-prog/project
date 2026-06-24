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

if (isset($_POST['btn-delete'])) {
    if (empty($_POST['user_ids'])) {
        $error = "select any user.";
    } else {
        $ids = implode(',', $_POST['user_ids']);

        $sql = mysqli_query($conn, "delete from user_info where id in ($ids)");

        foreach ($_POST['user_ids'] as $key => $id) {
            $directory = '../../uploads/user/' . $id;
            rmdir($directory);
            if ($_SESSION['user']['id'] == $id) {
                session_destroy();
            }
        }
        $users = $helper->getAllUsers();
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
        <div class="delete-users">
            <h2>Users</h2>
            <span class="error"><?php echo $error; ?></span>
            <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
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
                <button type="submit" class="btn-delete" onclick="return confirm('Sure to delete?')" name="btn-delete">Delete</button>
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
                                <td id="status-<?php echo $user['id']; ?>"><?php echo $user['status']; ?></td>
                                <td id="share-access-<?php echo $user['id']; ?>"><?php echo $user['can_share']; ?></td>
                                <td>
                                    <button class="btn-change" onclick="changeStatus(<?php echo $user['id']; ?>)">Change Status</button>
                                </td>
                                <td>
                                    <button class="btn-change" onclick="changeShareStatus(<?php echo $user['id']; ?>)">Change Share Access</button>
                                </td>
                            </tr>
                <?php }
                    }
                } ?>
            </table>

        </div>
    </div>
</body>

<script>
    function changeStatus(id) {
        fetch('../admin/change-status.php?id=' + id)
            .then(response => response.text())
            .then(data => document.getElementById('status-'+id).innerText = data);
    }

    function changeShareStatus(id) {
        fetch('../admin/change-share-access.php?id=' + id)
            .then(response => response.text())
            .then(data => document.getElementById('share-access-'+id).innerText = data);
    }
</script>

</html>