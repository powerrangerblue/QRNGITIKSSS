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

$result = $conn->query("SELECT student_id, name, course, year FROM students ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Student List</title>
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
    .btn-warning {
      background-color: #f0ad4e;
      border: none;
      color: #000;
    }
    .btn-danger {
      background-color: #d9534f;
      border: none;
    }
    .btn-warning:hover {
      background-color: #ec971f;
    }
    .btn-danger:hover {
      background-color: #c9302c;
    }
    .alert {
      color: #ffffff;
      background-color: #444;
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
      <li class="active"><a href="students_list.php">Student List</a></li>
      <li><a href="attendance_report.php">Attendance Report</a></li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
      <li><p class="navbar-text">Logged in as: <?= htmlspecialchars($_SESSION['name']); ?></p></li>
      <li><a href="admin_logout.php" style="color: red;"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
    </ul>
  </div>
</nav>

<div class="container">
  <h2 class="text-center">Registered Students</h2>

  <?php if ($result && $result->num_rows > 0): ?>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th>Course</th>
          <th>Year</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['student_id']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['course']) ?></td>
            <td><?= htmlspecialchars($row['year']) ?></td>
            <td>
              <a href="edit_student.php?id=<?= urlencode($row['student_id']) ?>" class="btn btn-sm btn-warning">Edit</a>
              <a href="delete_student.php?id=<?= urlencode($row['student_id']) ?>" class="btn btn-sm btn-danger">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert text-center">No students registered yet.</div>
  <?php endif; ?>
</div>

</body>
</html>
<?php $conn->close(); ?>
