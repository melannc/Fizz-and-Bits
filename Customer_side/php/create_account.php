<?php
    session_name("customer_session");
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
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

        $fname = validate($_POST['first_name']);
        $lname = validate($_POST['last_name']);
        $username = validate($_POST['username']);
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'customer';

        $checkUser = $db->query("SELECT * FROM users WHERE username='$username'");
        
        if ($checkUser->num_rows > 0) {
            echo "<script>alert('Username already taken. Please choose another.');</script>";
        } else {
            $sql = "INSERT INTO users (username, password, first_name, last_name, role)
                    VALUES ('$username', '$pass', '$fname', '$lname', '$role')";

            if ($db->query($sql) === TRUE) {
                echo "<script>alert('Account created successfully! Please login.'); window.location='index.php';</script>";
            } else {
                echo "Error: " . $sql . "<br>" . $db->error;
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="../css/login_style.css">
    <link rel="stylesheet" href="../css/fonts.css">
</head>
<body>
<div class="left">
    <img src="/Fizz_and_Bits_Website/images/imagotype_logo.png" alt="Company Logo" class="login-logo">
    <p class="font-poppins">Join Fizz & Bits today!</p>
</div>
<div class="right">
    <div class="login-box">
        <h2 class="font-surimi">Create Account</h2>

        <form method="POST">
            <div class="input-group">
                <input type="text" name="first_name" class="font-poppins" placeholder="First Name" required>
            </div>
            <div class="input-group">
                <input type="text" name="last_name" class="font-poppins" placeholder="Last Name" required>
            </div>
            <div class="input-group">
                <input type="text" name="username" class="font-poppins" placeholder="Username" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" class="font-poppins" placeholder="Password" required>
            </div>

            <button type="submit" class="font-surimi">Sign Up</button>
        </form>

        <div class="divider font-poppins">or</div>

        <div class="signup font-poppins">
            Already have an account? <a href="index.php">Login here</a>
        </div>
    </div>
</div>
</body>
</html>