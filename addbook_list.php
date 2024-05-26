<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo "Unauthorized access!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['isbn']) && isset($_POST['list_id'])) {
    $isbn = $_POST['isbn'];
    $list_id = $_POST['list_id'];

    // Check if the book exists
    $sql = "SELECT * FROM books WHERE ISBN = :isbn";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->execute();
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo "The book with ISBN $isbn does not exist in the database.";
        exit();
    }

    // Check if the book is already in the list
    $sql = "SELECT * FROM list_items WHERE list_id = :list_id AND ISBN = :isbn";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':list_id', $list_id);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo "This book is already in the list.";
        exit();
    }

    // Add the book to the list
    $sql = "INSERT INTO list_items (list_id, ISBN) VALUES (:list_id, :isbn)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':list_id', $list_id);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->execute();

    echo "Book added successfully!";
}
?>
