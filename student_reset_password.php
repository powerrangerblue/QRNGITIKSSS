<?php
include 'db.php';

$token = $_GET['token'] ?? '';
$message = '';
$show_form = true;

if (!$token) {
    $message = "Invalid or missing token.";
    $show_form = false;
} else {
    // Check if token exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND role = 'student'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $message = "Invalid or expired token.";
        $show_form = false;
    } else {
        $user = $result->fetch_assoc();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (strlen($password) < 6) {
                $message = "Password must be at least 6 characters.";
            } elseif ($password !== $confirm) {
                $message = "Passwords do not match.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE user_id = ?");
                $stmt->bind_param("ss", $hash, $user['user_id']);
                $stmt->execute();

                $message = "Password has been reset. <a href='student_login.php'>Login here</a>.";
                $show_form = false;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Reset Password</title>
  <style>
    body {
      background: #e0f7fa;
      font-family: 'Comic Sans MS', cursive, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      color: #004d40;
    }
    .container {
      background: #ffffff;
      border-radius: 16px;
      padding: 2.5rem 3rem;
      box-shadow: 0 8px 15px rgba(0, 77, 64, 0.2);
      width: 400px;
      text-align: center;
    }
    h2 {
      margin-bottom: 1.5rem;
      font-weight: 700;
      font-size: 2.2rem;
      color: #00796b;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
    }
    input[type="password"] {
      padding: 0.9rem 1rem;
      font-size: 1.1rem;
      border-radius: 12px;
      border: 2px solid #b2dfdb;
      transition: border-color 0.3s ease;
    }
    input[type="password"]:focus {
      outline: none;
      border-color: #00796b;
      box-shadow: 0 0 6px #004d40;
    }
    button {
      padding: 1rem;
      background-color: #00796b;
      color: white;
      font-weight: 700;
      font-size: 1.15rem;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      margin-top: 1rem;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #004d40;
    }
    .message {
      color: #d32f2f;
      margin-top: 1rem;
      font-weight: 700;
    }
    .success {
      color: #388e3c;
    }
    a {
      color: #004d40;
      text-decoration: none;
      font-weight: bold;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Reset Password</h2>

    <?php if ($message): ?>
      <div class="message <?= $show_form ? '' : 'success' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <?php if ($show_form): ?>
      <form method="POST">
        <input type="password" name="password" placeholder="New Password" required minlength="6" />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="6" />
        <button type="submit">Reset Password</button>
      </form>
    <?php endif; ?>
  </div>

</body>
</html>
