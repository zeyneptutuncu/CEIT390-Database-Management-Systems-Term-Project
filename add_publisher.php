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
    $publisher_name = $_POST['publisher_name'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $book_name = $_POST['book_name'];
    $isbn = $_POST['isbn'];

    try {
        $sql = "INSERT INTO publishers (publisher_name, address, phone_number, book_name, ISBN) VALUES (:publisher_name, :address, :phone_number, :book_name, :isbn)";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':publisher_name', $publisher_name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':book_name', $book_name);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->execute();
        header('Location: publishers.php');
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
    <title>Add Publisher</title>
</head>
<body>
    <?php include('includes/loginheader.php'); ?>
    <div class="container">
        <h1>Add Publisher</h1>
        <form method="post" action="">
            <div class="mb-3">
                <label for="publisher_name" class="form-label">Publisher Name</label>
                <input type="text" class="form-control" id="publisher_name" name="publisher_name" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <div class="mb-3">
                <label for="book_name" class="form-label">Book Name</label>
                <input type="text" class="form-control" id="book_name" name="book_name" required>
            </div>
            <div class="mb-3">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" class="form-control" id="isbn" name="isbn" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Publisher</button>
        </form>
    </div>
    <?php include('includes/footer.php'); ?>
</body>
</html>
