<?php
require 'connect.php';
session_start();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $fullname = trim($_POST['fullname']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  if (!$username || !$email || !$password) { $message = 'Please fill required fields.'; }
  else {
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) { $message = 'Admin username or email already exists.'; }
    else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = $conn->prepare("INSERT INTO admins (username, fullname, email, password) VALUES (?, ?, ?, ?)");
      $ins->bind_param('ssss', $username, $fullname, $email, $hash);
      if ($ins->execute()) {
        $message = 'Admin registered successfully. You can now login.';
      } else {
        $message = 'Registration failed: ' . $conn->error;
      }
    }
  }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Registration</title><link rel="stylesheet" href="styles.css"></head>
<style>
  body {
    background-image: url('nnn.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
  }

  /* style the form card and let an overlay image sit on top of it */
  .card {
    position: relative;
    max-width: 520px;
    width: 100%;
    padding: 2rem;
    border-radius: .5rem;
    overflow: hidden;
    background: rgba(255,255,255,0.85); /* translucent so background shows through */
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
  }

  /* overlay image on the form card; adjust opacity/mix-blend-mode as needed */
  .card::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image: url('aaa.jpg');
    background-size: cover;
    background-position: center;
    opacity: 0.35; /* increase for stronger overlay */
    mix-blend-mode: overlay;
    pointer-events: none;
  }

  /* ensure form content sits above the overlay */
  .card * {
    position: relative;
    z-index: 1;
  }
</style>
<body>
  <div class="card">
    <h2>Admin Registration</h2>
    <?php if ($message): ?><p class="msg"><?=htmlspecialchars($message)?></p><?php endif; ?>
    <form method="post">
      <label>Username</label><input name="username" required>
      <label>Full name</label><input name="fullname">
      <label>Email</label><input type="email" name="email" required>
      <label>Password</label><input type="password" name="password" required>
      <button type="submit">Register as Admin</button>
    </form>
    <p><a href="admin_login.php">Already an admin? Login</a></p>
  </div>
</body>
</html>
