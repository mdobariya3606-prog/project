<?php
include '../session.php';
include '../../config/bootstrap.php';
date_default_timezone_set('Asia/Kolkata');

class Helper
{
    public mysqli $conn;

    function __construct($conn)
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
            die('already logged out');
        }
    }

    function alreadyLoggedIn()
    {
        if (!empty($_SESSION)) {
            die('already logged in');
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
            'INSERT INTO audit_log (user_id, action) VALUES (?, ?)'
        );

        if (!$stmt) {
            die($this->conn->error);
        }

        $stmt->bind_param('is', $userId, $action);

        if (!$stmt->execute()) {
            die($stmt->error);
        }
    }

    function addUser($name, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare('insert into user_info (name, email, password) value (?, ?, ?)');
        $stmt->bind_param('sss', $name, $email, $hash);
        $stmt->execute();
    }

    function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare('select * from user_info where email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result();
    }

    function getUserById($id)
    {
        $stmt = $this->conn->prepare('select * from user_info where id = ?');
        $stmt->bind_param('s', $id);
        $stmt->execute();
        return $stmt->get_result();
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
        $stmt->bind_param('iss', $user['id'], $otp, $expires_at);
        $stmt->execute();
    }

    function existsByOtp($otp) {
        $sql = 'select id from password_reset where otp = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result->num_rows != 0) ? true : false;
    }
}
