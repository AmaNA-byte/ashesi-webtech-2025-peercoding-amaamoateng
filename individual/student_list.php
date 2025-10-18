<?php
  $page_title = "Student List";
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
    <?php
      require_once 'db_connect.php';

      // Get all students
      $sql = "SELECT id, name, email, program, created_at FROM students ORDER BY id DESC";
      $result = mysqli_query($conn, $sql);

      if (!$result) {
        $db_error = mysqli_error($conn);
      }
      
      // Get statistics
      $total_students = 0;
      $programs_count = [];
      
      if (!isset($db_error)) {
        $total_students = mysqli_num_rows($result);
        
        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) {
          $program = $row['program'];
          if (!isset($programs_count[$program])) {
            $programs_count[$program] = 0;
          }
          $programs_count[$program]++;
        }
        
        mysqli_data_seek($result, 0);
      }
    ?>

    <?php if (isset($db_error)): ?>
      <div class="alert error">
        <h3>Database Error</h3>
        <p><?= htmlspecialchars($db_error) ?></p>
      </div>
    <?php else: ?>
      <div class="welcome-banner">
        <h2>All Students</h2>
        <p>View and manage registered students</p>
      </div>

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
            <h3><?= count($programs_count) ?></h3>
            <p>Programs</p>
          </div>
        </div>
      </div>

      <div class="card">
        <?php if (mysqli_num_rows($result) === 0): ?>
          <p>No students registered yet.</p>
          <div class="quick-links">
            <a href="register.php" class="btn">Register First Student</a>
          </div>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Program</th>
                <th>Date Registered</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= htmlspecialchars($row['program']) ?></td>
                  <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <div class="quick-links">
          <a href="register.php" class="btn">Register New Student</a>
          <a href="index.php" class="btn outline">Back to Home</a>
        </div>
      </div>
    <?php endif; ?>

    <?php
      mysqli_close($conn);
    ?>
  </main>
</body>
</html>
