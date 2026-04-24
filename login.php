<?php

include 'db.php';

session_start();

$login_error = "";
$signup_error = "";
$signup_success = "";
if (isset($_POST["login"])) {

    $uname = $_POST['txtusername'] ?? '';
    $pword = $_POST['txtpassword'] ?? '';

    // password123
    if (empty($uname)) {
        $login_error = "Email is required.";
    } else if (empty($pword)) {
        $login_error = "Password is required.";
    } else {

        $sql = "SELECT * FROM users WHERE email='$uname'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if (password_verify($pword, $row['password'])) {



                $id = $row["ID"];
                $_SESSION["user_id"] = $id;


                // Save new session
                $stmt = $conn->prepare("
    INSERT INTO user_portal_sessions
    (user_id, login_time, last_activity)
    VALUES (?, NOW(), NOW())
    ");

                $stmt->bind_param("i", $id);
                $stmt->execute();

                $_SESSION['portal_session_id'] = $conn->insert_id;

                header("Location: attendance.php");
                exit();
            } else {
                $login_error = "Incorrect Email or Password";
            }
        } else {
            $login_error = "Incorrect Email or Password";
        }

    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>CHI</title>



    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-image: url("image.png");
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 870px;
            height: 580px;
            max-width: 100%;
            min-height: 480px;
        }

        .container p {
            font-size: 15px;
            line-height: 20px;
            letter-spacing: 0.3px;
            margin: 20px 0;
            margin-top: 5px;
        }

        .container span {
            font-size: 12px;
        }

        .container a {
            color: #333;
            font-size: 14px;
            text-decoration: none;
            margin: 15px 0 10px;
            margin-top: -5px;
            margin-left: 294px;
        }

        .container button {
            background-color: #035c04;
            color: #fff;
            font-size: 15px;
            padding: 10px 45px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 10px;
            cursor: pointer;
            margin-bottom: 40px;
        }

        .container button.hidden {
            background-color: transparent;
            border-color: #fff;
        }

        .container form {
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
        }

        .container input {
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 10px 15px;
            font-size: 19px;
            border-radius: 8px;
            width: 100%;
            outline: none;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.active .sign-in {
            transform: translateX(100%);
        }

        .sign-up {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: move 0.6s;
        }

        @keyframes move {

            0%,
            49.99% {
                opacity: 0;
                z-index: 1;
            }

            50%,
            100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .social-icons {
            margin: 20px 0;
        }

        .social-icons a {
            border: 1px solid #ccc;
            border-radius: 20%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 3px;
            width: 40px;
            height: 40px;
        }

        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 150px 0 0 100px;
            z-index: 1000;
        }

        .container.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 0 150px 100px 0;
        }

        .toggle {

            height: 100%;
            background: radial-gradient(100% 100% at 100% 0%, #189b1a 0%, #035c04 100%);
            color: #fff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .container.active .toggle {
            transform: translateX(50%);
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 30px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .toggle-panel h1 {
            font-size: 29px;
            font-weight: bold;
        }

        .toggle-panel p {
            font-size: 17px;

        }

        .toggle-left {
            transform: translateX(-200%);
        }

        .container.active .toggle-left {
            transform: translateX(0);
        }

        .toggle-right {
            right: 0;
            transform: translateX(0);
        }

        .container.active .toggle-right {
            transform: translateX(200%);
        }

        form h1 {
            font-size: 35px;
        }

        .error {
            background-color: #F2DEDE;
            color: #8f1917;
            font-weight: 500;
            padding: 14px;
            width: 100%;
            text-align: center;
        }

        .aycon {

            z-index: 100;
            margin-top: -120px;
            margin-bottom: 20px;
        }


        .password-box {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
        }


        .password-box input {
            width: 100%;
            padding-right: 45px;
            /* space for eye icon */
        }

        .password-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #555;
        }



        /* ========================= */
        /* RESPONSIVE SWITCH SYSTEM  */
        /* ========================= */

        /* Default behavior */

        #signupBtn {
            position: relative;
            transition: 0.3s ease;
        }

        #signupBtn:disabled {
            transform: scale(0.98);
        }

        .mobile-view {
            display: none;
        }


        .container {
            display: block;
        }

        /* 📱 When screen is mobile */
        @media (max-width: 768px) {

            body {
                background-image: url("image.png");
                background-size: cover;
                padding: 20px;
            }

            .container {
                display: none;
                /* Hide your animated design */
            }

            .mobile-view {
                display: flex;
                /* Show mobile version */
                justify-content: center;
                align-items: center;
                width: 100%;
            }

            .mobile-card {
                background: #fff;
                width: 100%;
                max-width: 380px;
                padding: 30px 20px;
                border-radius: 20px;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
                text-align: center;
            }

            .mobile-logo {
                width: 140px;
                margin-bottom: 20px;
            }

            .mobile-card h2 {
                font-size: 20px;
                margin-bottom: 20px;
            }

            .mobile-card input {
                width: 100%;
                padding: 12px;
                margin-bottom: 12px;
                border-radius: 8px;
                border: none;
                background: #f1f1f1;
                font-size: 14px;
            }

            .mobile-card button {
                width: 100%;
                padding: 12px;
                border-radius: 8px;
                border: none;
                background: radial-gradient(100% 100% at 100% 0%, #189b1a 0%, #035c04 100%);
                color: #fff;
                font-weight: 600;
                margin-top: 10px;
            }

            .mobile-toggle {
                display: flex;
                margin-bottom: 20px;
            }

            .mobile-toggle button {
                flex: 1;
                background: #eee;
                color: #333;
                margin: 0;
                border-radius: 8px;
            }

            .mobile-toggle button.active {
                background: #3960a2;
                color: #fff;
            }
        }
    </style>
</head>

<body>


    <div class="container <?php
    if (isset($_POST['create']) && $signup_error != '')
        echo 'active';
    ?>" id="container">

        <div class="form-container sign-up">


        </div>

        <div class="form-container sign-in">
            <form action="login.php" method="POST">
                <h1>Log In</h1>

                <input type="email" name="txtusername" placeholder="Email">
                <input type="password" name="txtpassword" placeholder="Password">
                <button type="submit" name="login">Log In</button>

                <?php if ($login_error != "") { ?>
                    <p class="error"><?php echo $login_error; ?></p>
                <?php } ?>

            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">


                <div class="toggle-panel toggle-right">
                    <img class="aycon" src="CHILogo.png" alt="" width="240px">
                    <h1>Hello, Welcome back!</h1>
                    <p>Ready to make progress? Have a great day at work.</p>

                </div>
            </div>
        </div>
    </div>




    <!-- ====================== -->
    <!-- 📱 MOBILE VIEW DESIGN -->
    <!-- ====================== -->

    <div class="mobile-view">

        <div class="mobile-card">

            <img src="CHILogo.png" class="mobile-logo">

            <h2>Employee Login</h2>

            <!-- LOGIN FORM -->
            <form method="POST" id="mobileLoginForm">
                <input type="email" name="txtusername" placeholder="Email">
                <input type="password" name="txtpassword" placeholder="Password">
                <button type="submit" name="login">Login</button>

                <?php if ($login_error != "") { ?>
                    <p class="error" style="margin-top:10px; font-size: 13px;"><?php echo $login_error; ?></p>
                <?php } ?>
            </form>

        </div>

    </div>


</body>

</html>