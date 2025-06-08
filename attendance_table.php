<?php
$conn = new mysqli("localhost", "root", "", "qrcode_db");
date_default_timezone_set("Asia/Manila");

$table = "attendance";

$result = $conn->query("SELECT * FROM $table ORDER BY id DESC");

echo '<div class="table-responsive"><table class="table table-bordered">';
echo '<thead><tr><th>ID</th><th>Student ID</th><th>Name</th><th>Time In</th><th>Time Out</th><th>Date</th></tr></thead><tbody>';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['student_id']}</td>
                <td>{$row['name']}</td>
                <td>" . date("h:i A", strtotime($row['time_in'])) . "</td>
                <td>" . ($row['time_out'] ? date("h:i A", strtotime($row['time_out'])) : '-') . "</td>
                <td>{$row['date']}</td>
              </tr>";
    }
} else {
    echo '<tr><td colspan="6" class="text-center">No attendance yet.</td></tr>';
}

echo '</tbody></table></div>';
?>
