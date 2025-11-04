<?php
  $page_title = "Student Registration System";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?></title>
  <link rel="stylesheet" href="styles.css?v=2">
</head>
<body>
  <header class="topbar">
    <h1>Student Registration System</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="register.php">Register</a>
      <a href="student_list.php">Student List</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2>Welcome to Student Management</h2>
      <p>Manage your student registrations easily</p>
    </div>

    <?php
      require_once 'db_connect.php';

      // Get total students
      $sql = "SELECT COUNT(*) as total FROM students";
      $result = mysqli_query($conn, $sql);
      $total_students = 0;
      if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_students = $row['total'];
      }

      // Get program count
      $sql = "SELECT COUNT(DISTINCT program) as programs FROM students";
      $result = mysqli_query($conn, $sql);
      $total_programs = 0;
      if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_programs = $row['programs'];
      }

      mysqli_close($conn);
    ?>

    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
          <h3><?= $total_students ?></h3>
          <p>Total Students</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-info">
          <h3><?= $total_programs ?></h3>
          <p>Programs</p>
        </div>
      </div>
    </div>

    <div class="card">
      <h2>Quick Actions</h2>
      <p>Choose an option to get started</p>
      <div class="quick-links">
        <a href="register.php" class="btn">Register New Student</a>
        <a href="student_list.php" class="btn outline">View All Students</a>
      </div>
    </div>
  </main>
</body>
</html>
