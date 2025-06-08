<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized access.";
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'qrcode_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Remove program_name since it's no longer in the form
    $event_start = isset($_POST['event_start']) ? $conn->real_escape_string($_POST['event_start']) : '';
    $event_end = isset($_POST['event_end']) ? $conn->real_escape_string($_POST['event_end']) : '';

    if (!$event_start || !$event_end) {
        echo "⚠️ Please provide both start and end time.";
        exit();
    }

    // Update only event_start and event_end
    $sql = "UPDATE event_time SET event_start = '$event_start', event_end = '$event_end' WHERE id = 1";

    if ($conn->query($sql)) {
        echo "✅ Program details updated successfully.";
    } else {
        echo "❌ Failed to update program details.";
    }
} else {
    echo "Invalid request.";
}
?>
