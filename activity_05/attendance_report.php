<?php
  require_once 'auth.php';
  require_once 'db_connect.php';
  
  requireRole('faculty');
  
  $user = getCurrentUser();
  $page_title = "Attendance Report";
  
  // Get filter parameters
  $course_filter = $_GET['course'] ?? '';
  $date_from = $_GET['date_from'] ?? '';
  $date_to = $_GET['date_to'] ?? '';
  $student_filter = $_GET['student'] ?? '';
  
  // Build query for attendance report
  $sql = "SELECT 
            s.session_date,
            s.session_time,
            c.course_code,
            c.course_name,
            s.session_type,
            s.location,
            st.name as student_name,
            st.email as student_email,
            a.status,
            a.notes,
            a.marked_at
          FROM sessions s
          JOIN courses c ON s.course_id = c.id
          JOIN student_courses sc ON s.course_id = sc.course_id
          JOIN students st ON sc.student_id = st.id
          LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = st.id";
  
  $where_conditions = [];
  $params = [];
  $param_types = "";
  
  if ($course_filter) {
    $where_conditions[] = "c.id = ?";
    $params[] = $course_filter;
    $param_types .= "i";
  }
  
  if ($date_from) {
    $where_conditions[] = "s.session_date >= ?";
    $params[] = $date_from;
    $param_types .= "s";
  }
  
  if ($date_to) {
    $where_conditions[] = "s.session_date <= ?";
    $params[] = $date_to;
    $param_types .= "s";
  }
  
  if ($student_filter) {
    $where_conditions[] = "st.name LIKE ?";
    $params[] = "%$student_filter%";
    $param_types .= "s";
  }
  
  if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
  }
  
  $sql .= " ORDER BY s.session_date DESC, s.session_time DESC, c.course_code, st.name";
  
  $stmt = mysqli_prepare($conn, $sql);
  if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
  }
  mysqli_stmt_execute($stmt);
  $attendance_records = mysqli_stmt_get_result($stmt);
  
  // Get courses for filter dropdown
  $courses_sql = "SELECT * FROM courses ORDER BY course_code";
  $courses_result = mysqli_query($conn, $courses_sql);
  
  // Calculate statistics
  $stats = [
    'total_records' => 0,
    'present' => 0,
    'absent' => 0,
    'late' => 0,
    'excused' => 0,
    'not_marked' => 0
  ];
  
  mysqli_data_seek($attendance_records, 0);
  while ($record = mysqli_fetch_assoc($attendance_records)) {
    $stats['total_records']++;
    if ($record['status']) {
      $stats[$record['status']]++;
    } else {
      $stats['not_marked']++;
    }
  }
  mysqli_data_seek($attendance_records, 0);
  
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
      <a href="attendance_report.php" class="active">Attendance Report</a>
      <a href="students.php">Students</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2>Attendance Report</h2>
      <p>View and analyze student attendance data</p>
    </div>

    <!-- Statistics -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-info">
          <h3><?= $stats['total_records'] ?></h3>
          <p>Total Records</p>
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
      
      <div class="stat-card">
        <div class="stat-icon">❓</div>
        <div class="stat-info">
          <h3><?= $stats['not_marked'] ?></h3>
          <p>Not Marked</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card">
      <h2>Filter Report</h2>
      <form method="GET" class="filter-form">
        <div class="form-row">
          <label for="course">Course</label>
          <select id="course" name="course">
            <option value="">All Courses</option>
            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
              <option value="<?= $course['id'] ?>" 
                      <?= $course_filter == $course['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <div class="form-row">
          <label for="student">Student Name</label>
          <input type="text" id="student" name="student" 
                 value="<?= htmlspecialchars($student_filter) ?>" 
                 placeholder="Search by student name">
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
        <a href="attendance_report.php" class="btn outline">Clear Filters</a>
      </form>
    </div>

    <!-- Attendance Report Table -->
    <div class="card">
      <h2>Attendance Records</h2>
      <?php if (mysqli_num_rows($attendance_records) > 0): ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Course</th>
                <th>Type</th>
                <th>Location</th>
                <th>Student</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Marked At</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($record = mysqli_fetch_assoc($attendance_records)): ?>
                <tr>
                  <td><?= date('M d, Y', strtotime($record['session_date'])) ?></td>
                  <td><?= date('g:i A', strtotime($record['session_time'])) ?></td>
                  <td>
                    <strong><?= htmlspecialchars($record['course_code']) ?></strong><br>
                    <small><?= htmlspecialchars($record['course_name']) ?></small>
                  </td>
                  <td>
                    <span class="session-type <?= $record['session_type'] ?>">
                      <?= ucfirst($record['session_type']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($record['location']) ?></td>
                  <td>
                    <strong><?= htmlspecialchars($record['student_name']) ?></strong><br>
                    <small><?= htmlspecialchars($record['student_email']) ?></small>
                  </td>
                  <td>
                    <span class="attendance-status <?= $record['status'] ?>">
                      <?= ucfirst($record['status'] ?: 'Not Marked') ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($record['notes'] ?: '-') ?></td>
                  <td>
                    <?php if ($record['marked_at']): ?>
                      <?= date('M d, Y g:i A', strtotime($record['marked_at'])) ?>
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
