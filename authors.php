<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('includes/config.php'); // Config dosyasının yolu

$order = isset($_GET['order']) ? $_GET['order'] : 'asc';

try {
    // Yazar listesini çekmek için
    $sql = "SELECT * FROM authors ORDER BY author_name " . ($order === 'desc' ? 'DESC' : 'ASC');
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Yazarları gruplamak için
    $groupedAuthors = [];
    foreach ($authors as $author) {
        $groupedAuthors[$author['author_name']]['biography'] = $author['biography'];
        $groupedAuthors[$author['author_name']]['books'][] = $author['book_name'];
        $groupedAuthors[$author['author_name']]['author_id'] = $author['author_id'];
    }
    $totalAuthors = count($groupedAuthors);
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
    <title>Authors</title>
    <style>
        .book-isbn-container {
            display: flex;
            flex-direction: column;
        }
        .center-align {
            text-align: center;
        }
        .btn-pink {
            background-color: #fd6f82 !important; /* Pink color */
            color: white !important;
            border: none !important;
            display: inline-block !important;
        }
        .btn-pink:hover {
            background-color: #ff5777 !important; /* Darker pink on hover */
        }
        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .search-container input {
            margin-right: 10px;
        }
        .button-container {
            display: flex;
            justify-content: flex-end; /* Butonları sağa hizalamak için */
            margin-bottom: 20px;
        }
        .sortable-header {
            text-decoration: none;
            color: inherit; /* Diğer kolon isimleri ile aynı renk */
        }
        .sortable-header:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include('includes/loginheader.php'); ?> <!-- Header dosyasını dahil et -->
    <div class="container">
        <h1>Authors</h1>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="button-container">
                <a href="add_author.php" class="btn btn-pink">Add Author</a>
            </div>
        <?php endif; ?>
        <div class="search-container">
            <input type="text" class="form-control" id="searchInput" placeholder="Search authors..." onkeydown="if (event.key === 'Enter') searchAuthors()">
            <button class="btn btn-pink" onclick="searchAuthors()">Search</button>
        </div>
        <div id="searchResults"></div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>
                        <a href="?order=<?php echo $order === 'asc' ? 'desc' : 'asc'; ?>" class="sortable-header">
                            Author Name <?php echo $order === 'asc' ? '↑' : '↓'; ?>
                        </a>
                    </th>
                    <th>Biography</th>
                    <th>Book Name</th>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <th class="center-align">Edit</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="authorTableBody">
                <?php foreach ($groupedAuthors as $authorName => $authorData): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($authorName); ?></td>
                        <td><?php echo htmlspecialchars($authorData['biography']); ?></td>
                        <td>
                            <div class="book-isbn-container">
                                <?php
                                foreach ($authorData['books'] as $book) {
                                    echo htmlspecialchars($book) . '<br>';
                                }
                                ?>
                            </div>
                        </td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <td class="center-align">
                                <a href="edit_author.php?id=<?php echo $authorData['author_id']; ?>" class="btn btn-warning">Edit</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>Total Authors: <?php echo $totalAuthors; ?></p>
    </div>
    <?php include('includes/footer.php'); ?> <!-- Footer dosyasını dahil et -->

    <script>
        function searchAuthors() {
            var input, filter, table, tr, td, i, j, txtValue, totalBooks, foundAuthor;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("authorTableBody");
            tr = table.getElementsByTagName("tr");
            foundAuthor = false;

            for (i = 0; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            if (j == 0) { // Author name column
                                var books = td[2].getElementsByTagName('div')[0].innerText.split('\n');
                                totalBooks = books.filter(book => book.trim() !== "").length;
                                foundAuthor = true;
                                var authorName = td[0].textContent || td[0].innerText;
                            }
                            break;
                        }
                    }
                }
            }
            var searchResults = document.getElementById("searchResults");
            if (foundAuthor) {
                searchResults.textContent = authorName + " yazarın " + totalBooks + " kitabı var.";
            } else {
                searchResults.textContent = "No author found.";
            }
        }
    </script>
</body>
</html>
