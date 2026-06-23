<?php
require '../session.php';
require '../middleware/auth.php';
require '../middleware/status.php';
require '../../config/bootstrap.php';
/** @var mysqli $conn */

$id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

if (empty($id)) {
    die('id required');
}

$stmt = $conn->prepare('
select d.owner_id, p.* 
from document_user_permission  p
join document_info d 
on p.document_id = d.document_id 
where p.id = ?');

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('invalid id');
}

$file = $result->fetch_assoc();
if ($file['owner_id'] != $user_id) {
    die('unauthorized');
}

$stmt = $conn->prepare('delete from document_user_permission where id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();

header("Location: permissions.php?id=$id");
