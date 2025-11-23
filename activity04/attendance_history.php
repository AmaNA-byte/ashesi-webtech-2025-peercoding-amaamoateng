<?php
  require_once 'auth.php';
  require_once 'db_connect.php';
  
  requireRole('student');
  
  $user = getCurrentUser();
  $page_title = "My Attendance History";
  
  // Get filter parameters
  $course_filter = $_GET['course'] ?? '';
  $status_filter = $_GET['status'] ?? '';
  $date_from = $_GET['date_from'] ?? '';
  $date_to = $_GET['date_to'] ?? '';
  
  // Build query
  $sql = "SELECT s.*, c.course_name, c.course_code, a.status, a.notes, a.marked_at
          FROM sessions s
          JOIN courses c ON s.course_id = c.id
          JOIN student_courses sc ON s.course_id = sc.course_id
          LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
          WHERE sc.student_id = ?";
  
  $params = [$user['student_id'], $user['student_id']];
  $param_types = "ii";
  
  if ($course_filter) {
    $sql .= " AND c.id = ?";
    $params[] = $course_filter;
    $param_types .= "i";
  }
  
  if ($status_filter) {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
  }
  
  if ($date_from) {
    $sql .= " AND s.session_date >= ?";
    $params[] = $date_from;
    $param_types .= "s";
  }
  
  if ($date_to) {
    $sql .= " AND s.session_date <= ?";
    $params[] = $date_to;
    $param_types .= "s";
  }
  
  $sql .= " ORDER BY s.session_date DESC, s.session_time DESC";
  
  $stmt = mysqli_prepare($conn, $sql);
  if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
  }
  mysqli_stmt_execute($stmt);
  $sessions = mysqli_stmt_get_result($stmt);
  
  // Get courses for filter dropdown
  $courses_sql = "SELECT DISTINCT c.id, c.course_code, c.course_name
                  FROM courses c
                  JOIN student_courses sc ON c.id = sc.course_id
                  WHERE sc.student_id = ?
                  ORDER BY c.course_code";
  $courses_stmt = mysqli_prepare($conn, $courses_sql);
  mysqli_stmt_bind_param($courses_stmt, "i", $user['student_id']);
  mysqli_stmt_execute($courses_stmt);
  $courses = mysqli_stmt_get_result($courses_stmt);
  
  // Calculate statistics
  $stats = [
    'total' => 0,
    'present' => 0,
    'absent' => 0,
    'late' => 0,
    'excused' => 0
  ];
  
  mysqli_data_seek($sessions, 0);
  while ($session = mysqli_fetch_assoc($sessions)) {
    $stats['total']++;
    if ($session['status']) {
      $stats[$session['status']]++;
    }
  }
  mysqli_data_seek($sessions, 0);
  
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
      <a href="attendance_history.php" class="active">My Attendance</a>
      <a href="courses.php">My Courses</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2>My Attendance History</h2>
      <p>Track your attendance across all courses</p>
    </div>

    <!-- Statistics -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-info">
          <h3><?= $stats['total'] ?></h3>
          <p>Total Sessions</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <h3><?= $stats['present'] ?></h3>
          <p>Present</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">❌</div>
        <div class="stat-info">
          <h3><?= $stats['absent'] ?></h3>
          <p>Absent</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">⏰</div>
        <div class="stat-info">
          <h3><?= $stats['late'] ?></h3>
          <p>Late</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card">
      <h2>Filter Results</h2>
      <form method="GET" class="filter-form">
        <div class="form-row">
          <label for="course">Course</label>
          <select id="course" name="course">
            <option value="">All Courses</option>
            <?php while ($course = mysqli_fetch_assoc($courses)): ?>
              <option value="<?= $course['id'] ?>" 
                      <?= $course_filter == $course['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <div class="form-row">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="">All Statuses</option>
            <option value="present" <?= $status_filter === 'present' ? 'selected' : '' ?>>Present</option>
            <option value="absent" <?= $status_filter === 'absent' ? 'selected' : '' ?>>Absent</option>
            <option value="late" <?= $status_filter === 'late' ? 'selected' : '' ?>>Late</option>
            <option value="excused" <?= $status_filter === 'excused' ? 'selected' : '' ?>>Excused</option>
          </select>
        </div>
        
        <div class="form-row">
          <label for="date_from">From Date</label>
          <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
        </div>
        
        <div class="form-row">
          <label for="date_to">To Date</label>
          <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
        </div>
        
        <button type="submit" class="btn">Apply Filters</button>
        <a href="attendance_history.php" class="btn outline">Clear Filters</a>
      </form>
    </div>

    <!-- Attendance Table -->
    <div class="card">
      <h2>Attendance Records</h2>
      <?php if (mysqli_num_rows($sessions) > 0): ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Course</th>
                <th>Type</th>
                <th>Location</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Marked At</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($session = mysqli_fetch_assoc($sessions)): ?>
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
                  <td>
                    <span class="attendance-status <?= $session['status'] ?>">
                      <?= ucfirst($session['status'] ?: 'Not Marked') ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($session['notes'] ?: '-') ?></td>
                  <td>
                    <?php if ($session['marked_at']): ?>
                      <?= date('M d, Y g:i A', strtotime($session['marked_at'])) ?>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p>No attendance records found matching your criteria.</p>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
