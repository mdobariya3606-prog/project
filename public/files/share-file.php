<?php

require '../session.php';
require '../middleware/auth.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';

/** @var mysqli $conn */
$helper = new Helper($conn);

require '../middleware/status.php';
require '../middleware/share-access.php';
require '../middleware/file.php';
require '../middleware/permission.php';

$document_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($document_id === false || $document_id === null) {
    throw new Exception('Invalid document id');
}

$sql = "
select * 
from user_info u
where id not in (
    select user_id from document_user_permission where document_id = ?
)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    throw new Exception($conn->error);
}
$stmt->bind_param('i', $document_id);

if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}
$users = $stmt->get_result();

$result = $helper->getDocumentById($document_id);

if ($result->num_rows === 0) {
    throw new Exception('Document not found');
}

$file = $result->fetch_assoc();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['user_ids'])) {
        $error = "select any user.";
    } else {
        $ids = $_POST['user_ids'];
        $allowed = ['DOWNLOAD', 'SHARE', 'ALL'];

        $type = $_POST['type'];
        if (!in_array($type, $allowed, true)) {
            throw new Exception('Invalid permission');
        }

        $conn->begin_transaction();
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "select id, name, email from  user_info where id in ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($user = $result->fetch_assoc()) {
                $helper->addPermission($user['id'], $document_id, $type);
                $helper->queueMail($_SESSION['user']['name'], $user['email'], $file['original_name'] . '.' . $file['extension']);
                $helper->logShare($_SESSION['user']['id'], $user['id'], $document_id);
            }
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        header("Location: ../files/all-files.php");
        exit;
    }
}

include '../include/header.php';
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
        <div class="share-files">
            <span class="error"><?php echo htmlspecialchars($error); ?></span>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?php echo $document_id; ?>" method="post">

                <h2>Permission</h2>
                <select name="type" id="" class="permission-type">
                    <option value="DOWNLOAD">DOWNLOAD</option>
                    <option value="SHARE">SHARE</option>
                    <option value="ALL">ALL</option>
                </select>

                <h2>Users</h2>
                <table class="user-table">
                    <tr>
                        <th class="check">Select Users</th>
                    </tr>
                    <tr>
                        <?php if (mysqli_num_rows($users) == 0) {
                            echo "all users have access of this file";
                        } else {
                            while ($user = $users->fetch_assoc()) {
                                if ($user['role'] !== 'ADMIN' && $user['id'] != $_SESSION['user']['id']) { ?>
                                    <td><input type="checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>" id=""> <?php echo htmlspecialchars($user['email']); ?></td>
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