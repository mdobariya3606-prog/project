<?php
require '../session.php';
include '../include/header.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';

/** @var mysqli $conn */
$helper = new Helper($conn);
$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare('select count(*) as total from document_info where owner_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total = $result->fetch_assoc();
$totalDocuments = $total['total'];

$sql = 'select created_at from document_info where owner_id = ? order by created_at desc limit 1';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$lastUploadResult = $stmt->get_result();

if ($lastUploadResult->num_rows > 0) {
    $lastUpload = $lastUploadResult->fetch_assoc()['created_at'];
} else {
    $lastUpload = "No recent uploads";
}

$stmt = $conn->prepare('
SELECT count(*) AS total
FROM document_user_permission p
JOIN document_info d
    ON p.document_id = d.document_id
JOIN user_info u
    ON d.owner_id = u.id
WHERE p.user_id = ? and d.owner_id != ?');

$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total = $result->fetch_assoc();
$sharedFiles = $total['total'];

$storage = $helper->getStorageById($user_id);

$stmt = $conn->prepare('
    select d.*, u.name, u.can_share 
    from document_info d 
    join user_info u 
    on d.owner_id = u.id 
    where d.owner_id = ? order by created_at limit 5');

$stmt->bind_param('i', $_SESSION['user']['id']);
$stmt->execute();

$result = $stmt->get_result();

$sql = 'select extension, count(*) as total from document_info where owner_id = ? group by extension';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();

$extResult = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <div class="dashboard">
            <table class="profile-table">
                <tr>
                    <th>Description</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td>Owned documents</td>
                    <td><?php echo $totalDocuments; ?></td>
                </tr>
                <tr>
                    <td>Received Files</td>
                    <td><?php echo $sharedFiles; ?></td>
                </tr>
                <tr>
                    <td>Storage Used</td>
                    <td><?php echo round($storage / (1024 * 1024), 2); ?>MB/400MB</td>
                </tr>
                <tr>
                    <td>Last Upload</td>
                    <td><?php echo $lastUpload; ?></td>
                </tr>
            </table>

            <h2>File types</h2>
            <table class="profile-table">
                <tr>
                    <th>File Type</th>
                    <th>Count</th>
                </tr>
                <?php while ($extRow = $extResult->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $extRow['extension']; ?></td>
                        <td><?php echo $extRow['total']; ?></td>
                    </tr>
                <?php } ?>
            </table>

            <h2>Last 5 uploaded files</h2>

            <div class="file-container">
                <?php if ($result->num_rows > 0) {
                    while ($file = $result->fetch_assoc()) { ?>
                        <div class="file-box">
                            <h3><?php echo $file['original_name'] ?></h3>

                            <p>Type: <?php echo $file['extension']; ?></p>
                            <p>Size: <?php echo round($file['file_size'] / (1024 * 1024), 2); ?> MB</p>
                            <p>Uploaded: <?php echo date('d-m-Y', strtotime($file['created_at'])); ?></p>
                        </div>
                <?php  }
                } ?>
            </div>


        </div>
    </div>
</body>

</html>