<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'qrcode_db');
date_default_timezone_set('Asia/Manila');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['student']) && !empty($_POST['student'])) {
        $studentId = trim($_POST['student']);
        $today = date('Y-m-d');

        // Check if student exists
        $stmt = $conn->prepare("SELECT student_id, name FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "❌ Student ID not found.";
            exit;
        }

        $student = $result->fetch_assoc();
        $stmt->close();

        // Check today's attendance
        $stmt = $conn->prepare("SELECT id, time_out FROM attendance WHERE student_id = ? AND date = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("ss", $studentId, $today);
        $stmt->execute();
        $attResult = $stmt->get_result();

        if ($attResult->num_rows === 0) {
            // No record today, insert time_in
            $time_in = date('H:i:s');
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, name, time_in, date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $student['student_id'], $student['name'], $time_in, $today);
            $stmt->execute();
            echo "✅ Time In recorded for {$student['name']}.";
        } else {
            $att = $attResult->fetch_assoc();
            if (empty($att['time_out'])) {
                // Update time_out
                $time_out = date('H:i:s');
                $stmt = $conn->prepare("UPDATE attendance SET time_out = ? WHERE id = ?");
                $stmt->bind_param("si", $time_out, $att['id']);
                $stmt->execute();
                echo "✅ Time Out recorded for {$student['name']}.";
            } else {
                echo "⚠️ Attendance already completed for today.";
            }
        }

        $stmt->close();
    } else {
        echo "❌ Invalid or missing student ID.";
    }
} else {
    echo "❌ Invalid request method.";
}

$conn->close();
?>
