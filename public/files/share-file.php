<link rel="stylesheet" href="../css/style.css">

<?php

require '../session.php';
require '../../config/bootstrap.php';
require '../middleware/status.php';
require '../middleware/file.php';
require '../middleware/permission.php';
require '../include/header.php';
/** @var mysqli $conn */

// $users = $helper->getAllUsers();
$document_id = $_GET['id'];

$sql = "
select * 
from user_info u
where id not in (
    select user_id from document_user_permission where document_id = ?
)";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $document_id);
$stmt->execute();
$users = $stmt->get_result();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['user_ids'])) {
        $error = "select any user.";
    } else {
        $ids = $_POST['user_ids'];
        $type = $_POST['type'];

        foreach ($ids as $id) {
            $helper->addPermission($id, $document_id, $type);
        }

        header("Location: all-files.php");
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
        <div class="share-files">
            <span class="error"><?php echo $error; ?></span>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $_GET['id']; ?>" method="post">
                <h2>Permission</h2>
                <select name="type" id="" class="permission-type">
                    <option value="DOWNLOAD">DOWNLOAD</option>
                    <option value="SHARE">SHARE</option>
                    <option value="ALL">ALL</option>
                </select>
                <h2>Users</h2>
                <table class="user-table" style="width: 400px;">
                    <tr>
                        <th class="check">Select Users</th>
                    </tr>
                    <tr>
                        <?php if (mysqli_num_rows($users) == 0) {
                            echo "all users have access of this file";
                        } else {
                            while ($user = mysqli_fetch_assoc($users)) {
                                if ($user['role'] !== 'ADMIN' && $user['id'] != $_SESSION['user']['id']) { ?>
                                    <td><input type="checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>" id=""> <?php echo $user['email']; ?></td>
                    </tr>
        <?php }
                            }
                        } ?>
                </table>
                <button type="submit" class="btn-share">Share</button>
            </form>
        </div>
    </div>
</body>

</html>