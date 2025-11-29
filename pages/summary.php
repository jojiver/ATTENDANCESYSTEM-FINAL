<?php
session_start();
include("../config.php");

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin-login.php");
    exit();
}

// DATE FILTER
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// FETCH ALL SECTIONS
$sections = ["A", "B", "C", "D"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Summary - AMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <style>
/* -----------------------------------------
   GLOBAL STYLING (MATCH DASHBOARD)
----------------------------------------- */
body {
    font-family: 'Poppins', sans-serif;
    background: #2e2b4f; /* same dashboard background */
    margin: 0;
    padding: 0;
}

/* Main container */
.container {
    width: 90%;
    margin: 40px auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

/* Title */
h2 {
    color: white;
    margin-bottom: 25px;
    text-align: center;
    font-weight: 700;
    letter-spacing: 1px;
}

/* Form label */
label {
    color: white;
    font-weight: 600;
}

/* Date picker input */
input[type="date"] {
    border-radius: 10px;
    padding: 8px 10px;
}

/* -----------------------------------------
   SUMMARY CARDS (Present / Absent per section)
----------------------------------------- */
.summary-card {
    background: #f6f5ff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    border-left: 6px solid #5b538d; /* purple accent */
}

/* Section header */
.section-title {
    background: #5b538d;
    color: white;
    padding: 8px 12px;
    font-weight: 600;
    border-radius: 6px;
    width: fit-content;
    margin-bottom: 16px;
}

/* List titles */
.present {
    color: #2e8b57;
    font-weight: 700;
    margin-bottom: 10px;
}

.absent {
    color: #d62828;
    font-weight: 700;
    margin-bottom: 10px;
}

/* Student list style */
.summary-card ul {
    padding-left: 18px;
    margin: 0;
}

.summary-card ul li {
    padding: 4px 0;
    font-size: 15px;
}

/* -----------------------------------------
   BUTTONS
----------------------------------------- */
.btn-primary {
    background-color: #8d86d6 !important;
    border: none;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 8px;
}

.btn-primary:hover {
    background-color: #7b73cc !important;
}
</style>

</head>

<body class="p-4">

    <div class="container">

        <h2 class="text-center text-white fw-bold mb-4 " style="color:black !important;">Daily Attendance Summary</h2>

        
        <form method="GET" action="" class="mb-4">
            <label class="text-white fw-semibold mb-2">Select Date</label>
            <input type="date" name="date" class="form-control" value="<?= $selected_date ?>" onchange="this.form.submit()">
        </form>

        <?php foreach ($sections as $sec): ?>

            <?php
            
            $present_q = $conn->prepare("
                SELECT fullname FROM attendance 
                WHERE section = ? AND date = ? AND status = 'Present'
                ORDER BY fullname ASC
            ");
            $present_q->bind_param("ss", $sec, $selected_date);
            $present_q->execute();
            $present_res = $present_q->get_result();

            
            $students_q = $conn->prepare("SELECT fullname FROM students WHERE section = ? ORDER BY fullname ASC");
            $students_q->bind_param("s", $sec);
            $students_q->execute();
            $all_students_res = $students_q->get_result();

       
            $present_list = [];
            while ($r = $present_res->fetch_assoc()) {
                $present_list[] = $r['fullname'];
            }

         
            $absent_list = [];
            while ($stu = $all_students_res->fetch_assoc()) {
                if (!in_array($stu['fullname'], $present_list)) {
                    $absent_list[] = $stu['fullname'];
                }
            }
            ?>

            <!-- CARD FOR EACH SECTION -->
            <div class="summary-card">
                <p class="section-title">Section <?= $sec ?></p>

                <div class="row">
                   
                    <div class="col-md-6">
                        <h5 class="present">Present</h5>
                        <?php if (count($present_list) > 0): ?>
                            <ul>
                                <?php foreach ($present_list as $p): ?>
                                    <li><?= htmlspecialchars($p) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No present students</p>
                        <?php endif; ?>
                    </div>

                    
                    <div class="col-md-6">
                        <h5 class="absent">Absent</h5>
                        <?php if (count($absent_list) > 0): ?>
                            <ul>
                                <?php foreach ($absent_list as $a): ?>
                                    <li><?= htmlspecialchars($a) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No absent students</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        <?php endforeach; ?>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-primary" style="background-color: #8d86d6;">Back to Dashboard</a>
        </div>

    </div>

</body>
</html>