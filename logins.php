<?php

include 'db.php';
session_start();
if (isset($_POST["login"])) {

    $uname = $_POST['username'] ?? '';
    $pword = $_POST['password'] ?? '';

    // AFTER successful login

    $user_id = 1;
    $_SESSION['user_id'] = $user_id;

    // Save new session
    $stmt = $conn->prepare("
    INSERT INTO user_portal_sessions
    (user_id, login_time, last_activity)
    VALUES (?, NOW(), NOW())
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $_SESSION['portal_session_id'] = $conn->insert_id;

    header("Location: /colorsteel/attendance.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #009420, #05ae2a, #08ca32);
        }

        /* LOGIN CARD */

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 14px;
            width: 350px;
            max-width: 90%;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
            text-align: center;
            animation: fadeIn .6s ease;
        }

        .login-container h2 {
            margin-bottom: 25px;
            color: #333;
        }

        /* INPUT */

        .input-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .input-group input:focus {
            border-color: #2c5364;
            box-shadow: 0 0 0 2px rgba(44, 83, 100, 0.2);
        }

        /* BUTTON */

        .login-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #009420;
            color: white;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        .login-btn:hover {
            background: #1e3c4a;
            transform: translateY(-1px);
        }

        /* MOBILE */

        @media (max-width:480px) {

            .login-container {
                padding: 30px 25px;
            }

        }

        /* ANIMATION */

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="login-container">

        <h2>Portal Login</h2>

        <form action="login.php" method="POST">

            <div class="input-group">
                <input type="text" name="username" placeholder="Username">
            </div>

            <div class="input-group">
                <input type="password" name="password" placeholder="Password">
            </div>

            <button class="login-btn" type="submit" name="login">Login</button>

        </form>

    </div>

</body>

</html>