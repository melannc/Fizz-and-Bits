<?php
ini_set('session.cookie_httponly', 1);
session_name("admin_session");
session_start();
require 'config.php';

$error_msg = '';

if (isset($_POST['btnLogin'])) {
    $username = $_POST["txtUname"];
    $password = $_POST["txtPass"];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND role = 'administrator'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row && password_verify($password, $row['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['fname'] = $row['first_name'];
            $_SESSION['lname'] = $row['last_name'];
            $_SESSION['logged_in'] = true;

            header('Location: dashboard.php');
            exit;
        } else {
            $error_msg = "Invalid username/password";
        }
    } else {
        $error_msg = "Invalid username/password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrator Control</title>
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
   <form action="" method="post">
        <!-- <h1 class="logo">FIZZ & BITS</h1> -->
        <img src="/Fizz_and_Bits_Website/images/imagotype_logo.png" alt="Fizz & Bits Logo" class="logo">
        <p class="font-surimi-admin">Login as Admin User</p>
        <label class="font-poppins">Username</label>
        <input type="text" name="txtUname" required class="font-poppins">
        <label class="font-poppins">Password</label>
        <input type="password" name="txtPass" required class="font-poppins">
        <!-- <input type="submit" value="login" name="btnLogin"> -->
        <button type="submit" value="login" name="btnLogin" class="font-poppins">LOG IN</button>
        <?php if ($error_msg): ?>
        <div class="error-message"><?= htmlentities($error_msg) ?></div>
        <?php endif; ?>
    </form>
</body>
</html>
