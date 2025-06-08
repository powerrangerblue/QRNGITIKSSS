<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'qrcode_db');
if ($conn->connect_error) {
    echo "Database connection failed.";
    exit;
}
date_default_timezone_set('Asia/Manila');

// Get current program name
$eventResult = $conn->query("SELECT program_name FROM event_time WHERE id = 1");
if ($eventResult && $eventResult->num_rows > 0) {
    $program_name = $eventResult->fetch_assoc()['program_name'];
    $table_name = 'attendance_' . strtolower(preg_replace('/\s+/', '_', $program_name));
} else {
    echo "No program set.";
    exit;
}

// Check if attendance table exists
$tableCheck = $conn->query("SHOW TABLES LIKE '$table_name'");
if ($tableCheck->num_rows === 0) {
    echo "Attendance table for program does not exist.";
    exit;
}

// Fetch all live attendance entries
$liveAttendanceResult = $conn->query("SELECT student_id, name, time_in, time_out, date FROM live_attendance");

if (!$liveAttendanceResult || $liveAttendanceResult->num_rows === 0) {
    echo "No attendance records to save.";
    exit;
}

$successCount = 0;
$failCount = 0;

while ($row = $liveAttendanceResult->fetch_assoc()) {
    $student_id = $conn->real_escape_string($row['student_id']);
    $name = $conn->real_escape_string($row['name']);
    $time_in = $conn->real_escape_string($row['time_in']);
    $time_out = $conn->real_escape_string($row['time_out']);
    $date = $conn->real_escape_string($row['date']);

    // Check if record already exists for student on the same date
    $checkSql = "SELECT id FROM `$table_name` WHERE student_id = '$student_id' AND date = '$date'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult && $checkResult->num_rows > 0) {
        // Update existing record
        $updateSql = "UPDATE `$table_name` SET time_in = '$time_in', time_out = '$time_out', name='$name' WHERE student_id = '$student_id' AND date = '$date'";
        if ($conn->query($updateSql)) {
            $successCount++;
        } else {
            $failCount++;
        }
    } else {
        // Insert new record
        $insertSql = "INSERT INTO `$table_name` (student_id, name, time_in, time_out, date) VALUES ('$student_id', '$name', '$time_in', '$time_out', '$date')";
        if ($conn->query($insertSql)) {
            $successCount++;
        } else {
            $failCount++;
        }
    }
}

// Optionally clear live attendance after save
$conn->query("TRUNCATE TABLE live_attendance");

echo "Save successful: $successCount records saved, $failCount failed.";

$conn->close();
?>
