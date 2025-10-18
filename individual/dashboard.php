<?php
  require_once 'auth.php';
  require_once 'db_connect.php';
  
  requireLogin();
  
  $user = getCurrentUser();
  $page_title = "Dashboard - Student Management System";
  
  // Get attendance statistics for students
  $attendance_stats = [];
  if ($user['role'] === 'student' && $user['student_id']) {
    $sql = "SELECT 
              COUNT(*) as total_sessions,
              SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
              SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
              SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
              SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_count
            FROM sessions s
            JOIN student_courses sc ON s.course_id = sc.course_id
            LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
            WHERE sc.student_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user['student_id'], $user['student_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $attendance_stats = mysqli_fetch_assoc($result);
    
    // Get recent sessions
    $sql = "SELECT s.*, c.course_name, c.course_code, a.status, a.notes
            FROM sessions s
            JOIN courses c ON s.course_id = c.id
            JOIN student_courses sc ON s.course_id = sc.course_id
            LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
            WHERE sc.student_id = ?
            ORDER BY s.session_date DESC, s.session_time DESC
            LIMIT 10";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user['student_id'], $user['student_id']);
    mysqli_stmt_execute($stmt);
    $recent_sessions = mysqli_stmt_get_result($stmt);
  }
  
  // Get overall statistics for admin/faculty
  $overall_stats = [];
  if ($user['role'] === 'admin' || $user['role'] === 'faculty') {
    $sql = "SELECT 
              COUNT(DISTINCT s.id) as total_sessions,
              COUNT(DISTINCT st.id) as total_students,
              COUNT(DISTINCT c.id) as total_courses
            FROM sessions s
            CROSS JOIN students st
            CROSS JOIN courses c";
    
    $result = mysqli_query($conn, $sql);
    $overall_stats = mysqli_fetch_assoc($result);
  }
  
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
      <?php if ($user['role'] === 'student'): ?>
        <a href="attendance_history.php">My Attendance</a>
        <a href="courses.php">My Courses</a>
      <?php endif; ?>
      <?php if ($user['role'] === 'faculty' || $user['role'] === 'admin'): ?>
        <a href="manage_sessions.php">Manage Sessions</a>
        <a href="attendance_report.php">Attendance Report</a>
        <a href="students.php">Students</a>
      <?php endif; ?>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2>Welcome, <?= htmlspecialchars($user['student_name'] ?: $user['username']) ?>!</h2>
      <p>Role: <?= ucfirst($user['role']) ?></p>
    </div>

    <?php if ($user['role'] === 'student'): ?>
      <!-- Student Dashboard -->
      <div class="stats-container">
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-info">
            <h3><?= $attendance_stats['total_sessions'] ?? 0 ?></h3>
            <p>Total Sessions</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-info">
            <h3><?= $attendance_stats['present_count'] ?? 0 ?></h3>
            <p>Present</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">❌</div>
          <div class="stat-info">
            <h3><?= $attendance_stats['absent_count'] ?? 0 ?></h3>
            <p>Absent</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">⏰</div>
          <div class="stat-info">
            <h3><?= $attendance_stats['late_count'] ?? 0 ?></h3>
            <p>Late</p>
          </div>
        </div>
      </div>

      <div class="card">
        <h2>Recent Sessions</h2>
        <?php if (mysqli_num_rows($recent_sessions) > 0): ?>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Course</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($session = mysqli_fetch_assoc($recent_sessions)): ?>
                  <tr>
                    <td><?= date('M d, Y', strtotime($session['session_date'])) ?></td>
                    <td><?= date('g:i A', strtotime($session['session_time'])) ?></td>
                    <td><?= htmlspecialchars($session['course_code']) ?></td>
                    <td>
                      <span class="session-type <?= $session['session_type'] ?>">
                        <?= ucfirst($session['session_type']) ?>
                      </span>
                    </td>
                    <td>
                      <span class="attendance-status <?= $session['status'] ?>">
                        <?= ucfirst($session['status'] ?: 'Not Marked') ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($session['notes'] ?: '-') ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p>No sessions found.</p>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <!-- Admin/Faculty Dashboard -->
      <div class="stats-container">
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-info">
            <h3><?= $overall_stats['total_students'] ?? 0 ?></h3>
            <p>Total Students</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">📚</div>
          <div class="stat-info">
            <h3><?= $overall_stats['total_courses'] ?? 0 ?></h3>
            <p>Total Courses</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-info">
            <h3><?= $overall_stats['total_sessions'] ?? 0 ?></h3>
            <p>Total Sessions</p>
          </div>
        </div>
      </div>

      <div class="card">
        <h2>Quick Actions</h2>
        <div class="quick-links">
          <a href="manage_sessions.php" class="btn">Manage Sessions</a>
          <a href="attendance_report.php" class="btn outline">View Reports</a>
          <a href="students.php" class="btn outline">Manage Students</a>
        </div>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
