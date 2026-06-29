<?php
require '../session.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';

/** @var mysqli $conn */
$helper = new Helper($conn);

require '../middleware/auth.php';
require '../middleware/status.php';
require '../middleware/permission.php';
require '../middleware/file.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
    throw new Exception('Invalid id');
}

$result = $helper->getDocumentById($id);

if ($result->num_rows === 0) {
    throw new Exception('Document not found');
}

$file = $result->fetch_assoc();

$newName = $newNameErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = $helper->validate($_POST['file_name']);

    $newNameErr = $helper->checkRequire($newName);
    if (empty($newNameErr) && !preg_match('/^[a-zA-Z0-9()-._ ]*$/', $newName)) {
        $newNameErr = "Invalid file name";
    }

    if (empty($newNameErr)) {
        $stmt = $conn->prepare('update document_info set original_name = ? where document_id = ?');
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('si', $newName, $id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $helper->logDocument($_SESSION['user']['id'], $file['document_id'], 'RENAME');

        header('Location: all-files.php');
        exit;
    }
}

require '../include/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rename file</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="rename-form">
        <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="post">
            <span class="error"><?php echo ($newNameErr); ?></span>
            <input type="text" name="file_name" id="file_name" value="<?php echo htmlspecialchars($file['original_name']); ?>">
            <button type="submit">rename-file</button>
        </form>
    </div>
</body>

</html>