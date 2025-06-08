<?php
session_start();
include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$student_id || !$password) {
        $message = "Please enter both Student ID and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'student'");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Login success
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                header("Location: student_dashboard.php");
                exit();
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "Student ID not found or not registered as student.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Student Login</title>
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
  .login-container {
    background: #ffffff;
    border-radius: 16px;
    padding: 2.5rem 3rem;
    box-shadow: 0 8px 15px rgba(0, 77, 64, 0.2);
    width: 350px;
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
  label {
    font-size: 1.1rem;
    font-weight: 600;
    text-align: left;
    color: #004d40;
  }
  input[type="text"],
  input[type="password"] {
    padding: 0.9rem 1rem;
    font-size: 1.1rem;
    border-radius: 12px;
    border: 2px solid #b2dfdb;
    transition: border-color 0.3s ease;
  }
  input[type="text"]:focus,
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
    margin-bottom: 1rem;
    font-weight: 700;
  }
  .link-text {
    margin-top: 1rem;
    font-size: 1rem;
    color: #00796b;
  }
  .link-text a {
    color: #004d40;
    text-decoration: none;
    font-weight: 700;
  }
  .link-text a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>

<div class="login-container">
  <h2>Student Login</h2>

  <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <label for="student_id">Student ID:</label>
    <input type="text" name="student_id" id="student_id" required autofocus />

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required />

    <button type="submit">Login</button>

    <p class="link-text"><a href="student_forgot_password.php">Forgot Password?</a></p>
  </form>

  <p class="link-text">Don't have an account? <a href="student_register.php">Register here</a></p>
</div>

</body>
</html>
