<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_role = $_SESSION['role'] ?? 'user';
$book = [];
$successMessage = '';
$errorMessage = '';

if (isset($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
    $sql = "SELECT b.*, bs.media_type FROM books b LEFT JOIN book_status bs ON b.ISBN = bs.ISBN WHERE b.ISBN = :isbn";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->execute();
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo "No book found with ISBN: $isbn";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_book'])) {
        $isbn = $_POST['isbn'];
        $book_name = $_POST['book_name'];
        $author_name = $_POST['author_name'];
        $genre = $_POST['genre'] ?? 'Unknown/Not Specified';
        $publication_date = !empty($_POST['publication_date']) ? $_POST['publication_date'] : null;
        $publisher_name = $_POST['publisher_name'] ?? null;
        $language = $_POST['language'] ?? null;
        $translator_name = $_POST['translator_name'] ?? null;
        $media_type = $_POST['media_type'] ?? null;

        try {
            $dbh->beginTransaction();
            
            $sql = "UPDATE books SET book_name = :book_name, author_name = :author_name, genre = :genre, 
                    publication_date = :publication_date, publisher_name = :publisher_name, language = :language, 
                    translator_name = :translator_name WHERE ISBN = :isbn";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->bindParam(':book_name', $book_name);
            $stmt->bindParam(':author_name', $author_name);
            $stmt->bindParam(':genre', $genre);
            $stmt->bindParam(':publication_date', $publication_date);
            $stmt->bindParam(':publisher_name', $publisher_name);
            $stmt->bindParam(':language', $language);
            $stmt->bindParam(':translator_name', $translator_name);
            $stmt->execute();

            // Update book_status table
            $status_sql = "UPDATE book_status SET media_type = :media_type WHERE ISBN = :isbn";
            $status_stmt = $dbh->prepare($status_sql);
            $status_stmt->bindParam(':isbn', $isbn);
            $status_stmt->bindParam(':media_type', $media_type);
            $status_stmt->execute();

            $dbh->commit();

            $successMessage = 'Book updated successfully!';
        } catch (Exception $e) {
            $dbh->rollBack();
            $errorMessage = 'Error: ' . $e->getMessage();
        }
    } elseif (isset($_POST['delete_book'])) {
        $isbn = $_POST['isbn'];

        try {
            $dbh->beginTransaction();

            // Delete from book_status first to maintain referential integrity
            $status_sql = "DELETE FROM book_status WHERE ISBN = :isbn";
            $status_stmt = $dbh->prepare($status_sql);
            $status_stmt->bindParam(':isbn', $isbn);
            $status_stmt->execute();

            // Delete the book
            $sql = "DELETE FROM books WHERE ISBN = :isbn";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->execute();

            $dbh->commit();

            $successMessage = 'Book deleted successfully!';
        } catch (Exception $e) {
            $dbh->rollBack();
            $errorMessage = 'Error: ' . $e->getMessage();
        }
    }
}

$genres = ["Historical Fiction", "Literary Fiction", "Mystery", "Romance", "Science Fiction", "Fantasy", "Biography", "Non-fiction", "Unknown/Not Specified"];
$media_types = ["PDF", "ePub"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="assets/css/edit_book.css">
    <style>
        .required {
            color: red;
        }
        .media-type-toggle {
            display: none;
        }
    </style>
</head>
<body>
    <?php include('includes/loginheader.php'); ?>

    <div class="container main-content">
        <h2>Edit Book</h2>
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="isbn">ISBN</label>
                <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['ISBN']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="genre">Genre</label>
                <select class="form-control" id="genre" name="genre">
                    <?php foreach ($genres as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo ($g == $book['genre']) ? 'selected' : ''; ?>><?php echo $g; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="book_name">Book Name <span class="required">*</span></label>
                <input type="text" class="form-control" id="book_name" name="book_name" value="<?php echo htmlspecialchars($book['book_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="author_name">Author Name <span class="required">*</span></label>
                <input type="text" class="form-control" id="author_name" name="author_name" value="<?php echo htmlspecialchars($book['author_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="publication_date">Publication Date</label>
                <input type="date" class="form-control" id="publication_date" name="publication_date" value="<?php echo htmlspecialchars($book['publication_date'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="publisher_name">Publisher</label>
                <input type="text" class="form-control" id="publisher_name" name="publisher_name" value="<?php echo htmlspecialchars($book['publisher_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="language">Language</label>
                <input type="text" class="form-control" id="language" name="language" value="<?php echo htmlspecialchars($book['language'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="translator_name">Translator Name</label>
                <input type="text" class="form-control" id="translator_name" name="translator_name" value="<?php echo htmlspecialchars($book['translator_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="media_type">Media Type</label>
                <select class="form-control" id="media_type" name="media_type">
                    <option value="">Select Media Type</option>
                    <?php foreach ($media_types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo ($type == $book['media_type']) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="update_book" class="btn btn-primary" style="background-color: #fd6f82; border-color: #fd6f82;">Update Book</button>
            <button type="submit" name="delete_book" class="btn btn-danger">Delete Book</button>
        </form>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>
