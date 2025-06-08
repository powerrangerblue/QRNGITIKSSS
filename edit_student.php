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

$student_id = $_GET['id'] ?? '';
if (!$student_id) {
  header('Location: students_list.php');
  exit;
}

$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Update student info
  $name = $_POST['name'];
  $course = $_POST['course'];
  $year = $_POST['year'];

  $stmt = $conn->prepare("UPDATE students SET name=?, course=?, year=? WHERE student_id=?");
  $stmt->bind_param("ssss", $name, $course, $year, $student_id);

  if ($stmt->execute()) {
    $response = ['success' => true, 'message' => 'Student info updated successfully.'];
  } else {
    $response = ['success' => false, 'message' => 'Update failed: ' . $conn->error];
  }

  $stmt->close();
}

// Fetch current student data
$stmt = $conn->prepare("SELECT student_id, name, course, year FROM students WHERE student_id=?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
  header('Location: students_list.php');
  exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Student</title>
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
    .container {
      max-width: 700px;
      background-color: #2f2f48;
      padding: 30px;
      border-radius: 6px;
      box-shadow: 0 0 10px #000000a0;
    }
    h2 {
      margin-bottom: 30px;
      color: #ffffff;
    }
    label {
      color: #ddd;
      font-weight: 600;
    }
    .form-control {
      background-color: #3a3a5c;
      border: none;
      color: #eee;
    }
    .form-control:focus {
      background-color: #50507a;
      color: #fff;
      border-color: #f0ad4e;
      box-shadow: 0 0 5px #f0ad4e;
    }
    .btn-primary {
      background-color: #f0ad4e;
      border: none;
      color: #000;
      font-weight: 600;
    }
    .btn-primary:hover {
      background-color: #ec971f;
      color: #000;
    }
    .btn-default {
      background-color: #444466;
      border: none;
      color: #eee;
      font-weight: 600;
      margin-left: 10px;
    }
    .btn-default:hover {
      background-color: #5a5a7d;
      color: #fff;
    }
    .alert {
      margin-top: 20px;
      border: none;
      font-weight: 600;
    }
    .alert-success {
      background-color: #3a8235;
      color: #e8f5e9;
    }
    .alert-danger {
      background-color: #a83232;
      color: #f8d7da;
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
    </ul>
    <ul class="nav navbar-nav navbar-right">
      <li><p class="navbar-text">Logged in as: <?= htmlspecialchars($_SESSION['name']); ?></p></li>
      <li><a href="admin_logout.php" style="color: red;"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
    </ul>
  </div>
</nav>

<div class="container">
  <h2 class="text-center">Edit Student</h2>

  <?php if ($response): ?>
    <div class="alert <?= $response['success'] ? 'alert-success' : 'alert-danger' ?>">
      <?= htmlspecialchars($response['message']) ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="form-horizontal">
    <div class="form-group">
      <label class="control-label col-sm-3">Student ID:</label>
      <div class="col-sm-9">
        <input type="text" class="form-control" value="<?= htmlspecialchars($student['student_id']) ?>" disabled />
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="control-label col-sm-3">Full Name:</label>
      <div class="col-sm-9">
        <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($student['name']) ?>" />
      </div>
    </div>
    <div class="form-group">
      <label for="course" class="control-label col-sm-3">Course:</label>
      <div class="col-sm-9">
        <input type="text" name="course" id="course" class="form-control" required value="<?= htmlspecialchars($student['course']) ?>" />
      </div>
    </div>
    <div class="form-group">
      <label for="year" class="control-label col-sm-3">Year:</label>
      <div class="col-sm-9">
        <input type="text" name="year" id="year" class="form-control" required value="<?= htmlspecialchars($student['year']) ?>" />
      </div>
    </div>
    <div class="form-group text-center">
      <button type="submit" class="btn btn-primary">Update Student</button>
      <a href="students_list.php" class="btn btn-default">Cancel</a>
    </div>
  </form>
</div>

</body>
</html>
