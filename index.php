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

// Fetch event/program info (only start/end times now)
$eventResult = $conn->query("SELECT event_start, event_end FROM event_time WHERE id = 1");
if ($eventResult && $eventResult->num_rows > 0) {
    $eventData = $eventResult->fetch_assoc();
    $event_start = $eventData['event_start'];
    $event_end = $eventData['event_end'];
} else {
    $event_start = '';
    $event_end = '';
}

// Count registered students
$countResult = $conn->query("SELECT COUNT(*) as total FROM students");
$count = ($countResult && $countResult->num_rows > 0) ? $countResult->fetch_assoc()['total'] : 0;

// Removed fetching of pending QR requests here since we won't show them
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>QR Code Scanner Attendance</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
  <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
  <style>
    body {
      background-color: #1e1e2f;
      color: #f0f0f0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar {
      margin-bottom: 30px;
      border-radius: 0;
      border: none;
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
    #preview {
      border-radius: 6px;
      box-shadow: 0 0 10px #333;
    }
    #text {
      font-weight: bold;
      font-size: 1.2rem;
      background-color: #fffacd;
      color: #000;
      text-align: center;
      margin-top: 10px;
    }
    #scanNotification {
      margin-top: 10px;
      font-weight: bold;
      font-size: 1rem;
      text-align: center;
    }
    #event-time-msg {
      font-size: 0.95rem;
    }
    h2, h3 {
      color: #ffdd57;
    }
    .table-container {
      background-color: #212132;
      padding: 15px;
      border-radius: 10px;
      margin-top: 15px;
    }
    .table {
      background-color: #2e2e44;
      color: #f0f0f0;
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
      <li class="active"><a href="index.php">Home</a></li>
      <li><a href="add_student.php">Add Student</a></li>
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
  <h2 class="text-center">QR Code Attendance</h2>

  <div class="panel panel-default">
    <div class="panel-heading"><strong>Set Program Time</strong></div>
    <div class="panel-body">
      <form id="event-time-form" class="form-inline">
        <div class="form-group" style="margin-right:15px;">
          <label for="event_start">Start Time:</label>
          <input type="time" name="event_start" id="event_start" class="form-control" required value="<?= htmlspecialchars($event_start) ?>">
        </div>
        <div class="form-group" style="margin-right:15px;">
          <label for="event_end">End Time:</label>
          <input type="time" name="event_end" id="event_end" class="form-control" required value="<?= htmlspecialchars($event_end) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Time</button>
      </form>
      <div id="event-time-msg" style="margin-top:10px; font-weight: bold;"></div>
    </div>
  </div>

  <div class="panel panel-info" style="margin-top: 20px;">
    <div class="panel-heading"><strong>Registered Students</strong></div>
    <div class="panel-body text-center">
      <p>Total Students Registered: <strong><?= $count ?></strong></p>
      <a href="students_list.php" class="btn btn-primary">View Full Student List</a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <h4 class="text-center">Live Camera</h4>
      <video id="preview" width="100%" height="auto"></video>
      <div id="scanNotification"></div>
    </div>
    <div class="col-md-6">
      <h4 class="text-center">Scan Result</h4>
      <input type="text" id="text" name="text" class="form-control" placeholder="Waiting for QR scan..." readonly />
    </div>
  </div>

  <hr />
  <h3 class="text-center">Live Attendance Table</h3>
  <div class="table-container" id="table-container"></div>

  <!-- Removed Pending QR Requests Section -->

</div>

<script>
  let scanner = null;
  const notif = document.getElementById('scanNotification');

  function startScanner() {
    Instascan.Camera.getCameras().then(function (cameras) {
      if (cameras.length > 0) {
        scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
        scanner.start(cameras[0]);

        scanner.addListener('scan', function (content) {
          document.getElementById('text').value = content;

          fetch('log_scan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'student=' + encodeURIComponent(content),
          })
          .then((response) => response.text())
          .then((data) => {
            notif.textContent = data;
            notif.style.color = data.includes('✅') ? 'limegreen' : 'red';
            setTimeout(() => { notif.textContent = ''; }, 5000);
            loadTable();
          });
        });
      } else {
        notif.textContent = 'No cameras found.';
      }
    }).catch(function () {
      notif.textContent = 'Camera access error.';
    });
  }

  function loadTable() {
    fetch('attendance_table.php')
      .then((res) => res.text())
      .then((html) => {
        document.getElementById('table-container').innerHTML = html;
      });
  }

  loadTable();
  startScanner();

  document.getElementById('event-time-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const startTime = document.getElementById('event_start').value;
    const endTime = document.getElementById('event_end').value;

    fetch('set_event_time.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'event_start=' + encodeURIComponent(startTime) + 
            '&event_end=' + encodeURIComponent(endTime),
    })
    .then(res => res.text())
    .then(data => {
      const msg = document.getElementById('event-time-msg');
      msg.textContent = data;
      msg.style.color = data.includes("⚠️") ? 'orange' : 'limegreen';
    });
  });
</script>
</body>
</html>
