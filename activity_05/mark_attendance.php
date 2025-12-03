<?php
  require_once 'auth.php';
  require_once 'db_connect.php';
  
  requireRole('faculty');
  
  $user = getCurrentUser();
  $page_title = "Mark Attendance";
  
  // Get session_id from URL
  $session_id = $_GET['session_id'] ?? '';
  
  if (!$session_id) {
    header('Location: manage_sessions.php');
    exit();
  }
  
  // Handle attendance marking
  if ($_POST && isset($_POST['mark_attendance'])) {
    $attendance_data = $_POST['attendance'] ?? [];
    
    foreach ($attendance_data as $student_id => $status) {
      $notes = $_POST['notes'][$student_id] ?? '';
      
      // Check if attendance already exists
      $check_sql = "SELECT id FROM attendance WHERE student_id = ? AND session_id = ?";
      $check_stmt = mysqli_prepare($conn, $check_sql);
      mysqli_stmt_bind_param($check_stmt, "ii", $student_id, $session_id);
      mysqli_stmt_execute($check_stmt);
      $check_result = mysqli_stmt_get_result($check_stmt);
      
      if (mysqli_num_rows($check_result) > 0) {
        // Update existing attendance
        $update_sql = "UPDATE attendance SET status = ?, notes = ?, marked_by = ?, marked_at = NOW() 
                      WHERE student_id = ? AND session_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ssiii", $status, $notes, $user['id'], $student_id, $session_id);
        mysqli_stmt_execute($update_stmt);
      } else {
        // Insert new attendance
        $insert_sql = "INSERT INTO attendance (student_id, session_id, status, notes, marked_by) 
                      VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "iissi", $student_id, $session_id, $status, $notes, $user['id']);
        mysqli_stmt_execute($insert_stmt);
      }
    }
    
    $success_message = "Attendance marked successfully!";
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
  
  if (!$session) {
    header('Location: manage_sessions.php');
    exit();
  }
  
  // Get students enrolled in this course
  $students_sql = "SELECT u.id, s.name, s.email, a.status, a.notes
                   FROM student_courses sc
                   JOIN users u ON sc.student_id = u.id
                   JOIN students s ON u.student_id = s.id
                   LEFT JOIN attendance a ON a.student_id = u.id AND a.session_id = ?
                   WHERE sc.course_id = ?
                   ORDER BY s.name";
  $students_stmt = mysqli_prepare($conn, $students_sql);
  mysqli_stmt_bind_param($students_stmt, "ii", $session_id, $session['course_id']);
  mysqli_stmt_execute($students_stmt);
  $students = mysqli_stmt_get_result($students_stmt);
  
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
      <a href="students.php">Students</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2>Mark Attendance</h2>
      <p><?= htmlspecialchars($session['course_code'] . ' - ' . $session['course_name']) ?></p>
      <p>Date: <?= date('M d, Y', strtotime($session['session_date'])) ?> | 
         Time: <?= date('g:i A', strtotime($session['session_time'])) ?> | 
         Location: <?= htmlspecialchars($session['location']) ?></p>
    </div>

    <?php if (isset($success_message)): ?>
      <div class="alert success">
        <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <h2>Student Attendance</h2>
      <form method="POST">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Student Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($students) > 0): ?>
                <?php while ($student = mysqli_fetch_assoc($students)): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($student['name']) ?></strong></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td>
                      <select name="attendance[<?= $student['id'] ?>]" required>
                        <option value="present" <?= $student['status'] === 'present' ? 'selected' : '' ?>>Present</option>
                        <option value="absent" <?= $student['status'] === 'absent' || !$student['status'] ? 'selected' : '' ?>>Absent</option>
                        <option value="late" <?= $student['status'] === 'late' ? 'selected' : '' ?>>Late</option>
                        <option value="excused" <?= $student['status'] === 'excused' ? 'selected' : '' ?>>Excused</option>
                      </select>
                    </td>
                    <td>
                      <input type="text" name="notes[<?= $student['id'] ?>]" 
                             value="<?= htmlspecialchars($student['notes'] ?? '') ?>" 
                             placeholder="Add notes">
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4">No students enrolled in this course.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        
        <div class="form-actions">
          <button type="submit" name="mark_attendance" class="btn">Save Attendance</button>
          <a href="manage_sessions.php" class="btn outline">Back to Sessions</a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>