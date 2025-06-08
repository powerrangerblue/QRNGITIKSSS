<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'qrcode_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
date_default_timezone_set('Asia/Manila');

function formatTime12Hour($time24) {
    if (!$time24) return '';
    return date("g:i A", strtotime($time24));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Attendance Report</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
  <style>
    body {
      background-color: #1e1e2f;
      color: #f0f0f0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar {
      background-color: #2a2a40;
      border: none;
      border-radius: 0;
      margin-bottom: 30px;
    }
    .navbar-default .navbar-brand,
    .navbar-default .navbar-nav > li > a {
      color: #ffffff;
    }
    .container h2 {
      margin-bottom: 30px;
      color: #ffffff;
    }
    label {
      color: #ffffff;
    }
    .form-control {
      background-color: #2f2f48;
      color: #ffffff;
      border: 1px solid #444;
    }
    .btn-primary {
      background-color: #337ab7;
      border: none;
    }
    .btn-success {
      background-color: #5cb85c;
      border: none;
    }
    .btn-primary:hover {
      background-color: #286090;
    }
    .btn-success:hover {
      background-color: #449d44;
    }
    table {
      background-color: #2f2f48;
      color: #f0f0f0;
    }
    th, td {
      background-color: #2f2f48 !important;
      color: #ffffff !important;
    }
    th {
      background-color: #3a3a5c !important;
    }
    .alert {
      background-color: #444;
      color: #fff;
      border: none;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="index.php">QR Attendance System</a>
    </div>
    <ul class="nav navbar-nav">
      <li><a href="index.php">Home</a></li>
      <li><a href="add_student.php">Add Student</a></li>
      <li><a href="students_list.php">Student List</a></li>
      <li class="active"><a href="attendance_report.php">Attendance Report</a></li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
      <li><p class="navbar-text">Logged in as: <?= htmlspecialchars($_SESSION['name']); ?></p></li>
      <li><a href="admin_logout.php" style="color: red;"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
    </ul>
  </div>
</nav>

<div class="container">
  <h2 class="text-center">Attendance Report</h2>
  
  <form id="attendance-report-form" class="form-inline text-center" style="margin-bottom: 20px;">
    <label for="attendance_date">Select Date: </label>
    <input type="date" id="attendance_date" name="attendance_date" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
    <button type="submit" class="btn btn-primary" style="margin-left:10px;">View Attendance</button>
    <button type="button" id="exportExcelBtn" class="btn btn-success" style="margin-left:10px;" disabled>Export to Excel</button>
  </form>

  <div id="attendance-report-container"></div>
</div>

<script>
  document.getElementById('attendance-report-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const selectedDate = document.getElementById('attendance_date').value;
    if (!selectedDate) return;

    fetch('fetch_attendance_report.php?date=' + encodeURIComponent(selectedDate))
      .then(res => res.text())
      .then(html => {
        document.getElementById('attendance-report-container').innerHTML = html;
        document.getElementById('exportExcelBtn').disabled = false;
        document.getElementById('exportExcelBtn').dataset.date = selectedDate;
      })
      .catch(() => {
        document.getElementById('attendance-report-container').innerHTML = '<p class="alert text-center">Failed to load attendance report.</p>';
        document.getElementById('exportExcelBtn').disabled = true;
      });
  });

  document.getElementById('exportExcelBtn').addEventListener('click', function() {
    const date = this.dataset.date;
    if (!date) return;
    window.open('fetch_attendance_report.php?date=' + encodeURIComponent(date) + '&export=excel', '_blank');
  });

  window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('attendance-report-form').dispatchEvent(new Event('submit'));
  });
</script>

</body>
</html>

<?php $conn->close(); ?>
