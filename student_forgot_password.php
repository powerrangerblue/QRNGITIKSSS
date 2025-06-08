<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');

    if (empty($identifier)) {
        $message = "Please enter your Student ID or Email.";
    } else {
        // Lookup user by student ID or email
        $stmt = $conn->prepare("SELECT * FROM users WHERE (user_id = ? OR email = ?) AND role = 'student'");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(32));

            // Save token
            $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE user_id = ?");
            $stmt->bind_param("ss", $token, $user['user_id']);
            $stmt->execute();

            $reset_link = "http://localhost/qr_attendance-main/student_reset_password.php?token=$token";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'canitanjasper016@gmail.com';
                $mail->Password   = 'idcbxqumsmttdbtq';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('canitanjasper016@gmail.com', 'QR Attendance System');
                $mail->addAddress($user['email'], $user['fullname']);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "
                    <h3>Hello {$user['fullname']},</h3>
                    <p>You requested to reset your password. Click the button below to reset it:</p>
                    <p><a href='$reset_link' style='padding: 10px 20px; background: #4c8bf5; color: white; text-decoration: none;'>Reset Password</a></p>
                    <p>If you did not request this, please ignore this email.</p>
                    <br><p>Regards,<br>QR Attendance System</p>
                ";

                $mail->send();
                $message = "A password reset link has been sent to your email.";
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Student ID or Email not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Forgot Password</title>
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
    input[type="text"] {
      padding: 0.9rem 1rem;
      font-size: 1.1rem;
      border-radius: 12px;
      border: 2px solid #b2dfdb;
      transition: border-color 0.3s ease;
    }
    input[type="text"]:focus {
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
    .link-text {
      margin-top: 1.5rem;
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

  <div class="container">
    <h2>Forgot Password</h2>
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <input type="text" name="identifier" placeholder="Enter Student ID or Email" required />
      <button type="submit">Send Reset Link</button>
    </form>

    <p class="link-text"><a href="student_login.php">Back to Login</a></p>
  </div>

</body>
</html>
