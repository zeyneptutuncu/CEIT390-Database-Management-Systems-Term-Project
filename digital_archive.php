<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include(__DIR__ . '/includes/config.php'); // Config dosyasının tam yolu

$filter_media_type = isset($_GET['media_type']) ? $_GET['media_type'] : '';

try {
    // Kullanıcının kullanıcı adını ve rolünü çekmek için
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT user_name, role FROM users WHERE user_id = :user_id";
    $user_stmt = $dbh->prepare($user_sql);
    $user_stmt->bindParam(':user_id', $user_id);
    $user_stmt->execute();
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    // Filtreleme için SQL sorgusu oluşturma
    $sql = "SELECT b.book_name, b.author_name, bs.media_type, b.ISBN 
            FROM books b 
            LEFT JOIN book_status bs ON b.ISBN = bs.ISBN 
            WHERE 1=1";
    
    if (!empty($filter_media_type)) {
        $sql .= " AND bs.media_type = :media_type";
    }

    $stmt = $dbh->prepare($sql);

    if (!empty($filter_media_type)) {
        $stmt->bindParam(':media_type', $filter_media_type);
    }

    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalBooks = count($books); // Toplam kitap sayısı
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Archive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style>
        .container {
            margin-top: 40px;
            margin-left: auto;
            margin-right: auto;
            max-width: 80%;
        }
        h1 {
            margin-bottom: 20px;
        }
        .table-container {
            margin-bottom: 40px; /* Logout butonuyla hizalamak için tablo altına boşluk ekleyin */
        }
        .footer {
            text-align: center;
            padding: 10px 0;
            background-color: #f8f9fa; /* Açık gri bir arka plan rengi */
            position: relative;
            width: 100%;
            bottom: 0;
        }
        .button-container {
            display: flex;
            justify-content: flex-end; /* Butonları sağa hizalamak için */
            margin-bottom: 20px;
        }
        .button-container .btn, .search-container .btn {
            margin-right: 10px;
            background-color: #fd6f82; /* Buton rengi */
            border-color: #fd6f82; /* Buton rengi */
        }
        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .search-container input {
            margin-right: 10px;
        }
        .search-result {
            margin-top: 20px;
        }
        .total-books {
            font-weight: bold;
            margin-bottom: 20px;
            color: #6c757d; /* Daha soluk bir renk */
        }
        .sortable {
            cursor: pointer;
        }
        .sortable:hover {
            color: #000; /* Siyah renk */
        }
        .sort-icon {
            margin-left: 5px;
        }
        .filter-button {
            background-color: #fd6f82 !important; /* Buton rengi */
            border-color: #fd6f82 !important; /* Buton rengi */
            color: #fff !important; /* Beyaz yazı rengi */
        }
        .filter-form .form-control {
            margin-right: 10px;
        }
        .no-media {
            color: red;
        }
    </style>
</head>
<body>
    <?php include(__DIR__ . '/includes/loginheader.php'); ?>

    <div class="container">
        <h1>Digital Archive, <?php echo htmlspecialchars($user['user_name'] ?? ''); ?>!</h1>

        <div class="search-container">
            <input type="text" class="form-control" id="searchInput" placeholder="Search book, authors..." onkeydown="if (event.key === 'Enter') searchBooks()">
            <button class="btn btn-primary" onclick="searchBooks()">Search</button>
        </div>

        <form method="GET" action="" class="filter-form mb-4 d-flex">
            <select name="media_type" class="form-control">
                <option value="">All Media Types</option>
                <option value="PDF" <?php echo $filter_media_type == 'PDF' ? 'selected' : ''; ?>>PDF</option>
                <option value="ePub" <?php echo $filter_media_type == 'ePub' ? 'selected' : ''; ?>>ePub</option>
            </select>
            <button type="submit" class="btn filter-button">Apply Filters</button>
        </form>

        <div class="total-books">Total Books: <?php echo $totalBooks; ?></div>

        <h2>Book List</h2>
        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="sortable" onclick="sortTable(0)">Title <span class="sort-icon">▲</span></th>
                        <th class="sortable" onclick="sortTable(1)">Author</th>
                        <th>Media Type</th>
                        <?php if ($user['role'] === 'admin'): ?>
                            <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="bookTableBody">
                    <?php if (!empty($books)): ?>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                            <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                            <td>
                                <?php if (!empty($book['media_type'])): ?>
                                    <?php echo htmlspecialchars($book['media_type']); ?>
                                <?php else: ?>
                                    <span class="no-media">&times;</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <td><a href="edit_book.php?isbn=<?php echo htmlspecialchars($book['ISBN']); ?>" class="btn btn-sm btn-warning">Edit</a></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No books found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include(__DIR__ . '/includes/footer.php'); ?>

    <script>
        function searchBooks() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("bookTableBody");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td");
                if (td.length > 0) {
                    if (td[0].innerHTML.toUpperCase().indexOf(filter) > -1 || td[1].innerHTML.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.querySelector(".table");
            switching = true;
            dir = "asc"; 

            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount ++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
            updateSortIcons(n, dir);
        }

        function updateSortIcons(n, dir) {
            var headers = document.querySelectorAll(".sortable");
            headers.forEach(function(header, index) {
                if (index === n) {
                    header.querySelector(".sort-icon").innerHTML = dir === "asc" ? "▲" : "▼";
                } else {
                    header.querySelector(".sort-icon").innerHTML = "";
                }
            });
        }
    </script>
</body>
</html>
