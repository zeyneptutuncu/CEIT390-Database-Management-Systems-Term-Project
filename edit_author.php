<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('includes/config.php');

$author_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $biography = $_POST['biography'];

    try {
        // Güncelleme işlemi
        $sql = "UPDATE authors SET biography = :biography WHERE author_id = :author_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':biography', $biography);
        $stmt->bindParam(':author_id', $author_id);
        $stmt->execute();

        header('Location: authors.php');
        exit();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    try {
        $dbh->beginTransaction();

        // Yazarın adını al
        $sql = "SELECT author_name FROM authors WHERE author_id = :author_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':author_id', $author_id);
        $stmt->execute();
        $author = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($author) {
            $author_name = $author['author_name'];

            // Kitapların ISBN'lerini al
            $sql = "SELECT ISBN FROM books WHERE author_name = :author_name";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':author_name', $author_name);
            $stmt->execute();
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Kitapları ve ilişkili verileri sil
            foreach ($books as $book) {
                $isbn = $book['ISBN'];

                // Kitap durumlarını sil
                $sql = "DELETE FROM book_status WHERE ISBN = :isbn";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':isbn', $isbn);
                $stmt->execute();

                // Çevirmenlerden kitabı sil
                $sql = "DELETE FROM translators WHERE ISBN = :isbn";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':isbn', $isbn);
                $stmt->execute();

                // Listelerden kitabı sil
                $sql = "DELETE FROM list_items WHERE ISBN = :isbn";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':isbn', $isbn);
                $stmt->execute();

                // Kitabı sil
                $sql = "DELETE FROM books WHERE ISBN = :isbn";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':isbn', $isbn);
                $stmt->execute();
            }

            // Yazarı sil
            $sql = "DELETE FROM authors WHERE author_id = :author_id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':author_id', $author_id);
            $stmt->execute();
        }

        $dbh->commit();

        header('Location: authors.php');
        exit();
    } catch (Exception $e) {
        $dbh->rollBack();
        echo 'Error: ' . $e->getMessage();
    }
}

try {
    $sql = "SELECT * FROM authors WHERE author_id = :author_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':author_id', $author_id);
    $stmt->execute();
    $author = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <title>Edit Author</title>
    <style>
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>
</head>
<body>
    <?php include('includes/loginheader.php'); ?>
    <div class="container">
        <h1>Edit Author</h1>
        <form method="post">
            <div class="form-group mb-3">
                <label for="author_name" class="form-label">Author Name</label>
                <input type="text" class="form-control" id="author_name" name="author_name" value="<?php echo htmlspecialchars($author['author_name']); ?>" readonly>
            </div>
            <div class="form-group mb-3">
                <label for="biography" class="form-label">Biography</label>
                <textarea class="form-control" id="biography" name="biography"><?php echo htmlspecialchars($author['biography']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Author</button>
            <a href="edit_author.php?id=<?php echo $author_id; ?>&delete=1" class="btn btn-danger">Delete Author</a>
        </form>
    </div>
    <?php include('includes/footer.php'); ?>
</body>
</html>
