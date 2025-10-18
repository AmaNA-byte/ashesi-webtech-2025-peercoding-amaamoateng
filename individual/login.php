<?php
  require_once 'auth.php';
  
  // Redirect if already logged in
  if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
  }
  
  $error = '';
  
  if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
      $error = 'Please enter both username and password.';
    } else {
      if (login($username, $password)) {
        header('Location: dashboard.php');
        exit();
      } else {
        $error = 'Invalid username or password.';
      }
    }
  }
  
  $page_title = "Login - Student Management System";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?></title>
  <link rel="stylesheet" href="styles.css?v=3">
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>Student Management System</h1>
        <p>Attendance Tracking & Course Management</p>
      </div>
      
      <?php if ($error): ?>
        <div class="alert error">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" class="login-form">
        <div class="form-row">
          <label for="username">Username or Email</label>
          <input type="text" id="username" name="username" 
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                 placeholder="Enter username or email" required>
        </div>
        
        <div class="form-row">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" 
                 placeholder="Enter password" required>
        </div>
        
        <button type="submit" class="btn login-btn">Login</button>
      </form>
      
      <div class="login-footer">
        <p><strong>Demo Accounts:</strong></p>
        <div class="demo-accounts">
          <div class="demo-account">
            <strong>Student:</strong> john.doe / password123
          </div>
          <div class="demo-account">
            <strong>Faculty:</strong> faculty1 / password123
          </div>
          <div class="demo-account">
            <strong>Admin:</strong> admin / password123
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
