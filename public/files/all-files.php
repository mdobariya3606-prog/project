<?php
require '../session.php';
require '../middleware/auth.php';
require '../middleware/status.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
include '../include/header.php';
/** @var mysqli $conn */
$helper = new Helper($conn);

if ($_SESSION['admin']) {
    $stmt = $conn->prepare('select d.*, u.name from document_info d join user_info u on d.owner_id = u.id order by u.id');
} else {
    $stmt = $conn->prepare('select d.*, u.name from document_info d join user_info u on d.owner_id = u.id where d.owner_id = ?');
    $stmt->bind_param('i', $_SESSION['user']['id']);
}
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files</title>
</head>

<body>
    <div class="file-container">
        <?php if ($result->num_rows > 0) {
            while ($file = $result->fetch_assoc()) { ?>
                <div class="file-box">
                    <h3><?php echo $file['original_name'] ?></h3>

                    <p>Type: <?php echo $file['extension']; ?></p>
                    <p>Size: <?php echo ceil($file['file_size'] / 1048); ?> KB</p>
                    <p>Owner: <?php echo $file['name']; ?></p>
                    <p>Uploaded: <?php echo $file['created_at']; ?></p>

                    <div class="actions">
                        <a href="rename.php?id=<?php echo $file['document_id']; ?>" class="btn">Rename</a>
                        <a href="download.php?id=<?php echo $file['document_id']; ?>" class="btn">Download</a>
                        <a href="delete-file.php?id=<?php echo $file['document_id']; ?>" onclick="return confirm('delete this document?')" class="btn delete">Delete</a>
                        <a href="share-file.php?id=<?php echo $file['document_id']; ?>" class="btn">Share</a>
                        <a href="permissions.php?id=<?php echo $file['document_id']; ?>" class="btn">Permissions</a>
                    </div>
                </div>
        <?php  }
        } ?>
    </div>
</body>

</html>