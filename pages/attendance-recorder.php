<?php
include("../config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);

    // Get student info
    $sql = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $fullname = $student['fullname'];
        $section = $student['section'];

        // Check if already marked today
        $check = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = CURDATE()");
        $check->bind_param("s", $student_id);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows == 0) {
            // Record attendance as Present
            $insert = $conn->prepare("INSERT INTO attendance (student_id, fullname, section, status, date, time_in) VALUES (?, ?, ?, 'Present', CURDATE(), NOW())");
            $insert->bind_param("sss", $student_id, $fullname, $section);
            $insert->execute();

            echo "<div style='color:green;'>Attendance marked for <b>$fullname</b> ($section)!</div>";
        } else {
            echo "<div style='color:orange;'>Already marked attendance today for <b>$fullname</b>.</div>";
        }
    } else {
        echo "<div style='color:red;'>Student ID not found!</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Recorder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container p-5">
    <div class="card shadow p-4">
        <h2 class="mb-4 text-center">Attendance Recorder</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Enter Student ID:</label>
                <input type="text" name="student_id" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Mark Attendance</button>
        </form>
    </div>

    <div class="mt-4">
        <h4>Today's Attendance</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th>Time In</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $today = date('Y-m-d');
                $records = $conn->prepare("SELECT * FROM attendance WHERE date = ?");
                $records->bind_param("s", $today);
                $records->execute();
                $rows = $records->get_result();
                if ($rows->num_rows > 0) {
                    while ($r = $rows->fetch_assoc()) {
                        echo "<tr>
                            <td>{$r['student_id']}</td>
                            <td>{$r['fullname']}</td>
                            <td>{$r['section']}</td>
                            <td>{$r['status']}</td>
                            <td>{$r['time_in']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No attendance yet today</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
