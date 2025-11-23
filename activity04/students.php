<?php
  require_once 'auth.php';
  require_once 'db_connect.php';
  
  requireRole('faculty');
  
  $user = getCurrentUser();
  $page_title = "Manage Students";
  
  // Get all students with their course enrollments
  $sql = "SELECT s.*, 
            GROUP_CONCAT(DISTINCT c.course_code ORDER BY c.course_code SEPARATOR ', ') as enrolled_courses,
            COUNT(DISTINCT sc.course_id) as course_count
          FROM students s
          LEFT JOIN student_courses sc ON s.id = sc.student_id
          LEFT JOIN courses c ON sc.course_id = c.id
          GROUP BY s.id
          ORDER BY s.name";
  
  $result = mysqli_query($conn, $sql);
  
  // Get statistics
  $total_students = mysqli_num_rows($result);
  
  // Get course statistics
  $courses_sql = "SELECT c.course_code, c.course_name, COUNT(sc.student_id) as enrollment_count
                  FROM courses c
                  LEFT JOIN student_courses sc ON c.id = sc.course_id
                  GROUP BY c.id
                  ORDER BY c.course_code";
  $courses_result = mysqli_query($conn, $courses_sql);
  
  mysqli_close($conn);
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
  <header class="topbar">
    <h1>Student Management System</h1>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="manage_sessions.php">Manage Sessions</a>
      <a href="attendance_report.php">Attendance Report</a>
      <a href="students.php" class="active">Students</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2>Student Management</h2>
      <p>View and manage student information and enrollments</p>
    </div>

    <!-- Statistics -->
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
          <h3><?= mysqli_num_rows($courses_result) ?></h3>
          <p>Total Courses</p>
        </div>
      </div>
    </div>

    <!-- Students List -->
    <div class="card">
      <h2>All Students</h2>
      <?php if ($total_students > 0): ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Program</th>
                <th>Courses Enrolled</th>
                <th>Course Count</th>
                <th>Date Registered</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($student = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?= $student['id'] ?></td>
                  <td>
                    <strong><?= htmlspecialchars($student['name']) ?></strong>
                  </td>
                  <td><?= htmlspecialchars($student['email']) ?></td>
                  <td><?= htmlspecialchars($student['program']) ?></td>
                  <td>
                    <?php if ($student['enrolled_courses']): ?>
                      <?= htmlspecialchars($student['enrolled_courses']) ?>
                    <?php else: ?>
                      <em>No courses enrolled</em>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge"><?= $student['course_count'] ?></span>
                  </td>
                  <td><?= date('M d, Y', strtotime($student['created_at'])) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p>No students found.</p>
      <?php endif; ?>
    </div>

    <!-- Course Enrollments -->
    <div class="card">
      <h2>Course Enrollments</h2>
      <?php if (mysqli_num_rows($courses_result) > 0): ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Enrolled Students</th>
                <th>Enrollment Rate</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($course['course_code']) ?></strong>
                  </td>
                  <td><?= htmlspecialchars($course['course_name']) ?></td>
                  <td>
                    <span class="badge"><?= $course['enrollment_count'] ?></span>
                  </td>
                  <td>
                    <?php 
                      $rate = $total_students > 0 ? ($course['enrollment_count'] / $total_students) * 100 : 0;
                      echo number_format($rate, 1) . '%';
                    ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p>No courses found.</p>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
