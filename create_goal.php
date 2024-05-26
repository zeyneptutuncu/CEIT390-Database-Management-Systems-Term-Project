<?php
session_start();
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $goal_name = $_POST['goal_name'];
    $target_books = $_POST['target_books'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO reading_goals (user_id, goal_name, target_books_count, completed_books_count, start_date, end_date, goal_status) VALUES (:user_id, :goal_name, :target_books_count, 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'not started')";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':goal_name', $goal_name, PDO::PARAM_STR);
    $stmt->bindParam(':target_books_count', $target_books, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: reading_goals.php');
    exit();
}
?>
