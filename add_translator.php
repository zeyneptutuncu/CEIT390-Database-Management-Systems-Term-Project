<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $translator_name = $_POST['translator_name'];
    $translated_language = $_POST['translated_language'];
    $translated_book_count = max(0, (int)$_POST['translated_book_count']); // Ensure non-negative book count
    $isbn = $_POST['isbn'];

    try {
        $sql = "INSERT INTO translators (translator_name, translated_language, translated_book_count, ISBN) VALUES (:translator_name, :translated_language, :translated_book_count, :isbn)";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':translator_name', $translator_name);
        $stmt->bindParam(':translated_language', $translated_language);
        $stmt->bindParam(':translated_book_count', $translated_book_count);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->execute();
        header('Location: translators.php');
        exit();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
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
    <title>Add Translator</title>
</head>
<body>
    <?php include('includes/loginheader.php'); ?> <!-- Header dosyasını dahil et -->
    <div class="container">
        <h1>Add Translator</h1>
        <form method="POST" action="add_translator.php">
            <div class="mb-3">
                <label for="translator_name" class="form-label">Translator Name</label>
                <input type="text" class="form-control" id="translator_name" name="translator_name" required>
            </div>
            <div class="mb-3">
                <label for="translated_language" class="form-label">Language</label>
                <input type="text" class="form-control" id="translated_language" name="translated_language" required>
            </div>
            
            <div class="mb-3">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" class="form-control" id="isbn" name="isbn" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Translator</button>
        </form>
    </div>
    <?php include('includes/footer.php'); ?> <!-- Footer dosyasını dahil et -->
</body>
</html>
