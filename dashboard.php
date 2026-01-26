<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['google_id'])) {
    header('Location: google-login.php');
    exit();
}

$name = $_SESSION['name'];
$email = $_SESSION['email'];
$google_id = $_SESSION['google_id'];
$picture = $_SESSION['picture'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Google Auth Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #74ebd5, #9face6);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .dashboard {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 25px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .dashboard img {
            width: 100px;
            border-radius: 50%;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .dashboard h2 {
            margin: 10px 0;
            font-size: 24px;
            color: #333;
        }

        .dashboard p {
            margin: 8px 0;
            color: #555;
        }

        .logout-btn {
            margin-top: 25px;
            padding: 10px 25px;
            font-size: 16px;
            background-color: #e53935;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #c62828;
        }
    </style>
</head>
<body>

    <div class="dashboard">
        <img src="<?php echo htmlspecialchars($picture); ?>" alt="Profile Picture">
        <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Google ID:</strong> <?php echo htmlspecialchars($google_id); ?></p>
        <p>You have successfully logged in via <strong>Google Authentication</strong>.</p>
        <a class="logout-btn mt-3" href="logout.php">Logout</a>
    </div>

</body>
</html>
