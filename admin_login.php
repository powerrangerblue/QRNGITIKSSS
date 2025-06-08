<?php
session_start();
include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = trim($_POST['admin_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$admin_id || !$password) {
        $message = "Please enter both ID and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'admin'");
        $stmt->bind_param("s", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit();
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "Admin ID not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Login</title>
<style>
  body {
    background: #1b2735;
    color: #e0e6f0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
  }
  .login-container {
    background: #273746;
    border-radius: 12px;
    padding: 3rem 3.5rem;
    width: 380px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
    text-align: center;
  }
  h2 {
    margin-bottom: 2rem;
    font-weight: 700;
    font-size: 2.2rem;
    letter-spacing: 1.5px;
    color: #f7f9fc;
  }
  form {
    display: flex;
    flex-direction: column;
    gap: 1.4rem;
  }
  label {
    font-weight: 600;
    font-size: 1rem;
    text-align: left;
    color: #bfc7d5;
  }
  input[type="text"],
  input[type="password"] {
    padding: 0.9rem 1rem;
    font-size: 1.1rem;
    border-radius: 8px;
    border: 2px solid #3a4a62;
    background-color: #2f3a4d;
    color: #e0e6f0;
    transition: border-color 0.3s ease, background-color 0.3s ease;
  }
  input[type="text"]::placeholder,
  input[type="password"]::placeholder {
    color: #8a99af;
  }
  input[type="text"]:focus,
  input[type="password"]:focus {
    outline: none;
    border-color: #4c8bf5;
    background-color: #3c4d6a;
  }
  button {
    margin-top: 1rem;
    padding: 1rem;
    background: #4c8bf5;
    color: white;
    font-weight: 700;
    font-size: 1.2rem;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    box-shadow: 0 4px 15px rgba(76, 139, 245, 0.6);
  }
  button:hover {
    background-color: #3a6ac9;
  }
  .message {
    color: #ff6b6b;
    font-weight: 700;
    margin-bottom: 1.2rem;
    text-align: center;
  }
  p.register {
    margin-top: 2rem;
    font-size: 0.95rem;
    color: #a3b1cc;
  }
  p.register a {
    color: #4c8bf5;
    font-weight: 700;
    text-decoration: none;
  }
  p.register a:hover {
    text-decoration: underline;
  }
  p.forgot-password {
    margin-top: 1rem;
  }
  p.forgot-password a {
    color: #f0ad4e;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
  }
  p.forgot-password a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>

<div class="login-container">
  <h2>Admin Login</h2>

  <?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <label for="admin_id">Admin ID:</label>
    <input type="text" name="admin_id" id="admin_id" required autofocus placeholder="Enter your admin ID" />

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required placeholder="Enter your password" />

    <button type="submit">Login</button>
  </form>

  <p class="forgot-password">
    <a href="forgot_password.php">Forgot Password?</a>
  </p>

  <p class="register">
    Don't have an account? <a href="admin_register.php">Register here</a>
  </p>
</div>

</body>
</html>
