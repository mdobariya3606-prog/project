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
    $newDIR = $_POST['newDIR'];

    if (empty($newDIR)) {
        $err = "required";
    } else {
        $dir = $helper->getFolderPath($_SESSION['folder']['id']);

        $path = '../../uploads/' . $dir . '/' . $newDIR;

        if (is_dir($path)) {
            $err = 'folder already exists';
        } else {
            mkdir($path, 0777, true);

            $stmt = $conn->prepare('insert into user_folder (folder_name, user_id, parent_id) values (?, ?, ?)');
            $stmt->bind_param('sii', $newDIR, $_SESSION['user']['id'], $_SESSION['folder']['id']);
            $stmt->execute();
            header('Location: ../files/all-files.php');
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
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <span class="error"><?php echo $err ?></span>
            <input type="text" name="newDIR" id="newDIR">
            <button type="submit">create-folder</button>
        </form>
    </div>
</body>

</html>