<?php
session_start();
include('includes/config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['goal_id'])) {
    $goal_id = intval($data['goal_id']);
    $target_books = isset($data['target_books']) ? intval($data['target_books']) : null;
    $completed_books = isset($data['completed_books']) ? intval($data['completed_books']) : null;

    if ($target_books !== null && $target_books > 0) {
        $sql = "UPDATE reading_goals SET target_books_count = :target_books WHERE goal_id = :goal_id AND user_id = :user_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':target_books', $target_books, PDO::PARAM_INT);
        $stmt->bindParam(':goal_id', $goal_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if ($completed_books !== null && $completed_books > 0) {
        $sql = "UPDATE reading_goals SET completed_books_count = completed_books_count + :completed_books WHERE goal_id = :goal_id AND user_id = :user_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':completed_books', $completed_books, PDO::PARAM_INT);
        $stmt->bindParam(':goal_id', $goal_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    $sql = "SELECT target_books_count, completed_books_count FROM reading_goals WHERE goal_id = :goal_id AND user_id = :user_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':goal_id', $goal_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $goal = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'target_books' => $goal['target_books_count'], 'completed_books' => $goal['completed_books_count']]);
    exit();
}

echo json_encode(['success' => false]);
exit();
