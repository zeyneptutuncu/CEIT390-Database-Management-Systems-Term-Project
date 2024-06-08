<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('includes/config.php');

$order = isset($_GET['order']) ? $_GET['order'] : 'asc';

try {
    $sql = "SELECT * FROM translators WHERE translator_name IS NOT NULL ORDER BY translator_name " . ($order === 'desc' ? 'DESC' : 'ASC');
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $translators = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $groupedTranslators = [];
    foreach ($translators as $translator) {
        if (!isset($groupedTranslators[$translator['translator_name']])) {
            $groupedTranslators[$translator['translator_name']] = [
                'translator_id' => $translator['translator_id'],
                'language' => $translator['language'],
                'ISBN' => []
            ];
        }
        $groupedTranslators[$translator['translator_name']]['ISBN'][] = $translator['ISBN'];
    }
    $totalTranslators = count($groupedTranslators);
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
    <title>Translators</title>
    <style>
        .book-isbn-container {
            display: flex;
            flex-direction: column;
        }
        .center-align {
            text-align: center;
        }
        .btn-pink {
            background-color: #fd6f82 !important;
            color: white !important;
            border: none !important;
            display: inline-block !important;
        }
        .btn-pink:hover {
            background-color: #ff5777 !important;
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
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        #searchResults {
            margin-top: 10px;
            font-weight: bold;
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
    <?php include('includes/loginheader.php'); ?>
    <div class="container">
        <h1>Translators</h1>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="button-container">
                <a href="add_translator.php" class="btn btn-pink">Add Translator</a>
            </div>
        <?php endif; ?>
        <div class="search-container">
            <input type="text" class="form-control" id="searchInput" placeholder="Search translators..." onkeydown="if (event.key === 'Enter') searchTranslators()">
            <button class="btn btn-pink" onclick="searchTranslators()">Search</button>
        </div>
        <div id="searchResults"></div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>
                        <a href="?order=<?php echo $order === 'asc' ? 'desc' : 'asc'; ?>" class="sortable-header">
                            Translator Name <?php echo $order === 'asc' ? '↑' : '↓'; ?>
                        </a>
                    </th>
                    <th>Languages</th>
                    <th>Book Count</th>
                    <th>ISBNs</th>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <th class="center-align">Edit</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="translatorTableBody">
                <?php foreach ($groupedTranslators as $translatorName => $translatorData): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($translatorName); ?></td>
                        <td><?php echo htmlspecialchars($translatorData['language']); ?></td>
                        <td><?php echo count($translatorData['ISBN']); ?></td> <!-- ISBN sayısını buraya ekliyoruz -->
                        <td>
                            <div class="book-isbn-container">
                                <?php
                                foreach ($translatorData['ISBN'] as $isbn) {
                                    echo htmlspecialchars($isbn) . '<br>';
                                }
                                ?>
                            </div>
                        </td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <td class="center-align">
                                <a href="edit_translator.php?id=<?php echo $translatorData['translator_id']; ?>" class="btn btn-warning">Edit</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>Total Translators: <?php echo $totalTranslators; ?></p>
    </div>
    <?php include('includes/footer.php'); ?>

    <script>
        function searchTranslators() {
            var input, filter, table, tr, td, i, j, txtValue, showRow;
            var totalBooks = 0;
            var foundTranslator = false;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("translatorTableBody");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            if (j == 0) { // Assume the first column contains the translator's name
                                totalBooks = td[2].textContent || td[2].innerText; // Assume the third column contains the book count
                                foundTranslator = true;
                            }
                            break;
                        }
                    }
                }
            }
            var searchResults = document.getElementById("searchResults");
            if (foundTranslator) {
                searchResults.textContent = "Total books by searched translator: " + totalBooks;
            } else {
                searchResults.textContent = "No translator found.";
            }
        }
    </script>
</body>
</html>
