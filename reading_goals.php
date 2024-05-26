<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$goal_id = isset($_GET['goal_id']) ? intval($_GET['goal_id']) : 0;

// Fetch user goals
$sql = "SELECT * FROM reading_goals WHERE user_id = :user_id";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$goals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch progress for the selected goal
if ($goal_id > 0) {
    $sql = "SELECT * FROM reading_goals WHERE goal_id = :goal_id AND user_id = :user_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':goal_id', $goal_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $goal = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $goal = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading Goals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            display: flex;
            flex-direction: column;
            align-items: flex-start;
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
            width: 120px;
            height: 35px;
            margin-left: 10px;
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

        .goal-details input[type="number"] {
            width: 80px;
            height: 35px;
            margin-left: 10px;
        }

        .goal-details label {
            width: 80px;
        }

        .goal-details p {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .chart-container {
            width: 300px;
            height: 300px;
        }

        .delete-button-container {
            position: absolute;
            bottom: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <?php include('includes/loginheader.php'); ?>

    <div class="container">
        <div class="sidebar">
            <h3>My Goals</h3>
            <?php if (empty($goals)): ?>
                <p>No goals yet. Create a goal to get started.</p>
            <?php else: ?>
                <?php foreach ($goals as $goal): ?>
                    <?php if (isset($goal['goal_id'])): ?>
                        <div class="list-item">
                            <span><?php echo htmlspecialchars($goal['goal_name']); ?> (<?php echo $goal['completed_books_count'] . '/' . $goal['target_books_count']; ?>)</span>
                            <a href="reading_goals.php?goal_id=<?php echo urlencode($goal['goal_id']); ?>">View</a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <button class="btn-primary" id="createGoalBtn">Create a Goal</button>
        </div>
        <div class="content">
            <?php if ($goal_id > 0 && $goal): ?>
                <h3><?php echo htmlspecialchars($goal['goal_name']); ?></h3>
                <div class="goal-details">
                    <form id="updateForm">
                        <p>
                            <label for="target_books">Target:</label> 
                            <input type="number" name="target_books" id="target_books" value="<?php echo $goal['target_books_count']; ?>" min="1">
                            <button type="button" id="updateTargetBtn" class="btn-primary">Update</button>
                        </p>
                        <p>
                            <label for="completed_books">Completed:</label> 
                            <input type="number" name="completed_books" id="completed_books" min="1">
                            <button type="button" id="addCompletedBtn" class="btn-primary">Add</button>
                        </p>
                    </form>
                    <p id="progressText"><?php echo $goal['completed_books_count'] . '/' . $goal['target_books_count']; ?> completed (<span id="percentage"><?php echo round(($goal['completed_books_count'] / $goal['target_books_count']) * 100); ?></span>%)</p>
                    <div class="chart-container">
                        <canvas id="progressChart"></canvas>
                    </div>
                    <div class="delete-button-container">
                        <button type="button" id="deleteGoalBtn" class="btn-delete">Delete Goal</button>
                    </div>
                </div>
            <?php else: ?>
                <p>Select a goal to view its details.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="popup" id="createGoalPopup">
        <div class="popup-content">
            <form method="POST" action="create_goal.php">
                <h4>Create a New Goal</h4>
                <div class="form-group">
                    <label for="goal_name">Goal Name</label>
                    <input type="text" name="goal_name" id="goal_name" required>
                </div>
                <div class="form-group">
                    <label for="target_books">Target Books Count</label>
                    <input type="number" name="target_books" id="target_books" min="1" required>
                </div>
                <button type="submit" name="create_goal" class="btn-primary">Create</button>
                <button type="button" class="btn-close" id="closePopupBtn"><i class="fas fa-times"></i></button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createGoalBtn').addEventListener('click', function() {
            document.getElementById('createGoalPopup').style.display = 'flex';
        });

        document.getElementById('closePopupBtn').addEventListener('click', function() {
            document.getElementById('createGoalPopup').style.display = 'none';
        });

        let progressChart;

        function updateChart(completed, target) {
            const ctx = document.getElementById('progressChart').getContext('2d');
            if (progressChart) {
                progressChart.destroy();
            }
            progressChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Completed', 'Remaining'],
                    datasets: [{
                        data: [completed, target - completed],
                        backgroundColor: ['#4caf50', '#f44336']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        label += context.parsed + ' books';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        function updateProgressText(completed, target) {
            const percentage = Math.round((completed / target) * 100);
            document.getElementById('progressText').innerHTML = `${completed}/${target} completed (<span id="percentage">${percentage}</span>%)`;
        }

        document.getElementById('updateTargetBtn').addEventListener('click', function() {
            const targetBooks = document.getElementById('target_books').value;
            const goalId = <?php echo $goal_id; ?>;
            fetch('update_goal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ goal_id: goalId, target_books: targetBooks })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateProgressText(data.completed_books, data.target_books);
                    updateChart(data.completed_books, data.target_books);
                }
            });
        });

        document.getElementById('addCompletedBtn').addEventListener('click', function() {
            const completedBooks = document.getElementById('completed_books').value;
            const goalId = <?php echo $goal_id; ?>;
            fetch('update_goal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ goal_id: goalId, completed_books: completedBooks })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateProgressText(data.completed_books, data.target_books);
                    updateChart(data.completed_books, data.target_books);
                }
            });
        });

        document.getElementById('deleteGoalBtn').addEventListener('click', function() {
            const goalId = <?php echo $goal_id; ?>;
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete this goal?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('delete_goal.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ goal_id: goalId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Deleted!',
                                'The goal has been deleted.',
                                'success'
                            ).then(() => {
                                window.location.href = 'reading_goals.php';
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'There was a problem deleting the goal.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        updateChart(<?php echo $goal['completed_books_count'] ?? 0; ?>, <?php echo $goal['target_books_count'] ?? 1; ?>);
    </script>
    <?php include('includes/footer.php'); ?>
</body>
</html>
