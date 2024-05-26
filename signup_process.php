<?php
include 'includes/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the username already exists
    $stmt = $dbh->prepare("SELECT * FROM users WHERE user_name = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'field' => 'username', 'message' => 'Username already exists. Please choose another one.']);
        exit();
    }

    // Check if the email already exists
    $stmt = $dbh->prepare("SELECT * FROM users WHERE mail_address = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'field' => 'email', 'message' => 'Email already exists. Please choose another one.']);
        exit();
    }

    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'field' => 'password', 'message' => 'Passwords do not match.']);
        exit();
    }

    // Insert the new user into the database
    $stmt = $dbh->prepare("INSERT INTO users (user_name, mail_address, password, role) VALUES (:username, :email, :password, 'user')");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
    exit();
}
?>
