<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include(__DIR__ . '/includes/config.php');

$user_role = $_SESSION['role'] ?? 'user';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_name = $_POST['book_name'];
    $author_name = $_POST['author_name'];
    $isbn = $_POST['isbn'];
    $pub_date = !empty($_POST['pub_date']) ? $_POST['pub_date'] : null;
    $genre = $_POST['genre'] ?? 'Unknown/Not Specified';
    $publisher_name = !empty($_POST['publisher_name']) ? $_POST['publisher_name'] : null;
    $language = !empty($_POST['language']) ? $_POST['language'] : null;
    $translator_name = !empty($_POST['translator_name']) ? $_POST['translator_name'] : null;
    $volume_count = null;

    $media_type = strtolower($_POST['media_type']) ?? 'no digital link';

    try {
        // Check if the ISBN already exists
        $isbn_check_sql = "SELECT COUNT(*) FROM books WHERE ISBN = :isbn";
        $isbn_check_stmt = $dbh->prepare($isbn_check_sql);
        $isbn_check_stmt->bindParam(':isbn', $isbn);
        $isbn_check_stmt->execute();
        $isbn_exists = $isbn_check_stmt->fetchColumn();

        if ($isbn_exists) {
            $error_message = "ISBN already exists!";
        } else {
            $dbh->beginTransaction();
            
            $sql = "INSERT INTO books (book_name, author_name, genre, publication_date, ISBN, volume_count, language, translator_name, publisher_name) 
                    VALUES (:book_name, :author_name, :genre, :pub_date, :isbn, :volume_count, :language, :translator_name, :publisher_name)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':book_name', $book_name);
            $stmt->bindParam(':author_name', $author_name);
            $stmt->bindParam(':genre', $genre);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->bindParam(':publisher_name', $publisher_name);

            if ($pub_date) {
                $stmt->bindParam(':pub_date', $pub_date);
            } else {
                $stmt->bindValue(':pub_date', null, PDO::PARAM_NULL);
            }
            if ($volume_count) {
                $stmt->bindParam(':volume_count', $volume_count);
            } else {
                $stmt->bindValue(':volume_count', null, PDO::PARAM_NULL);
            }
            if ($language) {
                $stmt->bindParam(':language', $language);
            } else {
                $stmt->bindValue(':language', null, PDO::PARAM_NULL);
            }
            if ($translator_name) {
                $stmt->bindParam(':translator_name', $translator_name);
            } else {
                $stmt->bindValue(':translator_name', null, PDO::PARAM_NULL);
            }

            $stmt->execute();

            $status_sql = "INSERT INTO book_status (ISBN, media_type) VALUES (:isbn, :media_type)";
            $status_stmt = $dbh->prepare($status_sql);
            $status_stmt->bindParam(':isbn', $isbn);
            $status_stmt->bindParam(':media_type', $media_type);
            $status_stmt->execute();

            $dbh->commit();

            if ($user_role === 'admin') {
                $success_message = "Book added successfully! You can add another book or return to the homepage.";
            } else {
                $success_message = "Book added successfully! For any errors or missing information, please contact us.";
            }
        }
    } catch (Exception $e) {
        $dbh->rollBack();
        $error_message = 'Error: ' . $e->getMessage();
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
    <title>Add Book</title>
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
    <?php include(__DIR__ . '/includes/loginheader.php'); ?>
    <div class="container main-content">
        <h2>Add Book</h2>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="book_name">Book Name <span class="required">*</span></label>
                <input type="text" class="form-control" id="book_name" name="book_name" required>
            </div>
            <div class="form-group">
                <label for="genre">Genre</label>
                <select class="form-control" id="genre" name="genre">
                    <?php foreach ($genres as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo ($g == 'Unknown/Not Specified') ? 'selected' : ''; ?>><?php echo $g; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="author_name">Author <span class="required">*</span></label>
                <input type="text" class="form-control" id="author_name" name="author_name" required>
            </div>
            <div class="form-group">
                <label for="isbn">ISBN <span class="required">*</span></label>
                <input type="text" class="form-control" id="isbn" name="isbn" required>
            </div>
            <div class="form-group">
                <label for="pub_date">Publication Date</label>
                <input type="date" class="form-control" id="pub_date" name="pub_date">
            </div>
            <div class="form-group">
                <label for="publisher_name">Publisher <span class="required">*</span></label>
                <input type="text" class="form-control" id="publisher_name" name="publisher_name" required>
            </div>
            <div class="form-group">
                <label for="language">Language</label>
                <input type="text" class="form-control" id="language" name="language">
            </div>
            <div class="form-group">
                <label for="translator_name">Translator Name</label>
                <input type="text" class="form-control" id="translator_name" name="translator_name">
            </div>
            <div class="form-group">
                <label for="media_type">Media Type <span class="required">*</span></label>
                <select class="form-control" id="media_type" name="media_type" onchange="toggleFileLink(this.value)">
                    <option value="">Select Media Type</option>
                    <?php foreach ($media_types as $type): ?>
                        <option value="<?php echo strtolower($type); ?>"><?php echo $type; ?></option>
                    <?php endforeach; ?>
                    <option value="no digital link">No digital link</option>
                </select>
                <span class="form-text text-muted">If no media type, select 'No digital link'.</span>
                <span class="form-text text-muted">If the book is available online, select the appropriate media type.</span>
            </div>
            <button type="submit" class="btn btn-primary" style="background-color: #fd6f82; border-color: #fd6f82;">Add Book</button>
        </form>
    </div>
    <?php include(__DIR__ . '/includes/footer.php'); ?>
</body>
</html>
