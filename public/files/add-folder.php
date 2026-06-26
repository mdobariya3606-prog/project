<?php
require '../session.php';
require '../middleware/auth.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
include '../include/header.php';
/** @var mysqli $conn  */;

$helper = new Helper($conn);
$err = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newDIR = $helper->validate($_POST['newDIR']);

    if (empty($newDIR)) {
        $err = "required";
    } else {
        try {
            $dir = $helper->getFolderPath($_SESSION['folder']['id']);
        } catch (Exception $e) {
            throw $e;
        }

        $path = '../../uploads/' . $dir . '/' . $newDIR;

        if (is_dir($path)) {
            $err = 'Folder already exists';
        } else {
            $conn->begin_transaction();

            try {
                if (!mkdir($path, 0777, true) && !is_dir($path)) {
                    throw new Exception('Folder creation failed');
                }
                $stmt = $conn->prepare('insert into user_folder (folder_name, user_id, parent_id) values (?, ?, ?)');
                if (!$stmt) {
                    throw new Exception($conn->error);
                }

                $stmt->bind_param('sii', $newDIR, $_SESSION['user']['id'], $_SESSION['folder']['id']);
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                $conn->commit();

                header('Location: ../files/all-files.php');
                exit;
            } catch (Exception $e) {
                if (is_dir($path)) {
                    rmdir($path);
                }
                $conn->rollback();
                throw $e;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add folder</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="change-pass">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <span class="error"><?php echo htmlspecialchars($err); ?></span>
            <input type="text" name="newDIR" id="newDIR">
            <button type="submit">create-folder</button>
        </form>
    </div>
</body>

</html>