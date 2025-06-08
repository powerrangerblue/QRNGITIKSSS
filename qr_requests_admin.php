<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];

    if (in_array($action, ['approve', 'reject'])) {
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE qr_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $request_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all requests
$result = $conn->query("SELECT * FROM qr_requests ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin - QR Code Requests</title>
<style>
  body { font-family: Arial, sans-serif; background: #1b2735; color: #e0e6f0; padding: 20px; }
  table { border-collapse: collapse; width: 100%; background: #273746; }
  th, td { padding: 12px 15px; border: 1px solid #444; text-align: left; }
  th { background: #4c8bf5; }
  button { padding: 6px 12px; margin-right: 5px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
  .approve { background: #28a745; color: white; }
  .reject { background: #dc3545; color: white; }
</style>
</head>
<body>

<h2>Student QR Code Requests</h2>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Student ID</th>
      <th>Full Name</th>
      <th>Status</th>
      <th>Requested At</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['student_id']) ?></td>
        <td><?= htmlspecialchars($row['fullname']) ?></td>
        <td><?= ucfirst($row['status']) ?></td>
        <td><?= $row['requested_at'] ?></td>
        <td>
          <?php if ($row['status'] === 'pending'): ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="request_id" value="<?= $row['id'] ?>" />
              <button type="submit" name="action" value="approve" class="approve">Approve</button>
              <button type="submit" name="action" value="reject" class="reject">Reject</button>
            </form>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan="6" style="text-align:center;">No requests found.</td></tr>
  <?php endif; ?>
  </tbody>
</table>

</body>
</html>
