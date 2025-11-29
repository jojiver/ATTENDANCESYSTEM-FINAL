<?php
session_start();
include("../config.php");

if (isset($_SESSION['admin_username'])) {
    header("Location: ../pages/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['admin_username'] = $row['username'];
        header("Location: ../pages/dashboard.php");
        exit;
    } else {
        echo "<script>alert('Invalid username or password'); window.location='admin-login.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login</title>
  <link rel="stylesheet" href="../assets/styles/style.css" />
</head>
<body class="centered">
  <div class="container">
  <h1 class="nav"><a href="../index.php" style="text-decoration:none; color:white;">AMS</a></h1>

    <div class="login-box">
      <div class="login-header">
        <img src="../assets/images/efbipfp.png" alt="Admin" class="admin-img" />
        <h2>Admin Login</h2>
      </div>

      <form action="admin-login.php" method="post">
        <label for="username" class="label">Username</label>
        <div class="input-group">
          <input type="text" id="username" name="username" placeholder="Enter username" required />
        </div>

        <label for="password" class="label">Password</label>
        <div class="input-group">
          <input type="password" id="password" name="password" placeholder="Enter password" required />
        </div>

        <button type="submit" class="btn login-btn">LOGIN</button>
      </form>
    </div>
  </div>
</body>
</html>
