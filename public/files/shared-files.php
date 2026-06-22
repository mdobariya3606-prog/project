<?php
require '../session.php';
require '../middleware/auth.php';
require '../middleware/status.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';
include '../include/header.php';
/** @var mysqli $conn */
$helper = new Helper($conn);

$stmt = $conn->prepare('
SELECT d.*, p.type AS permission, u.name AS owner_name
FROM document_user_permission p
JOIN document_info d
    ON p.document_id = d.document_id
JOIN user_info u
    ON d.owner_id = u.id
WHERE p.user_id = ? and d.owner_id != ?');

$user_id = $_SESSION['user']['id'];
$stmt->bind_param('ii', $user_id, $user_id);

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
                    <p>Size: <?php echo round($file['file_size'] / 1000, 2); ?> MB</p>
                    <p>Owner: <?php echo $file['owner_name']; ?></p>
                    <p>Uploaded: <?php echo $file['created_at']; ?></p>

                    <div class="actions">
                        <a href="download.php?id=<?php echo $file['document_id']; ?>" class="btn">Download</a>
                        <?php if ($file['permission'] == 'SHARE') { ?>
                            <a href="share-file.php?id=<?php echo $file['document_id']; ?>" class="btn">Share</a>

                        <?php } else if ($file['permission'] == 'ALL') { ?>
                            <a href="share-file.php?id=<?php echo $file['document_id']; ?>" class="btn">Share</a>
                            <a href="rename.php?id=<?php echo $file['document_id']; ?>" class="btn">Rename</a>

                            <a href="delete-file.php?id=<?php echo $file['document_id']; ?>" onclick="return confirm('delete this document?')" class="btn delete">Delete</a>
                        <?php } ?>
                    </div>
                </div>
        <?php  }
        } ?>
    </div>
</body>

</html>