<?php
require 'connect.php';
session_start();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $staff_id = trim($_POST['staff_id']);
  $firstname = trim($_POST['firstname']);
  $lastname = trim($_POST['lastname']);
  $email = trim($_POST['email']);
  $department = trim($_POST['department']);
  $phone = trim($_POST['phone']);
  $password = $_POST['password'];

  if (!$staff_id || !$firstname || !$lastname || !$email || !$password) {
    $message = 'Please fill required fields.';
  } else {
    $stmt = $conn->prepare("SELECT id FROM staff WHERE staff_id = ? OR email = ?");
    $stmt->bind_param('ss', $staff_id, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $message = 'Staff with that ID or email already exists.';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = $conn->prepare("INSERT INTO staff (staff_id, firstname, lastname, email, password, department, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $ins->bind_param('sssssss', $staff_id, $firstname, $lastname, $email, $hash, $department, $phone);
      if ($ins->execute()) {
        $message = 'Registered successfully. You can now login.';
      } else {
        $message = 'Registration failed: ' . $conn->error;
      }
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Staff Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
</head>
<body>
  <div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="overlay">
      <h2 class="mb-3">Staff Registration</h2>
      <?php if ($message): ?><div class="alert alert-info"><?=htmlspecialchars($message)?></div><?php endif; ?>
      <form method="post" class="row g-3">
        <div class="col-12">
          <label class="form-label">Staff ID (unique)</label>
          <input name="staff_id" required class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">First name</label>
          <input name="firstname" required class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Last name</label>
          <input name="lastname" required class="form-control">
        </div>
        <div class="col-12">
          <label class="form-label">Email</label>
          <input type="email" name="email" required class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Department</label>
          <input name="department" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input name="phone" class="form-control">
        </div>
        <div class="col-12">
          <label class="form-label">Password</label>
          <input type="password" name="password" required class="form-control">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary w-100">Register</button>
        </div>
      </form>
      <p class="mt-3 mb-0"><a href="login.php">Already registered? Login</a></p>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
