<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$list_id = isset($_GET['list_id']) ? intval($_GET['list_id']) : 0;

// Fetch user lists
$sql = "SELECT lists.*, COUNT(list_items.ISBN) as book_count FROM lists LEFT JOIN list_items ON lists.id = list_items.list_id WHERE lists.user_id = :user_id GROUP BY lists.id";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch books in the selected list
if ($list_id > 0) {
    $sql = "SELECT books.book_name, books.author_name, list_items.ISBN 
            FROM list_items 
            INNER JOIN books ON list_items.ISBN = books.ISBN 
            WHERE list_items.list_id = :list_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':list_id', $list_id, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch list name
    $list_sql = "SELECT list_name FROM lists WHERE id = :list_id";
    $list_stmt = $dbh->prepare($list_sql);
    $list_stmt->bindParam(':list_id', $list_id, PDO::PARAM_INT);
    $list_stmt->execute();
    $list_name = $list_stmt->fetchColumn();
} else {
    $items = [];
    $list_name = '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_list'])) {
    $list_name = $_POST['list_name'];

    $sql = "INSERT INTO lists (user_id, list_name) VALUES (:user_id, :list_name)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':list_name', $list_name, PDO::PARAM_STR);
    $stmt->execute();

    header("Location: add_collection.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Collection</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        .container {
            flex: 1;
            display: flex;
        }

        .sidebar {
            width: 30%;
            padding: 20px;
            border-right: 1px solid #ddd;
        }

        .content {
            width: 70%;
            padding: 20px;
            position: relative;
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .btn-primary {
            background-color: #fd6f82;
            border-color: #fd6f82;
            color: white;
        }

        .btn-primary:hover {
            background-color: #d85067;
            border-color: #d85067;
        }

        .btn-delete {
            background-color: #ff4d4d;
            border-color: #ff4d4d;
            color: white;
            width: 120px;
            height: 35px;
            margin-left: 10px;
        }

        .btn-delete:hover {
            background-color: #cc0000;
            border-color: #cc0000;
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            text-align: center;
        }

        .btn-close {
            background: #fd6f82;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-close:hover {
            background: #d85067;
        }

        .btn-close:focus {
            outline: none;
        }

        .search-container {
            display: flex;
            margin-top: 20px;
            gap: 10px; /* Added gap for spacing */
        }

        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .search-container button {
            padding: 10px 20px;
            border: 1px solid #fd6f82;
            background-color: #fd6f82;
            color: white;
            border-radius: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-start; /* Change alignment */
            margin-top: 20px;
        }

        .delete-button-container {
            position: absolute;
            bottom: 20px;
            right: 20px;
        }

        .footer {
            text-align: center;
            padding: 10px 0;
            background-color: #f8f9fa;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>
    <?php include('includes/loginheader.php'); ?>

    <div class="container">
        <div class="sidebar">
            <h3>My Lists</h3>
            <?php if (empty($lists)): ?>
                <p>No lists yet. Create a list to get started.</p>
            <?php else: ?>
                <?php foreach ($lists as $list): ?>
                    <div class="list-item">
                        <span><?php echo htmlspecialchars($list['list_name']); ?> (<?php echo $list['book_count']; ?>)</span>
                        <a href="add_collection.php?list_id=<?php echo urlencode($list['id']); ?>">View</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <button class="btn-primary" id="createListBtn">Create a List</button>
        </div>
        <div class="content">
            <?php if ($list_id > 0): ?>
                <h3><?php echo htmlspecialchars($list_name); ?> (<?php echo count($items); ?>)</h3>
                <?php if (!empty($items)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book Name</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['book_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['author_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['ISBN']); ?></td>
                                    <td><button class="btn-delete" data-isbn="<?php echo $item['ISBN']; ?>" data-list-id="<?php echo $list_id; ?>">Delete</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No items in this list.</p>
                <?php endif; ?>
                <form method="POST" id="searchForm" class="search-container">
                    <input type="hidden" name="list_id" value="<?php echo htmlspecialchars($list_id); ?>">
                    <input type="text" name="search_term" id="search_term" placeholder="Enter ISBN" required>
                    <button type="submit" class="btn-primary">Search</button>
                </form>
                <div id="searchResults">
                    <!-- Search results will be displayed here -->
                </div>
                <div class="delete-button-container">
                    <button type="button" id="deleteListBtn" class="btn-delete">Delete List</button>
                </div>
            <?php else: ?>
                <p>Select a list to view or add books.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="popup" id="createListPopup">
        <div class="popup-content">
            <form method="POST" action="">
                <h4>Create a New List</h4>
                <div class="form-group">
                    <label for="list_name">List Name</label>
                    <input type="text" name="list_name" id="list_name" required>
                </div>
                <button type="submit" name="create_list" class="btn-primary">Create</button>
                <button type="button" class="btn-close" id="closePopupBtn"><i class="fas fa-times"></i></button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createListBtn').addEventListener('click', function() {
            document.getElementById('createListPopup').style.display = 'flex';
        });

        document.getElementById('closePopupBtn').addEventListener('click', function() {
            document.getElementById('createListPopup').style.display = 'none';
        });

        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('search_books.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('searchResults').innerHTML = data;
            });
        });

        document.getElementById('searchResults').addEventListener('click', function(e) {
            if (e.target && e.target.matches('button.add-book')) {
                const isbn = e.target.getAttribute('data-isbn');
                const listId = e.target.getAttribute('data-list-id');
                const formData = new FormData();
                formData.append('isbn', isbn);
                formData.append('list_id', listId);
                fetch('add_book_to_list.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                });
            }
        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const isbn = this.getAttribute('data-isbn');
                const listId = this.getAttribute('data-list-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to delete this book from the list?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('delete_book_from_list.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ list_id: listId, isbn: isbn })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'The book has been deleted from the list.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });

        document.getElementById('deleteListBtn').addEventListener('click', function() {
            const listId = <?php echo $list_id; ?>;
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete this list?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('delete_list.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ list_id: listId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Deleted!',
                                'The list has been deleted.',
                                'success'
                            ).then(() => {
                                window.location.href = 'add_collection.php';
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'There was a problem deleting the list.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    </script>
    <div class="footer">
        &copy; CEIT390 | Online Library Management System
    </div>
</body>
</html>
