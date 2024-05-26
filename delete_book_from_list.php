<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $isbn = $data['isbn'];
    $list_id = intval($data['list_id']);

    if ($list_id > 0 && !empty($isbn)) {
        try {
            $sql = "DELETE FROM list_items WHERE ISBN = :isbn AND list_id = :list_id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
            $stmt->bindParam(':list_id', $list_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Book deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Book not found in the list.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid list ID or ISBN.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
