<?php
// Returns JSON { event_start, event_end }

$conn = new mysqli('localhost','root','','qrcode_db');
if($conn->connect_error) die('DB error');

$result = $conn->query("SELECT event_start, event_end FROM event_time WHERE id=1");
$row = $result->fetch_assoc();
echo json_encode([
  'event_start' => $row['event_start'] ?? '',
  'event_end' => $row['event_end'] ?? ''
]);
$conn->close();
?>
