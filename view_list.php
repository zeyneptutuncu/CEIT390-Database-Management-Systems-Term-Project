<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo "Unauthorized access!";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_term']) && isset($_POST['list_id'])) {
    $search_term = $_POST['search_term'];
    $list_id = $_POST['list_id'];

    // Check if the search term is an ISBN
    if (is_numeric($search_term)) {
        $isbn = $search_term;
        $sql = "SELECT * FROM books WHERE ISBN = :isbn";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->execute();
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Check if the search term is a book name or author name
        $sql = "SELECT * FROM books WHERE book_name LIKE :search_term OR author_name LIKE :search_term";
        $stmt = $dbh->prepare($sql);
        $like_search_term = '%' . $search_term . '%';
        $stmt->bindParam(':search_term', $like_search_term);
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (!empty($book)) {
        echo '<div class="search-result-item">';
        echo '<span>' . htmlspecialchars($book['book_name']) . ' by ' . htmlspecialchars($book['author_name']) . '</span>';
        echo '<button class="btn btn-primary add-book-btn" data-isbn="' . htmlspecialchars($book['ISBN']) . '">Add</button>';
        echo '</div>';
    } elseif (!empty($books)) {
        foreach ($books as $book) {
            echo '<div class="search-result-item">';
            echo '<span>' . htmlspecialchars($book['book_name']) . ' by ' . htmlspecialchars($book['author_name']) . '</span>';
            echo '<button class="btn btn-primary add-book-btn" data-isbn="' . htmlspecialchars($book['ISBN']) . '">Add</button>';
            echo '</div>';
        }
    } else {
        echo "Book not found in the database.";
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['list_id'])) {
    $list_id = $_GET['list_id'];

    $sql = "SELECT l.list_name, b.book_name, b.author_name, b.ISBN 
            FROM list_items li
            INNER JOIN lists l ON li.list_id = l.id
            INNER JOIN books b ON li.ISBN = b.ISBN
            WHERE l.id = :list_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':list_id', $list_id);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT list_name FROM lists WHERE id = :list_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':list_id', $list_id);
    $stmt->execute();
    $list = $stmt->fetch(PDO::FETCH_ASSOC);

    echo '<h3>' . htmlspecialchars($list['list_name']) . '</h3>';
    echo '<table>';
    echo '<tr><th>Book Name</th><th>Author</th><th>ISBN</th></tr>';
    foreach ($items as $item) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($item['book_name']) . '</td>';
        echo '<td>' . htmlspecialchars($item['author_name']) . '</td>';
        echo '<td>' . htmlspecialchars($item['ISBN']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<form id="search-form">';
    echo '<input type="hidden" id="list-id" value="' . htmlspecialchars($list_id) . '">';
    echo '<div class="form-group">';
    echo '<label for="search-term">Add Book to List</label>';
    echo '<input type="text" id="search-term" name="search_term" placeholder="Enter ISBN, Book Name, or Author Name" required>';
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary">Search</button>';
    echo '</form>';
    echo '<div id="search-results"></div>';
    exit();
}
