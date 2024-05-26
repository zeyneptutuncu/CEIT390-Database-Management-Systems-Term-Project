<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include('includes/config.php'); // Config dosyasının yolu

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authorName = $_POST['author_name'];
    $biography = $_POST['biography'];
    $bookName = $_POST['book_name'];
    $isbn = $_POST['isbn'];

    try {
        $sql = "INSERT INTO authors (author_name, biography, book_name, ISBN) VALUES (:author_name, :biography, :book_name, :isbn)";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':author_name', $authorName);
        $stmt->bindParam(':biography', $biography);
        $stmt->bindParam(':book_name', $bookName);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->execute();
        header('Location: authors.php');
        exit();
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <title>Add Author</title>
</head>
<body>
    <?php include('includes/loginheader.php'); ?> <!-- Header dosyasını dahil et -->
    <div class="container">
        <h1>Add Author</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="author_name">Author Name</label>
                <input type="text" class="form-control" id="author_name" name="author_name" required>
            </div>
            <div class="form-group">
                <label for="biography">Biography</label>
                <textarea class="form-control" id="biography" name="biography" required></textarea>
            </div>
            <div class="form-group">
                <label for="book_name">Book Name</label>
                <input type="text" class="form-control" id="book_name" name="book_name" required>
            </div>
            <div class="form-group">
                <label for="isbn">ISBN</label>
                <input type="text" class="form-control" id="isbn" name="isbn" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Author</button>
        </form>
    </div>
    <?php include('includes/footer.php'); ?> <!-- Footer dosyasını dahil et -->
</body>
</html>
