<?php
require '../session.php';
require '../functions/Helper.php';
require '../middleware/auth.php';
require '../middleware/admin.php';
require '../../config/bootstrap.php';
include '../include/header.php';

/** @var mysqli $conn */
$helper = new Helper($conn);
$current_path = "";

try {
    $current_path = 'uploads/' . $helper->getFolderPath($_SESSION['folder']['id']);
} catch (Exception $e) {
    $current_path = 'Unavailable';
}


$stmt = $conn->prepare('
    select d.*, u.name, u.can_share 
    from document_info d 
    join user_info u 
    on d.owner_id = u.id    
    order by u.id');

if (!$stmt) {
    throw new Exception($conn->error);
}

if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}

$result = $stmt->get_result();

$stmt = $conn->prepare('select * from user_folder where parent_id = ?');
if (!$stmt) {
    throw new Exception($conn->error);
}
$stmt->bind_param('i', $_SESSION['folder']['id']);
if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}
$folders = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files</title>
    <style>
        .search {
            display: block;
            padding: 10px;
            margin: 10px;
            width: 200px;
            border-radius: 4px;
            border: 1px solid #333;
        }
    </style>
</head>

<body>
    <?php if (!$_SESSION['admin']) { ?>
        <div class="folder-container">
            <div class="curr-folder">
                <p><span>Current Folder:</span> <?php echo htmlspecialchars($current_path); ?></p>
            </div>

            <a href="../files/add-folder.php" class="btn-add-file">Add Folder 📁</a>
            <a href="../files/add-file.php" class="btn-add-file">Add File 📄</a>
            <table class="folder-table">

                <tr>
                    <th>Folder name</th>
                    <th>Delete</th>
                </tr>

                <?php if (
                    ($_SESSION['admin'] && $_SESSION['folder']['parent_id'] != null)
                    || !$_SESSION['admin'] && $_SESSION['folder']['parent_id'] != 1
                ) { ?>
                    <tr>
                        <td>
                            <a href="../files/previous.php" class="btn-previous">..</a>
                        </td>
                    </tr>
                <?php } ?>

                <?php while ($folder = $folders->fetch_assoc()) { ?>
                    <tr id="folder-row-<?php echo $folder['id']; ?>">
                        <td>
                            <a href="../files/open-folder.php?id=<?php echo $folder['id'] ?>" class="folder-name"><?php echo htmlspecialchars($folder['folder_name']); ?>/</a>
                        </td>
                        <td>
                            <a onclick="deleteFolder(<?php echo $folder['id']; ?>)" class="btn delete">delete-folder</a>
                        </td>
                    </tr>
                <?php } ?>

            </table>
        </div>
    <?php } ?>

    <input type="text" class="search" id="search" placeholder="search files/type/users">

    <div class="file-container" , id="file-container">
        <?php if ($result->num_rows > 0) {
            while ($file = $result->fetch_assoc()) { ?>
                <div class="file-box" id='file-row-<?php echo $file['document_id']; ?>'>
                    <h3><?php echo htmlspecialchars($file['original_name']) ?></h3>

                    <p>Type: <?php echo htmlspecialchars($file['extension']); ?></p>
                    <p>Size: <?php echo round($file['file_size'] / (1024 * 1024), 2); ?> MB</p>
                    <p>Owner: <?php echo htmlspecialchars($file['name']); ?></p>
                    <p>Uploaded: <?php echo date('d-m-Y', strtotime($file['created_at'])); ?></p>

                    <div class="actions">
                        <a href="../files/rename.php?id=<?php echo $file['document_id']; ?>" class="btn">Rename</a>
                        <a href="../files/download.php?id=<?php echo $file['document_id']; ?>" class="btn">Download</a>

                        <a onclick="deleteDocument(<?php echo $file['document_id']; ?>)" class="btn delete">Delete</a>

                        <?php if ($_SESSION['admin'] || $file['can_share'] == 'YES') { ?>
                            <a href="../files/share-file.php?id=<?php echo $file['document_id']; ?>" class="btn">Share</a>
                            <a href="../files/permissions.php?id=<?php echo $file['document_id']; ?>" class="btn">Permissions</a>
                        <?php } ?>
                    </div>
                </div>
        <?php  }
        } ?>
    </div>
</body>

<script>
    document.getElementById('search').addEventListener('keyup', function() {
        let keyword = this.value;

        fetch('../admin/search.php?search=' + encodeURIComponent(keyword))
            .then(response => response.text())
            .then(data => {
                document
                    .getElementById('file-container')
                    .innerHTML = data;
            });
    })

    function deleteDocument(id) {
        if (confirm('delete this document?')) {
            fetch('../files/delete-file.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    if (data.trim() === 'success') {
                        document
                            .getElementById('file-row-' + id)
                            .remove();
                    }
                })
        }
    }

    function deleteFolder(id) {
        if (confirm('delete this folder?')) {
            fetch('../files/delete-folder.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        document
                            .getElementById('folder-row-' + id)
                            .remove();
                    }
                })
        }
    }
</script>

</html>