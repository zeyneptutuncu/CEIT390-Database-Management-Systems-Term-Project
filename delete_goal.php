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
    
    $sql = "DELETE FROM reading_goals WHERE goal_id = :goal_id AND user_id = :user_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':goal_id', $goal_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false]);
exit();
