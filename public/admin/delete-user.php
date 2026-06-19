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
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
    if (empty($_POST['user_ids'])) {
        $error = "select any user.";
    } else {
        $ids = implode(',', $_POST['user_ids']);
    
        $sql = mysqli_query($conn, "delete from user_info where id in ($ids)");
    
        header("Location: dashboard.php");
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
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <table class="user-table">
                    <tr>
                        <th class="check">Select Users</th>
                        <th class="name">Name</th>
                        <th class="email">Email</th>
                        <th class="status">Status</th>
                    </tr>
                    <tr>
                        <?php if (mysqli_num_rows($users) > 0) {
                            while ($user = mysqli_fetch_assoc($users)) {
                                if ($user['role'] !== 'ADMIN') { ?>
                                    <td><input type="checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>" id=""></td>
                                    <td class="name"><?php echo $user['name']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['status']; ?></td>
                    </tr>
        <?php }
                            }
                        } ?>
                </table>
                <button type="submit" class="btn-delete" onclick="return confirm('Sure to delete?')">Delete</button>
            </form>

        </div>
    </div>
</body>

</html>