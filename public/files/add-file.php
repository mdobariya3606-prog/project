<?php
include '../session.php';
include '../functions/Helper.php';
require '../middleware/auth.php';
require '../middleware/status.php';
include '../../config/bootstrap.php';
include '../include/header.php';
/** @var mysqli $conn */

$helper = new Helper($conn);
$file = $message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['document'];

    if (empty($file['size'])) {
        $message = "select file";
    }

    if (empty($message)) {
        $fileName = $file['name'];
        $tmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'php'];
        $extenstion = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // check user storage limit
        $user_id = $_SESSION['user']['id'];
        $kb = $helper->getStorageById($user_id);
        $usage = $kb / (1024 * 1024);

        if (!in_array($extenstion, $allowed)) {
            $message = 'invalid file type.';
        } else if (($usage + ($fileSize / (1024 * 1024))) > 400) {
            $message = "you're out of storage to upload this file.";
        } else if ($fileSize > (100 * 1024 * 1024)) {
            $message = 'file too large, upload less than 100 MB.';
        }

        if (empty($message)) {
            $newName = uniqid($_SESSION['user']['id'] . '.', true);
            if ($_SESSION['admin']) {
                $destination = '../../uploads/admin/' . $newName . '.' . $extenstion;
            } else {
                $destination = '../../uploads/user/' . $_SESSION['user']['id'] . '/' . $newName . '.' . $extenstion;
            }

            if (move_uploaded_file($tmpName, $destination)) {
                $original_name = pathinfo($fileName, PATHINFO_FILENAME);
                $document_id = $helper->addDocument($original_name, $newName, $fileSize, $extenstion);
                $message = 'file uploaded successfully';
                $helper->addPermission($_SESSION['user']['id'], $document_id, "ALL");
            } else {
                $message = 'file upload failed';
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
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="upload-file">
        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
            <div class="upload-box" id="uploadBox">

                <input type="file" id="fileInput" name="document">
                <div class="upload-icon">📄</div>
                <h3>Drop files here</h3>
                <small>PDF, DOCX, XLSX, PPT</small>
                <span id="fileName">No file selected</span>
                <span class="error"><?php echo $message; ?></span>

            </div>
            <button type="submit">Add file</button>
        </form>
    </div>

    <script>
        const uploadBox = document.getElementById('uploadBox');
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');

        uploadBox.addEventListener('click', () => {
            fileInput.click();
        })

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileName.textContent = fileInput.files[0].name;
            }
        })

        uploadBox.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadBox.classList.add('dragover');
        })

        uploadBox.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadBox.classList.remove('dragover');
        })

        uploadBox.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadBox.classList.remove('dragover');

            const files = e.dataTransfer.files;

            if (files.length > 0) {
                fileInput.files = files;
                fileName.textContent = files[0].name;
            }
        })
    </script>
</body>

</html>