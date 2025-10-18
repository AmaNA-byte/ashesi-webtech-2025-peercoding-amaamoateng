<?php
  $page_title = "Unauthorized Access";
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
        <h1>Access Denied</h1>
        <p>You don't have permission to access this page</p>
      </div>
      
      <div class="alert error">
        <h3>Unauthorized Access</h3>
        <p>You don't have the required permissions to view this page. Please contact your administrator if you believe this is an error.</p>
      </div>
      
      <div class="quick-links">
        <a href="dashboard.php" class="btn">Go to Dashboard</a>
        <a href="logout.php" class="btn outline">Logout</a>
      </div>
    </div>
  </div>
</body>
</html>
