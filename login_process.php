<?php
session_start();
include('includes/config.php');

$response = ['success' => false, 'message' => 'Invalid email or password.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT user_id, user_name, role FROM users WHERE mail_address = :email AND password = :password";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['user_name'];
        $_SESSION['role'] = $user['role'];
        $response['success'] = true;
    } else {
        $response['message'] = 'Invalid email or password.';
    }
}

echo json_encode($response);
?>
