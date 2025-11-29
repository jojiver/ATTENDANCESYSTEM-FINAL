<?php
session_start();
if (!isset($_SESSION['student_id']) || empty($_SESSION['fullname'])) {
  header("Location: student.php");
  exit();
}

$fullname = $_SESSION['fullname'];
$section  = $_SESSION['section'] ?? '';
$date     = date("F d, Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance Success</title>
  <link rel="stylesheet" href="../assets/styles/style.css"/>
</head>
<body>
  <div class="container">
    <a href="dashboard.php" class="admin-login">Home</a>

    <div class="attendance-box">
      <h2>Attendance Marked Successfully</h2>
      <p>
        <strong><?= htmlspecialchars($fullname) ?></strong><br>
        Section: <strong><?= htmlspecialchars($section) ?></strong><br>
        Date: <strong><?= htmlspecialchars($date) ?></strong><br><br>
        Your attendance has been recorded successfully.
      </p>

      <a href="../index.php" class="btn">Back</a>
    </div>
  </div>
</body>
</html>