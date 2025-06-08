<?php
session_start();
include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$student_id || !$fullname || !$email || !$password || !$confirm_password) {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Check if user_id or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? OR email = ?");
        $stmt->bind_param("ss", $student_id, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Student ID or email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';

            $stmt = $conn->prepare("INSERT INTO users (user_id, fullname, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $student_id, $fullname, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $message = "Registration successful. You can now <a href='student_login.php'>login</a>.";
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
<title>Student Registration</title>
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
  .register-container {
    background: #ffffff;
    border-radius: 16px;
    padding: 2.5rem 3rem;
    box-shadow: 0 8px 15px rgba(0, 77, 64, 0.2);
    width: 380px;
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
    text-align: left;
  }
  label {
    font-size: 1.1rem;
    font-weight: 600;
    color: #004d40;
  }
  input[type="text"],
  input[type="email"],
  input[type="password"] {
    padding: 0.9rem 1rem;
    font-size: 1.1rem;
    border-radius: 12px;
    border: 2px solid #b2dfdb;
    transition: border-color 0.3s ease;
    width: 100%;
    box-sizing: border-box;
  }
  input[type="text"]:focus,
  input[type="email"]:focus,
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
    text-align: center;
  }
  p.login-link {
    margin-top: 1.5rem;
    font-size: 1rem;
    color: #00796b;
    text-align: center;
  }
  p.login-link a {
    color: #004d40;
    text-decoration: none;
    font-weight: 700;
  }
  p.login-link a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>

<div class="register-container">
  <h2>Student Registration</h2>

  <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <label for="student_id">Student ID:</label>
    <input type="text" name="student_id" id="student_id" required autofocus />

    <label for="fullname">Full Name:</label>
    <input type="text" name="fullname" id="fullname" required />

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required />

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required />

    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required />

    <button type="submit">Register</button>
  </form>

  <p class="login-link">Already have an account? <a href="student_login.php">Login here</a></p>
</div>

</body>
</html>
