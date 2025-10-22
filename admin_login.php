<?php
require 'connect.php';
session_start();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = trim($_POST['username']);
  $pass = $_POST['password'];
  $stmt = $conn->prepare("SELECT id, username, password, fullname FROM admins WHERE username = ? OR email = ? LIMIT 1");
  $stmt->bind_param('ss', $user, $user);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    if (password_verify($pass, $row['password'])) {
      $_SESSION['admin_id'] = $row['id'];
      $_SESSION['admin_name'] = $row['fullname'] ?: $row['username'];
      $_SESSION['is_admin'] = true;
      header('Location: admin_dashboard.php'); exit;
    } else { $message = 'Invalid credentials.'; }
  } else { $message = 'No admin found.'; }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Login</title><link rel="stylesheet" href="styles.css"></head>
<style>
    body {
      background-image: url('nnn.jpg'); 
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
  <div class="card overlay">
    <h2>Admin Login</h2>
    <?php if ($message): ?><p class="msg"><?=htmlspecialchars($message)?></p><?php endif; ?>
    <form method="post">
      <label>Username or Email</label><input name="username" required>
      <label>Password</label><input type="password" name="password" required>
      <button type="submit">Login</button>
    </form>
    <p><a href="admin_register.php">Register as Admin</a></p>
  </div>
</body>
</html>
