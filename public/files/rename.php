<link rel="stylesheet" href="../css/style.css">
<?php
require '../session.php';
require '../../config/bootstrap.php';
require '../middleware/auth.php';
require '../middleware/status.php';
require '../middleware/permission.php';
require '../middleware/file.php';
include '../include/header.php';
/** @var mysqli $conn */


$id = $_GET['id'];



$result = $helper->getDocumentById($id);
$file = $result->fetch_assoc();

$newName = $newNameErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = htmlspecialchars(trim($_POST['file_name']));

    if (empty($newName)) {
        $newNameErr = "required";
    } else if (!preg_match('/^[a-zA-Z0-9() ]*$/', $newName)) {
        $newNameErr = "only spaces, digits and characters are allowed";
    }

    if (empty($newNameErr)) {
        $stmt = $conn->prepare('update document_info set original_name = ? where document_id = ?');
        $stmt->bind_param('si', $newName, $id);
        $stmt->execute();

        header('Location: all-files.php');
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rename file</title>
</head>

<body>
    <div class="rename-form">
        <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="post">
            <span class="error"><?php echo $newNameErr; ?></span>
            <input type="text" name="file_name" id="file_name" value="<?php echo $file['original_name']; ?>">
            <button type="submit">rename-file</button>
        </form>
    </div>
</body>

</html>