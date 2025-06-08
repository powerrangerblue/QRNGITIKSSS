<?php
$conn = new mysqli('localhost', 'root', '', 'qrcode_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
date_default_timezone_set('Asia/Manila');

function formatTime12Hour($time24) {
    if (!$time24) return '';
    return date("g:i A", strtotime($time24));
}

function calculateFine($time_in) {
    $lateThreshold = strtotime("08:00:00");
    if (!$time_in) return 0;
    $timeInTimestamp = strtotime($time_in);
    return ($timeInTimestamp > $lateThreshold) ? 10 : 0;
}

if (isset($_GET['date'])) {
    $date = $conn->real_escape_string($_GET['date']);

    $stmt = $conn->prepare("SELECT student_id, name, time_in, time_out, date FROM attendance WHERE date = ? ORDER BY time_in ASC");
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Export to Excel
    if (isset($_GET['export']) && $_GET['export'] === 'excel') {
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=attendance_report_" . $date . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<table border='1'>";
        echo "<tr><th>Student ID</th><th>Name</th><th>Time In</th><th>Time Out</th><th>Date</th><th>Fine (₱)</th></tr>";

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $fine = calculateFine($row['time_in']);
                echo "<tr>
                        <td>" . htmlspecialchars($row['student_id']) . "</td>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . formatTime12Hour($row['time_in']) . "</td>
                        <td>" . ($row['time_out'] ? formatTime12Hour($row['time_out']) : '-') . "</td>
                        <td>" . htmlspecialchars($row['date']) . "</td>
                        <td>₱" . $fine . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No attendance records found for " . htmlspecialchars($date) . ".</td></tr>";
        }
        echo "</table>";
        $stmt->close();
        exit;
    }

    // AJAX HTML View
    if ($result->num_rows > 0) {
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Student ID</th><th>Name</th><th>Time In</th><th>Time Out</th><th>Date</th><th>Fine (₱)</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            $fine = calculateFine($row['time_in']);
            echo "<tr>
                    <td>" . htmlspecialchars($row['student_id']) . "</td>
                    <td>" . htmlspecialchars($row['name']) . "</td>
                    <td>" . formatTime12Hour($row['time_in']) . "</td>
                    <td>" . ($row['time_out'] ? formatTime12Hour($row['time_out']) : '-') . "</td>
                    <td>" . htmlspecialchars($row['date']) . "</td>
                    <td>₱" . $fine . "</td>
                  </tr>";
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="text-center">No attendance records found for ' . htmlspecialchars($date) . '.</p>';
    }

    $stmt->close();
    exit;
} else {
    // If no date parameter provided, send a message
    echo '<p class="text-center text-danger">Date parameter is required to fetch attendance records.</p>';
}
?>
