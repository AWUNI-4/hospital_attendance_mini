<?php
require 'connect.php';
session_start();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $staff_id = trim($_POST['staff_id']);
  $password = $_POST['password'];
  $stmt = $conn->prepare("SELECT staff_id, password, firstname FROM staff WHERE staff_id = ? OR email = ? LIMIT 1");
  $stmt->bind_param('ss', $staff_id, $staff_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
      $_SESSION['staff_id'] = $row['staff_id'];
      $_SESSION['firstname'] = $row['firstname'];
      header('Location: mark_attendance.php'); exit;
    } else {
      $message = 'Invalid credentials.';
    }
  } else {
    $message = 'No user found.';
  }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Staff Login</title><link rel="stylesheet" href="styles.css"></head>
<style>
    body {
      background-image: url('ccc.jpg'); 
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 100vh;
    }
    .overlay {
      background: linear-gradient(rgba(255,255,255,0.8), rgba(255,255,255,0.8)), url('aaa.jpg') center/cover no-repeat;
      padding: 2rem;
      border-radius: .5rem;
      box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
      max-width: 520px;
      width: 100%;
    }
  </style>
<body>
  <div class="card">
    <h2>Staff Login</h2>
    <?php if ($message): ?><p class="msg"><?=htmlspecialchars($message)?></p><?php endif; ?>
    <form method="post">
      <label>Staff ID or Email</label><input name="staff_id" required>
      <label>Password</label><input type="password" name="password" required>
      <button type="submit">Login</button>
    </form>
    <p><a href="register.php">Register</a> | <a href="admin_login.php">Admin Login</a></p>
  </div>
</body>
</html>
