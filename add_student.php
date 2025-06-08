<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$response = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli("localhost", "root", "", "qrcode_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $student_id = trim($_POST['student_id']); // Trim whitespace
    $name = trim($_POST['name']);
    $course = trim($_POST['course']);
    $year = trim($_POST['year']);

    // Check if student ID already exists
    $check_stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
    $check_stmt->bind_param("s", $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $response = ['success' => false, 'error' => 'Student ID already exists'];
    } else {
        $stmt = $conn->prepare("INSERT INTO students (student_id, name, course, year) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $student_id, $name, $course, $year);

        if ($stmt->execute()) {
            $response = ['success' => true, 'student_id' => $student_id];
        } else {
            $response = ['success' => false, 'error' => $conn->error];
        }
        $stmt->close();
    }
    
    $check_stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Student & QR Code</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <style>
    body {
      background-color: #1e1e2f;
      color: #f0f0f0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar {
      margin-bottom: 30px;
      border: none;
      border-radius: 0;
      background-color: #2a2a40;
    }
    .navbar-default .navbar-brand,
    .navbar-default .navbar-nav > li > a {
      color: #ffffff;
    }
    .panel {
      background-color: #2f2f48;
      border-color: #444;
      color: #f0f0f0;
    }
    .panel-heading {
      background-color: #3a3a5c !important;
      color: #ffffff !important;
      font-weight: bold;
    }
    .form-control {
      background-color: #1e1e30;
      color: #f0f0f0;
      border: 1px solid #444;
    }
    .form-control:focus {
      background-color: #262640;
      border-color: #5e9eff;
      box-shadow: none;
    }
    .btn-primary {
      background-color: #4c8bf5;
      border: none;
      font-weight: bold;
    }
    .btn-primary:hover {
      background-color: #3a6ac9;
    }
    .alert-success {
      background-color: #2e7d32;
      color: white;
      border: none;
    }
    .alert-danger {
      background-color: #c62828;
      color: white;
      border: none;
    }
    #qr {
      margin-top: 20px;
      padding: 20px;
      background: #ffffff;
      border-radius: 12px;
      display: inline-block;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    #qr h4 {
      color: #333;
      text-align: center;
      margin-bottom: 15px;
    }
    canvas {
      display: block;
      margin: 0 auto;
      border: 2px solid #333;
    }
    .qr-info {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-top: 15px;
      color: #333;
    }
    @media print {
      body * {
        visibility: hidden;
      }
      #qr, #qr * {
        visibility: visible;
      }
      #qr {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
      }
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
      <li class="active"><a href="add_student.php">Add Student</a></li>
      <li><a href="students_list.php">Student List</a></li>
      <li><a href="attendance_report.php">Attendance Report</a></li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
      <li><p class="navbar-text">Logged in as: <?= htmlspecialchars($_SESSION['name']); ?></p></li>
      <li><a href="admin_logout.php" style="color: red;"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
    </ul>
  </div>
</nav>

<div class="container">
  <h2 class="text-center">Register New Student</h2>

  <div class="panel panel-primary">
    <div class="panel-heading text-center">Student Information</div>
    <div class="panel-body">
      <form method="POST" class="form-horizontal">
        <div class="form-group">
          <label class="control-label col-sm-2">Student ID:</label>
          <div class="col-sm-10">
            <input type="text" name="student_id" class="form-control" required pattern="[0-9A-Za-z]+" title="Student ID should contain only letters and numbers" />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2">Full Name:</label>
          <div class="col-sm-10">
            <input type="text" name="name" class="form-control" required />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2">Course:</label>
          <div class="col-sm-10">
            <input type="text" name="course" class="form-control" required />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2">Year:</label>
          <div class="col-sm-10">
            <input type="text" name="year" class="form-control" required />
          </div>
        </div>
        <div class="form-group text-center">
          <button type="submit" class="btn btn-primary">Add Student</button>
        </div>
      </form>

      <?php if ($response && $response['success']): ?>
        <div class="alert alert-success text-center">
          Student successfully added. QR code has been generated.
        </div>
        <div class="text-center">
          <div id="qr">
            <h4><strong>QR Code for Student ID: <?= htmlspecialchars($response['student_id']) ?></strong></h4>
            <canvas id="qrCanvas"></canvas>
            <div class="qr-info">
              <p><strong>Student ID:</strong> <?= htmlspecialchars($response['student_id']) ?></p>
              <p><small>Scan this QR code for attendance</small></p>
            </div>
          </div>
          <div class="text-center" style="margin-top: 15px;">
            <a id="downloadLink" class="btn btn-success" download="qr_<?= htmlspecialchars($response['student_id']) ?>.png">Download QR Code</a>
            <button onclick="window.print();" class="btn btn-info">Print QR Code</button>
            <a href="add_student.php" class="btn btn-warning">Add Another Student</a>
            <a href="index.php" class="btn btn-default">Back to Home</a>
          </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
        <script>
          // Generate QR code with consistent settings
          const studentId = "<?= htmlspecialchars($response['student_id']) ?>";
          
          const qr = new QRious({
            element: document.getElementById('qrCanvas'),
            value: studentId,
            size: 300,
            level: 'M', // Error correction level
            background: '#ffffff',
            foreground: '#000000',
            padding: 10
          });
          
          // Set download link
          document.getElementById('downloadLink').href = qr.toDataURL('image/png');
          
          // Test the QR code content
          console.log('QR Code contains:', studentId);
        </script>
      <?php elseif ($response && !$response['success']): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($response['error']) ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>