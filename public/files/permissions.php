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

$stmt = $conn->prepare('
    SELECT
        u.*,
        p.id AS pid,
        p.type AS permission
    FROM
        document_user_permission p
    JOIN user_info u ON
        p.user_id = u.id
    JOIN document_info d ON
        p.document_id = d.document_id
    WHERE
        p.document_id = ? AND u.id != d.owner_id and u.role != "ADMIN"');

$stmt->bind_param('i', $document_id);
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
                <?php if (mysqli_num_rows($users) > 0) {
                    while ($user = mysqli_fetch_assoc($users)) {
                ?>
                        <tr id="user-row-<?php echo $user['pid']; ?>">
                            <td><?php echo $user['id']; ?></td>
                            <td class="name"><?php echo $user['name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['permission']; ?></td>
                            <td>
                                <button class="btn-change" onclick="revokeAccess(<?php echo $user['pid']; ?>)">Revoke</button>
                            </td>
                        </tr>
                <?php }
                } ?>
            </table>
        </div>
    </div>
</body>
<script>
    function revokeAccess(id) {
        fetch('../files/revoke-permission.php?id=' + id)
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    document
                        .getElementById('user-row-' + id)
                        .remove();
                }
            })
    }
</script>

</html>