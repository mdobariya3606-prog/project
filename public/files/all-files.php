<?php
require '../session.php';
require '../functions/Helper.php';
require '../middleware/auth.php';
require '../middleware/status.php';
require '../../config/bootstrap.php';
include '../include/header.php';

/** @var mysqli $conn */
$helper = new Helper($conn);

if (!isset($_GET['page']) || empty($_GET['page'])) {
    $page = 1;
} else {
    $page = $_GET['page'];
}

$offset = ($page - 1) * 9;

if ($_SESSION['admin']) {
    $stmt = $conn->prepare('
    select d.*, u.name, u.can_share 
    from document_info d 
    join user_info u 
    on d.owner_id = u.id    
    order by u.id limit 9 offset ?');

    $stmt->bind_param('i', $offset);
} else {
    $stmt = $conn->prepare('
    select d.*, u.name, u.can_share 
    from document_info d 
    join user_info u 
    on d.owner_id = u.id 
    where d.owner_id = ? limit 9 offset ?');

    $stmt->bind_param('ii', $_SESSION['user']['id'], $offset);
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
                    <p>Size: <?php echo round($file['file_size'] / (1024 * 1024), 2); ?> MB</p>
                    <p>Owner: <?php echo $file['name']; ?></p>
                    <p>Uploaded: <?php echo date('d-m-Y', strtotime($file['created_at'])); ?></p>

                    <div class="actions">
                        <a href="rename.php?id=<?php echo $file['document_id']; ?>" class="btn">Rename</a>
                        <a href="download.php?id=<?php echo $file['document_id']; ?>" class="btn">Download</a>
                        <a href="delete-file.php?id=<?php echo $file['document_id']; ?>" onclick="return confirm('delete this document?')" class="btn delete">Delete</a>

                        <?php if ($_SESSION['admin'] || $file['can_share'] == 'YES') { ?>
                            <a href="share-file.php?id=<?php echo $file['document_id']; ?>" class="btn">Share</a>
                            <a href="permissions.php?id=<?php echo $file['document_id']; ?>" class="btn">Permissions</a>
                        <?php } ?>
                    </div>
                </div>
        <?php  }
        } ?>

        <div class="navigation">
            <a href="?page=<?= $page - 1 ?>">← Previous</a>

            <span>Page <?= $page  ?></span>

            <a href="?page=<?= $page + 1 ?>">Next →</a>
        </div>
    </div>
</body>

</html>