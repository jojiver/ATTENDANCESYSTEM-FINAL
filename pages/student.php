<?php
// pages/student.php
session_start();
include("../config.php"); // make sure this path is correct

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = trim($_POST['student_id']);
    $today = date("Y-m-d");

    if ($student_id === "") {
        $message = "❌ Please enter a Student ID.";
        $messageClass = "error";
    } else {
        // Check if student exists in new table (fullname + section)
        $stmt = $conn->prepare("SELECT fullname, section FROM students WHERE student_id = ? LIMIT 1");
        if ($stmt === false) {
            die("Prepare failed (students select): " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($fullname, $section);
            $stmt->fetch();
            $stmt->close();

            // Check if already marked today
            $chk = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
            if ($chk === false) {
                die("Prepare failed (attendance check): " . htmlspecialchars($conn->error));
            }

            $chk->bind_param("ss", $student_id, $today);
            $chk->execute();
            $chk->store_result();

            if ($chk->num_rows > 0) {
                $message = "⚠️ You already marked your attendance today.";
                $messageClass = "warning";
                $chk->close();
            } else {
                $chk->close();

                // Insert record in attendance table
                $ins = $conn->prepare("
                    INSERT INTO attendance (student_id, fullname, section, status, date, time_in)
                    VALUES (?, ?, ?, 'Present', ?, NOW())
                ");
                if ($ins === false) {
                    die("Prepare failed (attendance insert): " . htmlspecialchars($conn->error));
                }

                $ins->bind_param("ssss", $student_id, $fullname, $section, $today);
                if (!$ins->execute()) {
                    die("Execute failed (attendance insert): " . htmlspecialchars($ins->error));
                }
                $ins->close();

                // Save to session for success page
                $_SESSION['student_id'] = $student_id;
                $_SESSION['fullname'] = $fullname;
                $_SESSION['section'] = $section;

                header("Location: attendance-success.php");
                exit();
            }
        } else {
            // Student not found
            $message = "❌ Invalid Student ID. Please try again.";
            $messageClass = "error";
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Student Attendance</title>
  <link rel="stylesheet" href="../assets/styles/style.css" />
  <style>
    .message { margin-top: 15px; padding: 10px; border-radius: 8px; font-weight:500; text-align:center; }
    .success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    .error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
    .warning { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
  </style>
</head>
<body class="centered">
  <div class="container">
    <a href="admin-login.php" class="admin-login">Login as Admin</a>

    <div class="attendance-box">
      <h2>Student Attendance</h2>
      <p>Enter your student ID to mark your attendance</p>

      <form method="POST" action="student.php">
        <label for="student_id" class="label">Student ID</label>
        <div class="input-group">
          <input type="text" id="student_id" name="student_id" placeholder="e.g. A001" required 
            value="<?= isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : '' ?>" />
        </div>

        <button type="submit" class="btn present">Mark as Present</button>
        <a href="student.php" class="btn reset">Reset</a>
      </form>

      <?php if (!empty($message)) : ?>
        <div class="message <?= htmlspecialchars($messageClass) ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
