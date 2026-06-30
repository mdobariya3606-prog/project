<?php
include __DIR__ . '/../session.php';
include __DIR__ . '/../../config/bootstrap.php';

class Helper
{
    public mysqli $conn;

    function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    function validate($str)
    {
        return htmlspecialchars(trim($str));
    }

    function isLoggedOut()
    {
        if (empty($_SESSION)) {
            throw new Exception('already logged out');
        }
    }

    function alreadyLoggedIn()
    {
        if (!empty($_SESSION)) {
            header("Location: ../user/profile.php");
            exit;
        }
    }

    function checkRequire($field)
    {
        if (empty($field)) {
            return "$field required";
        }
    }

    function logAction($userId, $action)
    {

        $stmt = $this->conn->prepare(
            'insert into audit_log (user_id, action) VALUES (?, ?)'
        );

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('is', $userId, $action);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function logDocument($user_id, $document_id, $action)
    {
        $stmt = $this->conn->prepare(
            'insert into audit_log (user_id, document_id, action) VALUES (?, ?, ?)'
        );

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('iis', $user_id, $document_id, $action);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function addUser($name, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare('insert into user_info (name, email, password) value (?, ?, ?)');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('sss', $name, $email, $hash);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $user_id = $this->conn->insert_id;

        return $user_id;
    }

    function addDocument($original_name, $file_name, $file_size, $extension)
    {
        $stmt = $this->conn->prepare('insert into document_info (original_name, file_name, file_size, extension, owner_id, folder_id) values (?, ?, ?, ?, ?, ?)');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('ssssii', $original_name, $file_name, $file_size, $extension, $_SESSION['user']['id'], $_SESSION['folder']['id']);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $documentId = $this->conn->insert_id;

        $action = 'UPLOAD';
        $this->logDocument($_SESSION['user']['id'], $documentId, $action);

        return $documentId;
    }

    function deleteDocument($id)
    {
        $stmt = $this->conn->prepare('delete from document_info where document_id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt = $this->conn->prepare(
            'insert into delete_log (user_id, document_id) VALUES (?, ?)'
        );

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('ii', $_SESSION['user']['id'], $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function createUserFolder($user_id)
    {
        $folder = '../../uploads/user/' . $user_id;
        if (!mkdir($folder, 0777, true) && !is_dir($folder)) {
            throw new Exception('Unable to create user folder');
        }

        $stmt = $this->conn->prepare('insert into user_folder (folder_name, user_id, parent_id) values (?, ?, ?)');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $parent_id = 1;
        $stmt->bind_param('sii', $user_id, $user_id, $parent_id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function getFolderPath($id)
    {
        $stmt = $this->conn->prepare('select * from user_folder where id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $folder = $stmt->get_result()->fetch_assoc();

        if (!$folder) {
            throw new Exception('folder not found');
        }

        if ($folder['folder_name'] === 'user') {
            return 'user';
        } elseif ($folder['folder_name'] === 'admin') {
            return 'admin';
        }

        return $this->getFolderPath($folder['parent_id']) . '/' . $folder['folder_name'];
    }

    function deleteUserFolder($id)
    {
        $stmt = $this->conn->prepare('select id from user_folder where user_id = ? and parent_id = 1');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $this->deleteFolder($result->fetch_assoc()['id']);
        }
    }

    function deleteFolder($folder_id)
    {
        $this->conn->begin_transaction();
        try {
            $this->deleteFolderHelper($folder_id);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    function deleteFolderHelper($folder_id)
    {

        $stmt = $this->conn->prepare('select folder_name, parent_id from user_folder where id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $folder_id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $folder = $stmt->get_result()->fetch_assoc();

        if ($folder['parent_id'] === null) {
            throw new Exception("Can't delete root directory.");
        }

        // delete child folders

        $stmt = $this->conn->prepare('select * from user_folder where parent_id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $folder_id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $this->deleteFolderHelper($row['id']);
        }

        // delete files
        $stmt = $this->conn->prepare('select file_name, extension from document_info where folder_id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $folder_id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $result = $stmt->get_result();

        $path = '../../uploads/' . $this->getFolderPath($folder_id);

        while ($file = $result->fetch_assoc()) {
            $filePath = $path . DIRECTORY_SEPARATOR . $file['file_name'] . '.' . $file['extension'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // delete files from database

        $stmt = $this->conn->prepare('delete from document_info where folder_id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $folder_id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        // delete physical folder
        if (is_dir($path)) {
            if (!rmdir($path)) {
                throw new Exception('unable to delete folder');
            }
        }

        // delete folder from db

        $stmt = $this->conn->prepare('delete from user_folder where id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $folder_id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function updateUser($id, $name, $email)
    {
        $stmt = $this->conn->prepare('update user_info set name = ?, email = ? where id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('ssi', $name, $email, $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function getAllUsers()
    {
        return mysqli_query($this->conn, 'select * from user_info');
    }

    function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare('select * from user_info where email = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('s', $email);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        return $stmt->get_result();
    }

    function getUserById($id)
    {
        $stmt = $this->conn->prepare('select * from user_info where id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        return $stmt->get_result();
    }

    function getDocumentById($id)
    {
        $stmt = $this->conn->prepare('select * from document_info where document_id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        return $stmt->get_result();
    }

    function getDocumentByFileName($file_name)
    {
        $stmt = $this->conn->prepare('select * from document_info where file_name = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('s', $file_name);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        return $stmt->get_result();
    }

    function getTotalStorage()
    {
        $sql = mysqli_query($this->conn, 'select sum(file_size) as total from document_info');
        $file = mysqli_fetch_assoc($sql);
        return $file['total'];
    }

    function getStoragePerUser()
    {
        $result = mysqli_query($this->conn, '
        select u.id as user_id,
        COALESCE(SUM(d.file_size), 0) AS total
        from user_info u
        left join document_info d
            on d.owner_id = u.id
        group by u.id;');

        return $result;
    }

    function getStorageById($id)
    {
        $stmt = $this->conn->prepare('
        select COALESCE(SUM(d.file_size), 0) AS total 
        from user_info u
        left join document_info d
        on d.owner_id = u.id
        where u.id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    function isOwner($id)
    {
        $stmt = $this->conn->prepare('select document_id from document_info where document_id = ? and owner_id = ?');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('ii', $id, $_SESSION['user']['id']);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    function addPermission($user_id, $document_id, $type)
    {
        $stmt = $this->conn->prepare('insert into document_user_permission (user_id, document_id, type) values (?, ?, ?)');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('iis', $user_id, $document_id, $type);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function logShare($sender_id, $receiver_id, $document_id)
    {
        $stmt = $this->conn->prepare('insert into share_log (sender_id , receiver_id, document_id) values (?, ?, ?)');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('iii', $sender_id, $receiver_id, $document_id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function sendPasswordEmail($user, $mail)
    {
        $otp = random_int(100000, 999999);
        $mail->Subject = 'Reset password';

        $mail->Body = "Password reset code: $otp";

        $mail->send();

        $expires_at = date('Y-m-d H:i:s', time() + (10 * 60));

        $sql = 'insert into password_reset(user_id, otp, expires_at) values (?, ?, ?)';
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('iss', $user['id'], $otp, $expires_at);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function sendInviteEmail($mail, $subject, $body)
    {
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
    }

    function queueMail($sender, $receiver, $file_name)
    {
        $sender = ucfirst($sender);

        $body = "$sender has given you access of $file_name.";

        $recipient = $receiver;
        $subject = "File Invitation";
        $stmt = $this->conn->prepare('insert into email_queue (recipient, subject, body) values (?, ?, ?)');

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('sss', $recipient, $subject, $body);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    function existsByOtp($otp)
    {
        $sql = 'select id from password_reset where otp = ? and expires_at > NOW()';
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('s', $otp);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    function changePassword($id, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = 'update user_info set password = ? where id = ?';
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param('si', $hash, $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }


        $this->logAction($id, 'PASSWORD_RESET');
    }
}
