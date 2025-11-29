<?php
session_start();
include("../config.php");

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin-login.php");
    exit;
}
if (isset($_POST['add_student'])) {
    $student_id = $_POST['student_id'];
    $fullname = $_POST['fullname'];
    $section = $_POST['section'];

    $stmt = $conn->prepare("INSERT INTO students (student_id, fullname, section) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $student_id, $fullname, $section);
    $stmt->execute();

    echo "<script>alert('Student Added Successfully!'); window.location='dashboard.php';</script>";
    exit;
}


if (isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];

    // Check if student exists
    $check = $conn->prepare("SELECT * FROM students WHERE student_id=?");
    $check->bind_param("s", $student_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Student does not exist.'); window.location='dashboard.php';</script>";
        exit;
    }

    // DELETE attendance first (prevents foreign key error 1451)
    $del_attendance = $conn->prepare("DELETE FROM attendance WHERE student_id=?");
    $del_attendance->bind_param("s", $student_id);
    $del_attendance->execute();

    // DELETE student
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id=?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();

    echo "<script>alert('Student Deleted Successfully!'); window.location='dashboard.php';</script>";
    exit;
}


/* EDIT STUDENT */
if (isset($_POST['edit_student'])) {
    $student_id = $_POST['student_id'];
    $fullname = $_POST['fullname'];
    $section = $_POST['section'];

    // Check if student exists
    $check = $conn->prepare("SELECT * FROM students WHERE student_id=?");
    $check->bind_param("s", $student_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Student does not exist.'); window.location='dashboard.php';</script>";
        exit;
    }

    // Update student
    $stmt = $conn->prepare("UPDATE students SET fullname=?, section=? WHERE student_id=?");
    $stmt->bind_param("sss", $fullname, $section, $student_id);
    $stmt->execute();

    echo "<script>alert('Student Updated Successfully!'); window.location='dashboard.php';</script>";
    exit;
}



$selected_section = isset($_GET['section']) ? $_GET['section'] : '';
$selected_date = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');



if (!empty($selected_section)) {
    $stmt = $conn->prepare("
        SELECT s.student_id, s.fullname, s.section,
               COALESCE(a.status, 'Absent') AS status
        FROM students s
        LEFT JOIN attendance a ON s.student_id = a.student_id AND a.date = ?
        WHERE s.section = ?
        ORDER BY s.fullname ASC
    ");
    $stmt->bind_param("ss", $selected_date, $selected_section);

} else {
    $stmt = $conn->prepare("
        SELECT s.student_id, s.fullname, s.section,
               COALESCE(a.status, 'Absent') AS status
        FROM students s
        LEFT JOIN attendance a ON s.student_id = a.student_id AND a.date = ?
        ORDER BY s.section, s.fullname ASC
    ");
    $stmt->bind_param("s", $selected_date);
}

$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AMS - Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/styles/dashboard.css" />
</head>

<body>


<nav class="navbar navbar-expand-lg dashboard-nav">
  <div class="container-fluid d-flex justify-content-between align-items-center px-4">
    <a class="navbar-brand fw-bold text-white fs-3">AMS</a>

    <div class="d-flex gap-2">
      <a href="summary.php" class="btn btn-summary" style="background-color: #8d86d6; color: white; font-weight:bold;">Daily Summary</a>
     <button onclick="window.location.href='logout.php'" class="btn btn-logout">Logout</button>
    </div>
  </div>
</nav>


<main class="dashboard-container">
  <div class="container">

    <h2 class="text-center text-white fw-bold mb-4 text-uppercase">Attendance Record</h2>

    <div class="attendance-card mx-auto p-4">

      
      <div class="row mb-4">
        <div class="col-md-6 mb-2">
          <button class="btn w-100 btn-student" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            ADD STUDENT
          </button>
        </div>

        <div class="col-md-6 mb-2">
          <button class="btn w-100 btn-student" data-bs-toggle="modal" data-bs-target="#editStudentModal">
            EDIT STUDENT
          </button>
        </div>

        <div class="col-md-6 mb-2">
          <button class="btn w-100 btn-student" data-bs-toggle="modal" data-bs-target="#deleteStudentModal">
            DELETE STUDENT
          </button>
        </div>
      </div>

   
      <form method="GET" action="" class="filters mb-3">
        <div class="mb-2">
          <label class="form-label fw-semibold text-white">Select Date</label>
          <input type="date" name="date" class="form-control" value="<?= $selected_date ?>" onchange="this.form.submit()">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-white">Select Section</label>
          <select name="section" class="form-select" onchange="this.form.submit()">
            <option value="">All Sections</option>
            <option value="A" <?= $selected_section=='A'?'selected':'' ?>>A</option>
            <option value="B" <?= $selected_section=='B'?'selected':'' ?>>B</option>
            <option value="C" <?= $selected_section=='C'?'selected':'' ?>>C</option>
             <option value="D" <?= $selected_section=='D'?'selected':'' ?>>D</option>
          </select>
        </div>

        <button type="submit" class="btn btn-refresh w-100">Refresh</button>
      </form>

      <!-- TABLE -->
      <div class="table-responsive">
        <table class="table table-borderless text-center align-middle">
          <thead>
            <tr>
              <th>Student ID</th>
              <th>Full Name</th>
              <th>Section</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $students->fetch_assoc()): ?>
              <tr>
                <td><?= $row['student_id'] ?></td>
                <td><?= $row['fullname'] ?></td>
                <td><?= $row['section'] ?></td>
                <td>
                  <span class="<?= $row['status']=='Present'?'text-success':'text-danger' ?>">
                    <?= $row['status'] ?>
                  </span>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</main>


<div class="modal fade" id="addStudentModal">
  <div class="modal-dialog">
    <form method="POST" class="modal-content p-4">
      <h5 class="fw-bold mb-3">Add New Student</h5>

      <input type="hidden" name="add_student" value="1">

      <label class="form-label">Student ID</label>
      <input type="text" name="student_id" class="form-control mb-2" required>

      <label class="form-label">Full Name</label>
      <input type="text" name="fullname" class="form-control mb-2" required>

      <label class="form-label">Section</label>
      <select name="section" class="form-select mb-3" required>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
      </select>

      <button class="btn btn-student w-100">Add Student</button>
    </form>
  </div>
</div>


<div class="modal fade" id="editStudentModal">
  <div class="modal-dialog">
    <form method="POST" class="modal-content p-4">

      <h5 class="fw-bold mb-3">Edit Student</h5>

      <input type="hidden" name="edit_student" value="1">

      <label class="form-label">Enter Student ID</label>
      <input type="text" name="student_id" class="form-control mb-2" required>

      <label class="form-label">New Full Name</label>
      <input type="text" name="fullname" class="form-control mb-2" required>

      <label class="form-label">New Section</label>
      <select name="section" class="form-select mb-3" required>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
         <option value="D">C</option>
      </select>

      <button class="btn btn-student w-100">Update</button>
    </form>
  </div>
</div>


<div class="modal fade" id="deleteStudentModal">
  <div class="modal-dialog">
    <form method="POST" class="modal-content p-4">

      <h5 class="fw-bold mb-3">Delete Student</h5>

      <input type="hidden" name="delete_student" value="1">

      <label class="form-label">Enter Student ID</label>
      <input type="text" name="student_id" class="form-control mb-3" required>

      <button class="btn btn-danger w-100">Delete</button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
