<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include(__DIR__ . '/includes/config.php'); // Config dosyasının tam yolu

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_name = $_POST['book_name'];
    $author_name = $_POST['author_name'];
    $isbn = $_POST['isbn'];
    $genre = $_POST['genre'];
    $publication_date = !empty($_POST['publication_date']) ? $_POST['publication_date'] : null;
    $publisher = !empty($_POST['publisher']) ? $_POST['publisher'] : null;
    $language = !empty($_POST['language']) ? $_POST['language'] : null;
    $translator_name = !empty($_POST['translator_name']) ? $_POST['translator_name'] : null;

    try {
        // First, insert the author if not exists
        $sql = "SELECT author_id FROM authors WHERE author_name = :author_name";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':author_name', $author_name);
        $stmt->execute();
        $author = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$author) {
            $sql = "INSERT INTO authors (author_name) VALUES (:author_name)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':author_name', $author_name);
            $stmt->execute();
            $author_id = $dbh->lastInsertId();
        } else {
            $author_id = $author['author_id'];
        }

        // Now, insert the book
        $sql = "INSERT INTO books (book_name, author_id, ISBN, genre, publication_date, publisher, language, translator_name) 
                VALUES (:book_name, :author_id, :isbn, :genre, :publication_date, :publisher, :language, :translator_name)";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':book_name', $book_name);
        $stmt->bindParam(':author_id', $author_id);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':genre', $genre);
        if ($publication_date) {
            $stmt->bindParam(':publication_date', $publication_date);
        } else {
            $stmt->bindValue(':publication_date', null, PDO::PARAM_NULL);
        }
        if ($publisher) {
            $stmt->bindParam(':publisher', $publisher);
        } else {
            $stmt->bindValue(':publisher', null, PDO::PARAM_NULL);
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

        header('Location: dashboard.php');
        exit();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
} else {
    header('Location: add_book.php');
    exit();
}
