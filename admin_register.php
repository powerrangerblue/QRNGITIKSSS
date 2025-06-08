<?php
session_start();
include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['admin_id'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$user_id || !$fullname || !$email || !$password || !$confirm_password) {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? OR email = ?");
        $stmt->bind_param("ss", $user_id, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Admin ID or email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';

            $stmt = $conn->prepare("INSERT INTO users (user_id, fullname, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssss", $user_id, $fullname, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $message = "Admin registration successful. You can now <a href='admin_login.php'>login</a>.";
            } else {
                $message = "Error during registration.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Registration</title>
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
  .register-container {
    background: #273746;
    border-radius: 10px;
    padding: 2rem 2.5rem;
    width: 320px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.5);
    text-align: center;
  }
  h2 {
    margin-bottom: 1.5rem;
    font-weight: 700;
    font-size: 1.6rem;
    letter-spacing: 1.2px;
    color: #f7f9fc;
  }
  form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  label {
    font-weight: 600;
    font-size: 0.9rem;
    text-align: left;
    color: #bfc7d5;
  }
  input[type="text"],
  input[type="email"],
  input[type="password"] {
    padding: 0.65rem 0.85rem;
    font-size: 1rem;
    border-radius: 6px;
    border: 2px solid #3a4a62;
    background-color: #2f3a4d;
    color: #e0e6f0;
    transition: border-color 0.3s ease, background-color 0.3s ease;
  }
  input[type="text"]::placeholder,
  input[type="email"]::placeholder,
  input[type="password"]::placeholder {
    color: #8a99af;
  }
  input[type="text"]:focus,
  input[type="email"]:focus,
  input[type="password"]:focus {
    outline: none;
    border-color: #4c8bf5;
    background-color: #3c4d6a;
  }
  button {
    margin-top: 1rem;
    padding: 0.85rem;
    background: #4c8bf5;
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    box-shadow: 0 3px 10px rgba(76, 139, 245, 0.6);
  }
  button:hover {
    background-color: #3a6ac9;
  }
  .message {
    color: #ff6b6b;
    font-weight: 700;
    margin-bottom: 1rem;
    text-align: center;
    font-size: 0.95rem;
  }
  p.login {
    margin-top: 1.5rem;
    font-size: 0.85rem;
    color: #a3b1cc;
  }
  p.login a {
    color: #4c8bf5;
    font-weight: 700;
    text-decoration: none;
  }
  p.login a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>

<div class="register-container">
  <h2>Admin Registration</h2>

  <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <label for="admin_id">Admin ID:</label>
    <input type="text" name="admin_id" id="admin_id" required autofocus placeholder="Enter your admin ID" />

    <label for="fullname">Full Name:</label>
    <input type="text" name="fullname" id="fullname" required placeholder="Your full name" />

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required placeholder="Your email address" />

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required placeholder="Choose a password" />

    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Re-enter your password" />

    <button type="submit">Register</button>
  </form>

  <p class="login">
    Already have an account? <a href="admin_login.php">Login here</a>
  </p>
</div>

</body>
</html>
