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
select d.*, p.type AS permission, u.name as owner_name
from document_user_permission p
join document_info d
    on p.document_id = d.document_id
join user_info u
    on d.owner_id = u.id
where p.user_id = ? and d.owner_id != ?');

if (!$stmt) {
    throw new Exception($conn->error);
}

$user_id = $_SESSION['user']['id'];
$stmt->bind_param('ii', $user_id, $user_id);

if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}

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
                <div class="file-box" id="file-box-<?php echo $file['document_id']; ?>">
                    <h3><?php echo htmlspecialchars($file['original_name']); ?></h3>

                    <p>Type: <?php echo htmlspecialchars($file['extension']); ?></p>
                    <p>Size: <?php echo ceil($file['file_size'] / 1048); ?> KB</p>
                    <p>Owner: <?php echo htmlspecialchars($file['owner_name']); ?></p>
                    <p>Uploaded: <?php echo htmlspecialchars($file['created_at']); ?></p>

                    <div class="actions">
                        <a href="../files/download.php?id=<?php echo $file['document_id']; ?>" class="btn">Download</a>
                        <?php if ($file['permission'] == 'SHARE') { ?>
                            <a href="../files/share-file.php?id=<?php echo $file['document_id']; ?>" class="btn">Share</a>

                        <?php } else if ($file['permission'] == 'ALL') { ?>
                            <a href="../files/share-file.php?id=<?php echo $file['document_id']; ?>" class="btn">Share</a>
                            <a href="../files/rename.php?id=<?php echo $file['document_id']; ?>" class="btn">Rename</a>
                            <a href="../files/permissions.php?id=<?php echo $file['document_id']; ?>" class="btn">Permissions</a>

                            <a onclick="deleteFile(<?php echo $file['document_id']; ?>)" class="btn delete">Delete</a>
                        <?php } ?>
                    </div>
                </div>
        <?php  }
        } ?>
    </div>
</body>
<script>
    function deleteFile(id) {
        if (confirm('delete this document?')) {
            fetch('../files/delete-file.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        document.getElementById('file-box-' + id).remove();
                    }
                })
        }
    }
</script>

</html>