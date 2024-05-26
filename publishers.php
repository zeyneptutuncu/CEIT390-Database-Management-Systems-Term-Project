<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('includes/config.php');

try {
    $sql = "SELECT publisher_name, address, phone_number, GROUP_CONCAT(book_name SEPARATOR ', ') as books, GROUP_CONCAT(ISBN SEPARATOR ', ') as isbns 
            FROM publishers 
            WHERE publisher_name IS NOT NULL 
            GROUP BY publisher_name, address, phone_number";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $publishers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPublishers = $stmt->rowCount();
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
    <title>Publishers</title>
    <style>
        .book-isbn-container {
            display: flex;
            flex-direction: column;
        }
        .center-align {
            text-align: center;
        }
        .add-publisher-button {
            background-color: #fd6f82; /* Pink color */
            color: white;
            border: none;
            padding: 6px 12px;
            text-decoration: none;
            margin-top: -10px;
            margin-bottom: 10px;
            display: inline-block;
            border-radius: 4px;
        }
        .add-publisher-button:hover {
            background-color: #ff5777; /* Darker pink on hover */
        }
        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .search-container input {
            margin-right: 10px;
        }
        .search-container .btn {
            background-color: #fd6f82; /* Pink color */
            border-color: #fd6f82; /* Pink color */
        }
        #searchResults {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include('includes/loginheader.php'); ?>
    <div class="container">
        <h1>Publishers</h1>
        <div class="text-end mb-3">
            <a href="add_publisher.php" class="add-publisher-button">Add Publisher</a>
        </div>
        <div class="search-container">
            <input type="text" class="form-control" id="searchInput" placeholder="Search publishers..." onkeydown="if (event.key === 'Enter') searchPublishers()">
            <button class="btn btn-primary" onclick="searchPublishers()">Search</button>
        </div>
        <div id="searchResults"></div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Publisher Name</th>
                    <th>Address</th>
                    <th>Phone Number</th>
                    <th>Books</th>
                    <th>ISBNs</th>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <th class="center-align">Edit</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="publisherTableBody">
                <?php foreach ($publishers as $publisher): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($publisher['publisher_name']); ?></td>
                        <td><?php echo htmlspecialchars($publisher['address']); ?></td>
                        <td><?php echo htmlspecialchars($publisher['phone_number']); ?></td>
                        <td>
                            <div class="book-isbn-container">
                                <?php
                                $books = explode(', ', $publisher['books']);
                                foreach ($books as $book) {
                                    echo htmlspecialchars($book) . '<br>';
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="book-isbn-container">
                                <?php
                                $isbns = explode(', ', $publisher['isbns']);
                                foreach ($isbns as $isbn) {
                                    echo htmlspecialchars($isbn) . '<br>';
                                }
                                ?>
                            </div>
                        </td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <td class="center-align">
                                <a href="edit_publisher.php?publisher=<?php echo urlencode($publisher['publisher_name']); ?>" class="btn btn-warning">Edit</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>Total Publishers: <?php echo $totalPublishers; ?></p>
    </div>
    <?php include('includes/footer.php'); ?>

    <script>
        function searchPublishers() {
            var input, filter, table, tr, td, i, j, txtValue, totalBooks, foundPublisher;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("publisherTableBody");
            tr = table.getElementsByTagName("tr");
            foundPublisher = false;

            for (i = 0; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            if (j == 0) { // Publisher name column
                                var isbns = td[4].getElementsByTagName('div')[0].innerText.split('\n');
                                totalBooks = isbns.filter(isbn => isbn.trim() !== "").length;
                                foundPublisher = true;
                                var publisherName = td[0].textContent || td[0].innerText;
                            }
                            break;
                        }
                    }
                }
            }
            var searchResults = document.getElementById("searchResults");
            if (foundPublisher) {
                searchResults.textContent = publisherName + " publisherına ait bastırdığı " + totalBooks + " kitap var.";
            } else {
                searchResults.textContent = "No publisher found.";
            }
        }
    </script>
</body>
</html>
