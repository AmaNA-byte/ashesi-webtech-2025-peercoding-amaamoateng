<?php
  require_once 'auth.php';
  require_once 'db_connect.php';
  
  requireRole('faculty');
  
  $user = getCurrentUser();
  $session_id = $_GET['session_id'] ?? '';
  
  if (!$session_id) {
    header('Location: manage_sessions.php');
    exit();
  }
  
  // Generate random 6-character code
  function generateAttendanceCode() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
      $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
  }
  
  // Check if code already exists, if not generate new one
  $check_sql = "SELECT attendance_code FROM sessions WHERE id = ?";
  $check_stmt = mysqli_prepare($conn, $check_sql);
  mysqli_stmt_bind_param($check_stmt, "i", $session_id);
  mysqli_stmt_execute($check_stmt);
  $check_result = mysqli_stmt_get_result($check_stmt);
  $session_data = mysqli_fetch_assoc($check_result);
  
  if (!$session_data['attendance_code']) {
    $code = generateAttendanceCode();
    $update_sql = "UPDATE sessions SET attendance_code = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "si", $code, $session_id);
    mysqli_stmt_execute($update_stmt);
  } else {
    $code = $session_data['attendance_code'];
  }
  
  // Get session details
  $session_sql = "SELECT s.*, c.course_code, c.course_name 
                  FROM sessions s 
                  JOIN courses c ON s.course_id = c.id 
                  WHERE s.id = ?";
  $session_stmt = mysqli_prepare($conn, $session_sql);
  mysqli_stmt_bind_param($session_stmt, "i", $session_id);
  mysqli_stmt_execute($session_stmt);
  $session_result = mysqli_stmt_get_result($session_stmt);
  $session = mysqli_fetch_assoc($session_result);
  
  mysqli_close($conn);
  
  $page_title = "Attendance Code";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?></title>
  <link rel="stylesheet" href="styles.css?v=3">
  <style>
    .code-display {
      text-align: center;
      padding: 3rem;
      background: linear-gradient(135deg, #884243, #6d3435);
      color: white;
      border-radius: 15px;
      margin: 2rem 0;
    }
    
    .code-display h1 {
      font-size: 5rem;
      letter-spacing: 1rem;
      margin: 1rem 0;
      font-family: 'Courier New', monospace;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .code-info {
      font-size: 1.2rem;
      margin-top: 1rem;
    }
    
    .qr-placeholder {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      margin: 2rem auto;
      max-width: 300px;
    }
    
    .timer {
      font-size: 1.5rem;
      font-weight: bold;
      margin-top: 1rem;
      padding: 1rem;
      background: rgba(255,255,255,0.2);
      border-radius: 10px;
    }
  </style>
</head>
<body>
  <header class="topbar">
    <h1>Student Management System</h1>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="manage_sessions.php">Manage Sessions</a>
      <a href="mark_attendance.php?session_id=<?= $session_id ?>">Mark Attendance</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2><?= htmlspecialchars($session['course_code'] . ' - ' . $session['course_name']) ?></h2>
      <p><?= date('l, F j, Y', strtotime($session['session_date'])) ?> at <?= date('g:i A', strtotime($session['session_time'])) ?></p>
      <p>Location: <?= htmlspecialchars($session['location']) ?></p>
    </div>

    <div class="card">
      <div class="code-display">
        <p class="code-info">Attendance Code</p>
        <h1><?= $code ?></h1>
        <p class="code-info">Students should enter this code to mark their attendance</p>
        <div class="timer" id="timer">Valid for: 3:00:00</div>
      </div>

      <div style="text-align: center;">
        <p><strong>Instructions for Students:</strong></p>
        <ol style="text-align: left; max-width: 600px; margin: 1rem auto;">
          <li>Go to "Mark Attendance" in your student portal</li>
          <li>Enter the 6-digit code shown above</li>
          <li>Submit to mark your attendance</li>
          <li>Code is valid for 3 hours from session start time</li>
        </ol>
      </div>

      <div class="quick-links" style="justify-content: center; margin-top: 2rem;">
        <a href="mark_attendance.php?session_id=<?= $session_id ?>" class="btn">Manual Attendance</a>
        <a href="manage_sessions.php" class="btn outline">Back to Sessions</a>
      </div>
    </div>
  </main>

  <script>
    // Timer countdown (3 hours)
    let timeLeft = 3 * 60 * 60; // 3 hours in seconds
    
    function updateTimer() {
      const hours = Math.floor(timeLeft / 3600);
      const minutes = Math.floor((timeLeft % 3600) / 60);
      const seconds = timeLeft % 60;
      
      document.getElementById('timer').textContent = 
        `Valid for: ${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
      
      if (timeLeft > 0) {
        timeLeft--;
        setTimeout(updateTimer, 1000);
      } else {
        document.getElementById('timer').textContent = 'Code Expired';
        document.getElementById('timer').style.background = 'rgba(255,0,0,0.3)';
      }
    }
    
    updateTimer();
  </script>
</body>
</html>