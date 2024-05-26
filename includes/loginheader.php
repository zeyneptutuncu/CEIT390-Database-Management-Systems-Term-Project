<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Library Management System</title>
    <style>
        .navbar {
            margin-bottom: 20px;
            width: 100%;
        }
        .navbar-nav {
            margin-left: auto;
        }
        .navbar-brand {
            font-size: 1.25rem;
        }
        .nav-link {
            margin-right: 10px; /* Menü öğeleri arasına biraz boşluk ekle */
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Library Management System</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Home</a>
                    <li class="nav-item">
                    <a class="nav-link" href="digital_archive.php">Digital Archieve</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="authors.php">Authors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="publishers.php">Publishers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="translators.php">Translators</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reading_goals.php">Reading Goals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add_collection.php">My List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">My Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
