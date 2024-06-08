<?php
include('includes/config.php'); // Include your database connection

if (isset($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
    $query = "DELETE FROM books WHERE ISBN = :isbn";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':isbn', $isbn);

    if ($stmt->execute()) {
        header('Location: dashboard.php');
    } else {
        echo "Error deleting record: " . $stmt->errorInfo();
    }
}
?>
