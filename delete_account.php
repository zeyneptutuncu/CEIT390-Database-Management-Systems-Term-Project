<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Kullanıcıyı ve ilişkili verileri silme
    $dbh->beginTransaction();
    
    // Reading goals tablosundan ilgili verileri sil
    $stmt = $dbh->prepare("DELETE FROM reading_goals WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete reading goals.");
    }

    // List_item'ları sil
    $stmt = $dbh->prepare("DELETE FROM list_items WHERE list_id IN (SELECT id FROM lists WHERE user_id = :user_id)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete list items.");
    }

    // Lists tablosundan ilgili verileri sil
    $stmt = $dbh->prepare("DELETE FROM lists WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete lists.");
    }

    // Kullanıcıyı sil
    $stmt = $dbh->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete user.");
    }

    $dbh->commit();

    // Kullanıcı çıkışını yap ve index sayfasına yönlendir
    session_destroy();
    header('Location: index.php');
    exit();
} catch (Exception $e) {
    $dbh->rollBack();
    echo 'Error: ' . $e->getMessage();
}
?>
