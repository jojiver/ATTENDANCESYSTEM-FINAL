<?php
include("../config.php"); // your DB connection file

$message = ""; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = trim($_POST['student-id']);

    $check_student = mysqli_query($conn, "SELECT * FROM students WHERE student_id = '$student_id'");

    if (mysqli_num_rows($check_student) == 0) {
        $message = "❌ Invalid or wrong Student ID.";
    } else {
        $student = mysqli_fetch_assoc($check_student);

        if ($student['status'] === 'Present') {
            $message = "⚠️ You already marked attendance today.";
        } else {
            $update = mysqli_query($conn, "UPDATE students SET status='Present' WHERE student_id='$student_id'");

            $date = date('Y-m-d');
            mysqli_query($conn, "INSERT INTO attendance_log (student_id, date, status)
                                VALUES ('$student_id', '$date', 'Present')");

            if ($update) {
                $message = "✅ Attendance marked successfully!";
            } else {
                $message = "⚠️ Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Attendance</title>
  <link rel="stylesheet" href="../assets/styles/style.css"/>
</head>
<body>
  <div class="container">
    <a href="../pages/admin-login.php" class="admin-login">View Attendance</a>

    <div class="attendance-box">
      <h2>Student Attendance</h2>
      <p>Enter your student ID to mark your attendance</p>

      <form action="student.php" method="post">
        <label for="student-id" class="label">Student ID</label>
        <div class="input-group">
          <input type="text" name="student-id" placeholder="Enter Your Student ID" required />
        </div>

        <button type="submit" class="btn present">Mark as Present</button>
        <a href="student.php" class="btn reset">Reset</a>
      </form>

 
      <?php if (!empty($message)): ?>
        <div class="message-box">
          <p><?= htmlspecialchars($message) ?></p>
        </div>
      <?php endif; ?>

    </div>
  </div>
</body>
</html>
