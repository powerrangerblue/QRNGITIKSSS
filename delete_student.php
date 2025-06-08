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
    // No id provided, redirect back to list
    header("Location: students_list.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM students WHERE student_id=?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: students_list.php");
exit();
?>
