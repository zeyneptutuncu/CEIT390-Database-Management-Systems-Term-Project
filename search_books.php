<?php
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_term = trim($_POST['search_term']);
    $list_id = isset($_POST['list_id']) ? intval($_POST['list_id']) : 0;

    // Check if the search term is an ISBN
    if (is_numeric($search_term) && strlen($search_term) == 13) {
        $sql = "SELECT * FROM books WHERE ISBN = :search_term AND ISBN NOT IN (SELECT ISBN FROM list_items WHERE list_id = :list_id)";
    } else {
        echo "<p>Please enter a valid 13-digit ISBN.</p>";
        exit();
    }

    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':search_term', $search_term, PDO::PARAM_STR);
    $stmt->bindParam(':list_id', $list_id, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            echo "<p>No books found matching your search criteria.</p>";
        } else {
            echo '<table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Book Name</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
            foreach ($results as $index => $book) {
                echo '<tr>
                        <td>' . ($index + 1) . '</td>
                        <td>' . htmlspecialchars($book['book_name']) . '</td>
                        <td>' . htmlspecialchars($book['author_name']) . '</td>
                        <td>' . htmlspecialchars($book['ISBN']) . '</td>
                        <td><button class="btn-primary add-book" data-isbn="' . htmlspecialchars($book['ISBN']) . '" data-list-id="' . htmlspecialchars($list_id) . '">Add</button></td>
                    </tr>';
            }
            echo '</tbody></table>';
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "<p>Invalid request method.</p>";
}
?>
