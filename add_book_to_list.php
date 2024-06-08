<?php
session_start();
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $isbn = trim($_POST['isbn']);
    $list_id = intval($_POST['list_id']);
    $user_id = $_SESSION['user_id']; // Kullanıcı ID'sini al

    if (empty($isbn) || empty($list_id) || empty($user_id)) {
        echo "Error: Missing required data.";
        exit();
    }

    // Check if the book already exists in the list
    $sql = "SELECT COUNT(*) FROM list_items WHERE list_id = :list_id AND ISBN = :isbn";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':list_id', $list_id, PDO::PARAM_INT);
    $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo "Error: Book already exists in the list.";
    } else {
        $sql = "INSERT INTO list_items (list_id, ISBN, user_id) VALUES (:list_id, :isbn, :user_id)";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':list_id', $list_id, PDO::PARAM_INT);
        $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // Kullanıcı ID'sini bağla
        $stmt->execute();

        echo "Book added to the list successfully.";
    }
} else {
    echo "Invalid request method.";
}
?>
