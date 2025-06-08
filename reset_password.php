<?php
session_start();
include 'db.php';

$token = $_GET['token'] ?? '';
$message = '';
$show_form = false;

if (!$token) {
    $message = "Invalid or missing reset token.";
} else {
    // Verify token validity without expiry check
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $message = "Invalid reset token.";
    } else {
        // Fetch user info here
        $user = $result->fetch_assoc();
        $show_form = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!$password || !$confirm) {
                $message = "Please fill in all fields.";
            } elseif ($password !== $confirm) {
                $message = "Passwords do not match.";
            } else {
                // Optional: add password strength validation here

                $hashed = password_hash($password, PASSWORD_DEFAULT);

                // Update password, clear token (no expiry field anymore)
                $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE user_id = ?");
                $update->bind_param("ss", $hashed, $user['user_id']);

                if ($update->execute()) {
                    $message = "Password has been reset successfully. <a href='admin_login.php'>Login</a>";
                    $show_form = false; // Don't show form after success
                } else {
                    $message = "Failed to reset password. Please try again.";
                }
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
            background-color: #1b2735;
            color: #e0e6f0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-box {
            background: #273746;
            padding: 2rem 3rem;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
            width: 360px;
        }
        h2 {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input {
            margin-bottom: 1rem;
            padding: 0.8rem;
            border: 2px solid #3a4a62;
            border-radius: 6px;
            background: #2f3a4d;
            color: white;
            font-size: 1rem;
        }
        button {
            padding: 0.9rem;
            background-color: #4c8bf5;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }
        .message {
            margin-bottom: 1rem;
            color: #ffd166;
            font-weight: bold;
            text-align: center;
            word-break: break-word;
        }
        a {
            color: #4c8bf5;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Reset Password</h2>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($show_form): ?>
        <form method="POST" novalidate>
            <input type="password" name="password" placeholder="New Password" required minlength="6" />
            <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="6" />
            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
