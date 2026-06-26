<?php

require '../session.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';

/** @var mysqli $conn */
$helper = new Helper($conn);

require '../middleware/auth.php';
require '../middleware/status.php';
require '../middleware/file.php';
require '../include/header.php';

$document_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($document_id === false || $document_id === null) {
    throw new Exception('Invalid document id');
}

$stmt = $conn->prepare("
    select u.*, p.id AS pid, p.type AS permission
    from document_user_permission p
    join user_info u
    on p.user_id = u.id
    join document_info d
    on d.document_id = p.document_id
    where
        p.document_id = ?
        and u.role != 'ADMIN'
        and u.id != d.owner_id
");

if (!$stmt) {
    throw new Exception($conn->error);
}

$stmt->bind_param('i', $document_id);

if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}

$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permissions</title>
    <link rel="stylesheet" href="../css/style.css">
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

                <?php while ($user = $users->fetch_assoc()) { ?>

                    <tr id="user-row-<?php echo $user['pid']; ?>">
                        <td><?php echo $user['id']; ?></td>
                        <td class="name"><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['permission']); ?></td>
                        <td>
                            <button
                                class="btn-change"
                                onclick="revokeAccess(<?php echo $user['pid']; ?>)">
                                Revoke
                            </button>
                        </td>
                    </tr>

                <?php } ?>

            </table>

        </div>
    </div>

    <script>
        function revokeAccess(id) {
            fetch('../files/revoke-permission.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        document.getElementById('user-row-' + id).remove();
                    } else {
                        console.log(data);
                    }
                });
        }
    </script>

</body>

</html>