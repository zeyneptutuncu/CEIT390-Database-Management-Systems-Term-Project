<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include('includes/config.php');

$publisher_name = urldecode($_GET['publisher']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $new_address = $_POST['address'];
        $new_phone_number = $_POST['phone_number'];

        try {
            $sql = "UPDATE publishers SET address = :new_address, phone_number = :new_phone_number WHERE publisher_name = :publisher_name";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':new_address', $new_address);
            $stmt->bindParam(':new_phone_number', $new_phone_number);
            $stmt->bindParam(':publisher_name', $publisher_name);
            $stmt->execute();

            header('Location: publishers.php');
            exit();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    } elseif (isset($_POST['delete'])) {
        try {
            // Books tablosundaki publisher_name'i NULL olarak güncelle
            $sql = "UPDATE books SET publisher_name = NULL WHERE publisher_name = :publisher_name";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':publisher_name', $publisher_name);
            $stmt->execute();

            // Yayınevini sil
            $sql = "DELETE FROM publishers WHERE publisher_name = :publisher_name";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':publisher_name', $publisher_name);
            $stmt->execute();

            header('Location: publishers.php');
            exit();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}

try {
    $sql = "SELECT * FROM publishers WHERE publisher_name = :publisher_name";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':publisher_name', $publisher_name);
    $stmt->execute();
    $publisher = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Publisher</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style>
        .container {
            margin-top: 40px;
            max-width: 600px;
        }
        .form-control {
            width: 100%;
        }
        .text-end {
            text-align: end;
        }
        .form-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .form-group label {
            width: 30%;
        }
        .form-group .form-control {
            width: 65%;
        }
        .button-group {
            text-align: right;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>
</head>
<body>
    <?php include('includes/loginheader.php'); ?>

    <div class="container">
        <h1>Edit Publisher</h1>
        <form method="post" action="">
            <div class="form-group mb-3">
                <label for="publisher_name" class="form-label">Publisher Name</label>
                <input type="text" class="form-control" id="publisher_name" name="publisher_name" value="<?php echo htmlspecialchars($publisher['publisher_name'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            <div class="form-group mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($publisher['address'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($publisher['phone_number'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="button-group mb-3">
                <button type="submit" name="delete" class="btn btn-danger">Delete Publisher</button>
                <button type="submit" name="update" class="btn" style="background-color: #fd6f82; border-color: #fd6f82; color: white; padding: 6px 12px; border-radius: 4px;">Update Publisher</button>
            </div>
        </form>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>
