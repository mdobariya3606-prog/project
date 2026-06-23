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

$user_id = $_SESSION['user']['id'];
$document_id = $_GET['id'];

$stmt = $conn->prepare('select u.*, p.id as pid, p.type as permission from document_user_permission p join user_info u on p.user_id = u.id where p.document_id = ? and p.user_id != ?');
$stmt->bind_param('ii', $document_id, $user_id);
$stmt->execute();

$users = $stmt->get_result();
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
                    <th class="id">Id</th>
                    <th class="name">Name</th>
                    <th class="email">Email</th>
                    <th class="email">Permission</th>
                    <th class="functions">Functions</th>
                </tr>
                <tr>
                    <?php if (mysqli_num_rows($users) > 0) {
                        while ($user = mysqli_fetch_assoc($users)) {
                            if ($user['role'] !== 'ADMIN') { ?>
                                <td><?php echo $user['id']; ?></td>
                                <td class="name"><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['permission']; ?></td>
                                <td>
                                    <a href="../files/revoke-permission.php?id=<?php echo $user['pid'];?>">Revoke</a>
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