<?php

require '../session.php';
require '../../config/bootstrap.php';
require '../functions/Helper.php';

/** @var mysqli $conn */
$helper = new Helper($conn);

require '../middleware/auth.php';
require '../middleware/status.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
    throw new Exception('Invalid permission id');
}

$stmt = $conn->prepare("
    select p.user_id, p.document_id, d.owner_id
    from document_user_permission p
    join document_info d
    on p.document_id = d.document_id
    where p.id = ?
");

if (!$stmt) {
    throw new Exception($conn->error);
}

$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    throw new Exception('Invalid permission');
}

$permission = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
| Admins can revoke any permission.
| Owners can revoke permissions on their own documents.
|--------------------------------------------------------------------------
*/

if (
    !$_SESSION['admin'] &&
    $permission['owner_id'] != $_SESSION['user']['id']
) {
    die('unauthorized');
}

$conn->begin_transaction();

try {

    $stmt = $conn->prepare("delete from document_user_permission where id = ?");

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $conn->commit();

    echo 'success';
} catch (Exception $e) {

    $conn->rollback();
    throw $e;
}
