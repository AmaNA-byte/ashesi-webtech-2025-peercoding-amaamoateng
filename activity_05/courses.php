<?php
  require_once 'auth.php';
  require_once 'db_connect.php';
  
  requireRole('student');
  
  $user = getCurrentUser();
  $page_title = "My Courses";
  
  // Get student's enrolled courses
  $sql = "SELECT c.*, sc.enrolled_at
          FROM courses c
          JOIN student_courses sc ON c.id = sc.course_id
          WHERE sc.student_id = ?
          ORDER BY c.course_code";
  
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $user['student_id']);
  mysqli_stmt_execute($stmt);
  $courses = mysqli_stmt_get_result($stmt);
  
  // Get upcoming sessions for enrolled courses
  $sessions_sql = "SELECT s.*, c.course_code, c.course_name
                   FROM sessions s
                   JOIN courses c ON s.course_id = c.id
                   JOIN student_courses sc ON s.course_id = sc.course_id
                   WHERE sc.student_id = ? AND s.session_date >= CURDATE()
                   ORDER BY s.session_date ASC, s.session_time ASC
                   LIMIT 10";
  
  $sessions_stmt = mysqli_prepare($conn, $sessions_sql);
  mysqli_stmt_bind_param($sessions_stmt, "i", $user['student_id']);
  mysqli_stmt_execute($sessions_stmt);
  $upcoming_sessions = mysqli_stmt_get_result($sessions_stmt);
  
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
      <a href="attendance_history.php">My Attendance</a>
      <a href="courses.php" class="active">My Courses</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2>My Courses</h2>
      <p>View your enrolled courses and upcoming sessions</p>
    </div>

    <!-- Enrolled Courses -->
    <div class="card">
      <h2>Enrolled Courses</h2>
      <?php if (mysqli_num_rows($courses) > 0): ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Description</th>
                <th>Credits</th>
                <th>Enrolled Date</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($course['course_code']) ?></strong>
                  </td>
                  <td><?= htmlspecialchars($course['course_name']) ?></td>
                  <td><?= htmlspecialchars($course['description'] ?: 'No description available') ?></td>
                  <td>
                    <span class="badge"><?= $course['credits'] ?></span>
                  </td>
                  <td><?= date('M d, Y', strtotime($course['enrolled_at'])) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p>You are not enrolled in any courses yet.</p>
      <?php endif; ?>
    </div>

    <!-- Upcoming Sessions -->
    <div class="card">
      <h2>Upcoming Sessions</h2>
      <?php if (mysqli_num_rows($upcoming_sessions) > 0): ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Course</th>
                <th>Type</th>
                <th>Location</th>
                <th>Notes</th>
                <th>Materials</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($session = mysqli_fetch_assoc($upcoming_sessions)): ?>
                <tr>
                  <td><?= date('M d, Y', strtotime($session['session_date'])) ?></td>
                  <td><?= date('g:i A', strtotime($session['session_time'])) ?></td>
                  <td>
                    <strong><?= htmlspecialchars($session['course_code']) ?></strong><br>
                    <small><?= htmlspecialchars($session['course_name']) ?></small>
                  </td>
                  <td>
                    <span class="session-type <?= $session['session_type'] ?>">
                      <?= ucfirst($session['session_type']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($session['location']) ?></td>
                  <td><?= htmlspecialchars($session['instructor_notes'] ?: '-') ?></td>
                  <td><?= htmlspecialchars($session['materials_required'] ?: '-') ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p>No upcoming sessions found.</p>
      <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="card">
      <h2>Quick Actions</h2>
      <div class="quick-links">
        <a href="attendance_history.php" class="btn">View Attendance History</a>
        <a href="dashboard.php" class="btn outline">Back to Dashboard</a>
      </div>
    </div>
  </main>
</body>
</html>
