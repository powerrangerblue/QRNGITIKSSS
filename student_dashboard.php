<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: student_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

include 'db.php';
date_default_timezone_set('Asia/Manila');

// Get program and standard time
$programResult = $conn->query("SELECT program_name, event_start FROM event_time WHERE id = 1");
if ($programResult && $programResult->num_rows > 0) {
    $eventData = $programResult->fetch_assoc();
    $current_program = $eventData['program_name'];
    $standard_time_in = $eventData['event_start'];
} else {
    $current_program = "N/A";
    $standard_time_in = "08:00:00";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Student Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  /* KEEP your entire existing CSS exactly as you gave it */
  body {
    background: #e0f7fa;
    font-family: 'Comic Sans MS', cursive, sans-serif;
    margin: 0;
    padding: 0;
    color: #004d40;
  }
  .navbar {
    background-color: #00796b;
    padding: 1rem 2rem;
    color: white;
    font-weight: 700;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .navbar a {
    color: white;
    text-decoration: none;
    font-weight: 700;
  }
  .navbar a:hover {
    text-decoration: underline;
  }
  .container {
    max-width: 900px;
    margin: 40px auto 80px;
    background: white;
    padding: 2.5rem 3rem;
    border-radius: 20px;
    box-shadow: 0 8px 15px rgba(0, 77, 64, 0.2);
  }
  h2, h3, h4 {
    color: #00796b;
    margin-top: 0;
    margin-bottom: 1rem;
    font-weight: 700;
  }
  .welcome-text p {
    font-size: 1.1rem;
    margin: 0.3rem 0;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }
  thead {
    background-color: #004d40;
    color: white;
  }
  th, td {
    padding: 0.75rem 1rem;
    border: 1px solid #b2dfdb;
    text-align: center;
    font-size: 1rem;
  }
  .late {
    color: #d32f2f;
    font-weight: 700;
  }
  .footer {
    text-align: center;
    padding: 15px;
    color: #004d40;
    font-size: 0.9rem;
    margin-top: 60px;
    border-top: 1px solid #b2dfdb;
  }
  @media (max-width: 600px) {
    .container {
      padding: 1.5rem 2rem;
      margin: 20px;
    }
    th, td {
      font-size: 0.85rem;
      padding: 0.5rem 0.75rem;
    }
  }
</style>
</head>
<body>

<nav class="navbar">
  <div>Student Dashboard</div>
  <a href="student_logout.php">Logout</a>
</nav>

<div class="container">
  <div class="welcome-text">
    <h2>Welcome, <?= htmlspecialchars($fullname) ?>!</h2>
    <p>Your Student ID: <strong><?= htmlspecialchars($user_id) ?></strong></p>
    <p>Current Program: <strong><?= htmlspecialchars($current_program) ?></strong></p>
  </div>

  <h3>Your Attendance Records</h3>
  <div id="attendance-table">
    <?php
    $stmt = $conn->prepare("SELECT date, time_in, time_out FROM attendance WHERE student_id = ? ORDER BY date DESC");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<thead><tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Fine</th></tr></thead><tbody>";

        while ($row = $result->fetch_assoc()) {
            $date = htmlspecialchars($row['date']);
            $time_in_24 = $row['time_in'];
            $time_out_24 = $row['time_out'];

            $time_in = $time_in_24 ? date("h:i A", strtotime($time_in_24)) : '-';
            $time_out = $time_out_24 ? date("h:i A", strtotime($time_out_24)) : '-';

            $fine = 0;
            $late_class = '';

            if ($time_in_24 && strtotime($time_in_24) > strtotime($standard_time_in)) {
                $fine = 10.00;
                $late_class = 'late';
            }

            echo "<tr>";
            echo "<td>$date</td>";
            echo "<td class='$late_class'>$time_in</td>";
            echo "<td>$time_out</td>";
            echo "<td>₱" . number_format($fine, 2) . "</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p class='text-muted'>No attendance records found.</p>";
    }
    ?>
  </div>
</div>

<div class="footer">
  &copy; <?= date("Y") ?> Ramon Magsaysay Memorial Colleges. All rights reserved.
</div>

</body>
</html>
