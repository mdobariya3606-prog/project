<?php

require '../session.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
require '../middleware/admin.php';
/** @var mysqli $conn */

$helper = new Helper($conn);

$users = $helper->getAllUsers();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['user_ids'])) {
        $error = "select any user.";
    } else {

        $conn->begin_transaction();
        try {
            foreach ($_POST['user_ids'] as $id) {
                $helper->logAction($id, 'USER_DELETED');
                $helper->deleteUserFolder($id);
            }

            $idList = array_map('intval', $_POST['user_ids']);
            $ids = implode(',', $idList);
            $sql = mysqli_query($conn, "delete from user_info where id in ($ids)");

            if (!$sql) {
                throw new Exception($conn->error);
            }

            $conn->commit();
            $users = $helper->getAllUsers();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
}

require '../include/header.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <div class="delete-users">
            <a href="../admin/change-user-password.php" class="btn-change-pass">Change user password</a>

            <h2>Delete users</h3>
                <span class="error"><?php echo htmlspecialchars($error); ?></span>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
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
                                        <td class="name"><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>
            <?php }
                                }
                            } ?>
                    </table>
                    <div class="div-btn-delete">
                        <button type="submit" class="btn-delete" onclick="return confirm('Sure to delete?')" name="btn-delete">Delete</button>
                    </div>
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
            .then(data => document.getElementById('status-' + id).innerText = data);
    }

    function changeShareStatus(id) {
        fetch('../admin/change-share-access.php?id=' + id)
            .then(response => response.text())
            .then(data => document.getElementById('share-access-' + id).innerText = data);
    }
</script>

</html>