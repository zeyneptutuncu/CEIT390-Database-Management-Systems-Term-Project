<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$list_name = $_SESSION['list_name'];
$search_results = $_SESSION['search_results'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['select_book'])) {
    $book_isbn = $_POST['book_isbn'];

    $sql = "INSERT INTO list_items (list_name, book_isbn) VALUES (:list_name, :book_isbn)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':list_name', $list_name);
    $stmt->bindParam(':book_isbn', $book_isbn);
    $stmt->execute();

    unset($_SESSION['search_results']);
    unset($_SESSION['list_name']);
    header("Location: add_collection.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Book</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('includes/loginheader.php'); ?>

    <div class="container">
        <h3>Select Book</h3>
        <?php if (!empty($search_results)): ?>
            <form method="POST">
                <table>
                    <tr>
                        <th>Select</th>
                        <th>Book Name</th>
                        <th>Author</th>
                        <th>ISBN</th>
                    </tr>
                    <?php foreach ($search_results as $book): ?>
                        <tr>
                            <td><input type="radio" name="book_isbn" value="<?php echo htmlspecialchars($book['ISBN']); ?>" required></td>
                            <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                            <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                            <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <button type="submit" name="select_book" class="btn btn-primary">Add Selected Book</button>
            </form>
        <?php else: ?>
            <p>No books found.</p>
        <?php endif; ?>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>
