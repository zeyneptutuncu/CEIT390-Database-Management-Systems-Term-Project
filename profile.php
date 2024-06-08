<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('includes/config.php');

$user_id = $_SESSION['user_id'];

try {
    // Kullanıcı bilgilerini çekmek için
    $sql = "SELECT user_name, mail_address, phone_number FROM users WHERE user_id = :user_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    // Formdan gelen verileri al
    $new_username = $_POST['user_name'];
    $new_mail_address = $_POST['mail_address'];
    $new_phone_number = $_POST['phone_number'] ?? '';

    // Kullanıcı bilgilerini güncelle
    try {
        // Var olan kullanıcıyı kontrol et
        $check_sql = "SELECT COUNT(*) FROM users WHERE (user_name = :user_name OR mail_address = :mail_address OR phone_number = :phone_number) AND user_id != :user_id";
        $check_stmt = $dbh->prepare($check_sql);
        $check_stmt->bindParam(':user_name', $new_username);
        $check_stmt->bindParam(':mail_address', $new_mail_address);
        $check_stmt->bindParam(':phone_number', $new_phone_number);
        $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $check_stmt->execute();
        $count = $check_stmt->fetchColumn();

        if ($count > 0) {
            $error_message = "User with the same username, email, or phone number already exists.";
        } else {
            // Güncelleme işlemi
            $update_sql = "UPDATE users SET user_name = :user_name, mail_address = :mail_address, phone_number = :phone_number WHERE user_id = :user_id";
            $update_stmt = $dbh->prepare($update_sql);
            $update_stmt->bindParam(':user_name', $new_username);
            $update_stmt->bindParam(':mail_address', $new_mail_address);
            $update_stmt->bindParam(':phone_number', $new_phone_number);
            $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $update_stmt->execute();

            // Kullanıcı bilgilerini tekrar çek
            $sql = "SELECT user_name, mail_address, phone_number FROM users WHERE user_id = :user_id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $success_message = "Profile updated successfully!";
        }
    } catch (Exception $e) {
        $error_message = 'Error: ' . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        if (strlen($new_password) > 10) {
            $error_message = 'Password must be 10 characters or less.';
        } else {
            try {
                $password_sql = "UPDATE users SET password = :password WHERE user_id = :user_id";
                $password_stmt = $dbh->prepare($password_sql);
                $password_stmt->bindParam(':password', $new_password); // Şifreyi hashlemiyoruz
                $password_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $password_stmt->execute();
                $success_message = "Password updated successfully!";
            } catch (Exception $e) {
                $error_message = 'Error: ' . $e->getMessage();
            }
        }
    } else {
        $error_message = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 40px;
            max-width: 600px;
        }
        .form-control {
            width: 50%;
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
            width: 20%;
        }
        .form-group .form-control {
            width: 75%;
        }
        .button-group {
            text-align: right;
        }
        .btn-primary, .btn-update {
            background-color: #fd6f82;
            border-color: #fd6f82;
            color: white;
        }
        .btn-primary:hover, .btn-update:hover {
            background-color: #d85067;
            border-color: #d85067;
        }
        .btn-delete {
            background-color: #ff4d4d;
            border-color: #ff4d4d;
            color: white;
            width: 200px;
            height: 35px;
            margin-left: 10px;
        }
        .btn-delete:hover {
            background-color: #cc0000;
            border-color: #cc0000;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            flex: 1;
        }
        footer {
            text-align: center;
            margin-top: auto;
            padding: 10px 0;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include('includes/loginheader.php'); ?>

    <div class="container">
        <h1>My Profile</h1>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group mb-3">
                <label for="user_name" class="form-label">Username</label>
                <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="mail_address" class="form-label">Email</label>
                <input type="email" class="form-control" id="mail_address" name="mail_address" value="<?php echo htmlspecialchars($user['mail_address'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="button-group mb-3">
                <button type="submit" name="update_profile" class="btn-update">Update My Profile</button>
            </div>
        </form>
        <hr>
        <h2>Change Password</h2>
        <form method="post" action="">
            <div class="form-group mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="form-group mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="button-group mb-3">
                <button type="submit" name="update_password" class="btn-update">Update Password</button>
            </div>
        </form>
        <div class="button-group mb-3">
            <form method="post" action="delete_account.php">
                <button type="submit" name="delete_account" class="btn-delete">Delete My Account</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('.btn-delete').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure? Do you want to delete your account?')) {
                this.closest('form').submit();
            }
        });
    </script>

    <?php include('includes/footer.php'); ?>
</body>
</html>
