<?php
session_name("customer_session");
session_start();

    if (isset($_SESSION['user_id'])) {
        if ($row['role'] == "administrator") {
            header("Location: /Fizz_and_Bits_Website/Administrator_side/php/dashboard.php");
        } else {
            header("Location: /Fizz_and_Bits_Website/Customer_side/php/front.php");
        }
        exit();
        exit();
    }

    include "config.php";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        function validate($data){
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $username = validate($_POST['username']);
        $pass = trim($_POST['password']);

        $stmt = $db->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (password_verify($pass, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['fname'] = $row['first_name'];
                $_SESSION['lname'] = $row['last_name']; 

                if ($row['role'] == "administrator") {
                    header("Location: /Fizz_and_Bits_Website/Administrator_side/php/dashboard.php");
                } else {
                    header("Location: front.php");
                }
                exit();
            } else {
                echo "<script>alert('Incorrect Username or Password');</script>";
            }
        } else {
            echo "<script>alert('Incorrect Username or Password');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login_style.css">
    <link rel="stylesheet" href="../css/fonts.css">
</head>
<body>
<div class="left">
    <img src="/Fizz_and_Bits_Website/images/imagotype_logo.png" alt="Company Logo" class="login-logo">
</div>
<div class="right">
    <div class="login-box">
        <h2 class="font-surimi-login">Login as a Customer</h2>

        <form method="POST">
            <div class="input-group">
                <input type="text" name="username" class="font-poppins" placeholder="Username" required>
            </div>

            <div class="input-group">
                <input type="password" name="password" class="font-poppins" placeholder="Password" required>
            </div>
            
            <button type="submit" class="font-poppins-btn login-btn">LOG IN</button>
        </form>
        <div class="signup font-poppins">
            Don’t have an account? <a href="create_account.php" style="color: #017B39;">Create here</a>
        </div>
    </div>
</div>

</body>
</html>