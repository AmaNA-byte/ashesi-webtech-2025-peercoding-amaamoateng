<?php
  require_once 'auth.php';
  require_once 'db_connect.php';
  
  requireRole('student');
  
  $user = getCurrentUser();
  $page_title = "Mark My Attendance";
  
  // Handle attendance code submission
  if ($_POST && isset($_POST['attendance_code'])) {
    $code = trim($_POST['attendance_code']);
    
    // Find active session with this code (within last 3 hours)
    $sql = "SELECT s.*, c.course_name, c.course_code
            FROM sessions s
            JOIN courses c ON s.course_id = c.id
            JOIN student_courses sc ON s.course_id = sc.course_id
            WHERE sc.student_id = ?
            AND s.attendance_code = ?
            AND s.session_date = CURDATE()
            AND TIMESTAMPDIFF(HOUR, CONCAT(s.session_date, ' ', s.session_time), NOW()) <= 3";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $user['student_id'], $code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($session = mysqli_fetch_assoc($result)) {
      // Check if already marked
      $check_sql = "SELECT id FROM attendance WHERE student_id = ? AND session_id = ?";
      $check_stmt = mysqli_prepare($conn, $check_sql);
      mysqli_stmt_bind_param($check_stmt, "ii", $user['id'], $session['id']);
      mysqli_stmt_execute($check_stmt);
      $check_result = mysqli_stmt_get_result($check_stmt);
      
      if (mysqli_num_rows($check_result) > 0) {
        $error_message = "You have already marked attendance for this session.";
      } else {
        // Calculate if late (more than 15 minutes after session time)
        $session_datetime = strtotime($session['session_date'] . ' ' . $session['session_time']);
        $current_datetime = time();
        $minutes_late = ($current_datetime - $session_datetime) / 60;
        
        $status = ($minutes_late > 15) ? 'late' : 'present';
        
        // Mark attendance
        $insert_sql = "INSERT INTO attendance (student_id, session_id, status, marked_by) 
                      VALUES (?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "iisi", $user['id'], $session['id'], $status, $user['id']);
        
        if (mysqli_stmt_execute($insert_stmt)) {
          $success_message = "Attendance marked successfully as " . ucfirst($status) . "!";
          $marked_session = $session;
        } else {
          $error_message = "Error marking attendance. Please try again.";
        }
      }
    } else {
      $error_message = "Invalid or expired attendance code.";
    }
  }
  
  // Get today's sessions
  $today_sql = "SELECT s.*, c.course_code, c.course_name, a.status
                FROM sessions s
                JOIN courses c ON s.course_id = c.id
                JOIN student_courses sc ON s.course_id = sc.course_id
                LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
                WHERE sc.student_id = ? AND s.session_date = CURDATE()
                ORDER BY s.session_time";
  
  $today_stmt = mysqli_prepare($conn, $today_sql);
  mysqli_stmt_bind_param($today_stmt, "ii", $user['id'], $user['student_id']);
  mysqli_stmt_execute($today_stmt);
  $today_sessions = mysqli_stmt_get_result($today_stmt);
  
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
      <a href="student_mark_attendance.php" class="active">Mark Attendance</a>
      <a href="attendance_history.php">My Attendance</a>
      <a href="courses.php">My Courses</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2>Mark Attendance</h2>
      <p>Enter the attendance code provided by your instructor</p>
    </div>

    <?php if (isset($success_message)): ?>
      <div class="alert success">
        <?= htmlspecialchars($success_message) ?>
        <?php if (isset($marked_session)): ?>
          <p><strong>Course:</strong> <?= htmlspecialchars($marked_session['course_code']) ?></p>
          <p><strong>Time:</strong> <?= date('g:i A', strtotime($marked_session['session_time'])) ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
      <div class="alert error">
        <?= htmlspecialchars($error_message) ?>
      </div>
    <?php endif; ?>

    <!-- Attendance Code Form -->
    <div class="card">
      <h2>Enter Attendance Code</h2>
      <form method="POST" class="form">
        <div class="form-row">
          <label for="attendance_code">Attendance Code</label>
          <input type="text" 
                 id="attendance_code" 
                 name="attendance_code" 
                 placeholder="Enter 6-digit code" 
                 maxlength="6"
                 pattern="[A-Z0-9]{6}"
                 style="text-transform: uppercase;"
                 required>
          <small>Enter the 6-digit code displayed by your instructor</small>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn">Submit Attendance</button>
        </div>
      </form>
    </div>

    <!-- Today's Sessions -->
    <div class="card">
      <h2>Today's Sessions</h2>
      <?php if (mysqli_num_rows($today_sessions) > 0): ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Time</th>
                <th>Course</th>
                <th>Type</th>
                <th>Location</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($session = mysqli_fetch_assoc($today_sessions)): ?>
                <tr>
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
                    <?php if ($session['status']): ?>
                      <span class="attendance-status <?= $session['status'] ?>">
                        <?= ucfirst($session['status']) ?>
                      </span>
                    <?php else: ?>
                      <span class="attendance-status" style="background-color: #fef3c7; color: #92400e;">
                        Not Marked
                      </span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p>No sessions scheduled for today.</p>
      <?php endif; ?>
    </div>
  </main>

  <script>
    // Auto-uppercase attendance code
    document.getElementById('attendance_code').addEventListener('input', function(e) {
      e.target.value = e.target.value.toUpperCase();
    });
  </script>
</body>
</html>