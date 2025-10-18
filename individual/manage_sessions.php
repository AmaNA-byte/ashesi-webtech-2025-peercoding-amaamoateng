<?php
  require_once 'auth.php';
  require_once 'db_connect.php';
  
  requireRole('faculty');
  
  $user = getCurrentUser();
  $page_title = "Manage Sessions";
  
  // Handle form submissions
  if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
      $course_id = $_POST['course_id'];
      $session_date = $_POST['session_date'];
      $session_time = $_POST['session_time'];
      $session_type = $_POST['session_type'];
      $location = $_POST['location'];
      $instructor_notes = $_POST['instructor_notes'];
      $materials_required = $_POST['materials_required'];
      
      $sql = "INSERT INTO sessions (course_id, session_date, session_time, session_type, location, instructor_notes, materials_required) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "issssss", $course_id, $session_date, $session_time, $session_type, $location, $instructor_notes, $materials_required);
      
      if (mysqli_stmt_execute($stmt)) {
        $success_message = "Session created successfully!";
      } else {
        $error_message = "Error creating session: " . mysqli_error($conn);
      }
    }
    
    if ($action === 'update') {
      $session_id = $_POST['session_id'];
      $session_date = $_POST['session_date'];
      $session_time = $_POST['session_time'];
      $session_type = $_POST['session_type'];
      $location = $_POST['location'];
      $instructor_notes = $_POST['instructor_notes'];
      $materials_required = $_POST['materials_required'];
      
      $sql = "UPDATE sessions SET session_date = ?, session_time = ?, session_type = ?, location = ?, instructor_notes = ?, materials_required = ? 
              WHERE id = ?";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "ssssssi", $session_date, $session_time, $session_type, $location, $instructor_notes, $materials_required, $session_id);
      
      if (mysqli_stmt_execute($stmt)) {
        $success_message = "Session updated successfully!";
      } else {
        $error_message = "Error updating session: " . mysqli_error($conn);
      }
    }
    
    if ($action === 'delete') {
      $session_id = $_POST['session_id'];
      
      $sql = "DELETE FROM sessions WHERE id = ?";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "i", $session_id);
      
      if (mysqli_stmt_execute($stmt)) {
        $success_message = "Session deleted successfully!";
      } else {
        $error_message = "Error deleting session: " . mysqli_error($conn);
      }
    }
  }
  
  // Get courses for dropdown
  $courses_sql = "SELECT * FROM courses ORDER BY course_code";
  $courses_result = mysqli_query($conn, $courses_sql);
  
  // Get sessions with course information
  $sessions_sql = "SELECT s.*, c.course_code, c.course_name 
                   FROM sessions s 
                   JOIN courses c ON s.course_id = c.id 
                   ORDER BY s.session_date DESC, s.session_time DESC";
  $sessions_result = mysqli_query($conn, $sessions_sql);
  
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
      <a href="manage_sessions.php" class="active">Manage Sessions</a>
      <a href="attendance_report.php">Attendance Report</a>
      <a href="students.php">Students</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="welcome-banner">
      <h2>Manage Sessions</h2>
      <p>Create and manage course sessions</p>
    </div>

    <?php if (isset($success_message)): ?>
      <div class="alert success">
        <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
      <div class="alert error">
        <?= htmlspecialchars($error_message) ?>
      </div>
    <?php endif; ?>

    <!-- Create New Session Form -->
    <div class="card">
      <h2>Create New Session</h2>
      <form method="POST" class="form">
        <input type="hidden" name="action" value="create">
        
        <div class="form-row">
          <label for="course_id">Course</label>
          <select id="course_id" name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
              <option value="<?= $course['id'] ?>">
                <?= htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <div class="form-row">
          <label for="session_date">Date</label>
          <input type="date" id="session_date" name="session_date" required>
        </div>
        
        <div class="form-row">
          <label for="session_time">Time</label>
          <input type="time" id="session_time" name="session_time" required>
        </div>
        
        <div class="form-row">
          <label for="session_type">Session Type</label>
          <select id="session_type" name="session_type" required>
            <option value="lecture">Lecture</option>
            <option value="lab">Lab</option>
            <option value="practical">Practical</option>
          </select>
        </div>
        
        <div class="form-row">
          <label for="location">Location</label>
          <input type="text" id="location" name="location" placeholder="e.g., Room 101, Lab 1" required>
        </div>
        
        <div class="form-row">
          <label for="instructor_notes">Instructor Notes</label>
          <textarea id="instructor_notes" name="instructor_notes" rows="3" placeholder="Notes about the session content..."></textarea>
        </div>
        
        <div class="form-row">
          <label for="materials_required">Materials Required</label>
          <textarea id="materials_required" name="materials_required" rows="2" placeholder="e.g., Laptop, notebook, calculator..."></textarea>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn">Create Session</button>
        </div>
      </form>
    </div>

    <!-- Existing Sessions -->
    <div class="card">
      <h2>Existing Sessions</h2>
      <?php if (mysqli_num_rows($sessions_result) > 0): ?>
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
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($session = mysqli_fetch_assoc($sessions_result)): ?>
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
                  <td>
                    <div class="quick-links">
                      <button onclick="editSession(<?= htmlspecialchars(json_encode($session)) ?>)" class="btn outline" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Edit</button>
                      <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this session?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                        <button type="submit" class="btn outline" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background-color: #dc3545; color: white; border-color: #dc3545;">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p>No sessions found. Create your first session above.</p>
      <?php endif; ?>
    </div>
  </main>

  <script>
    function editSession(session) {
      // Populate form with session data
      document.getElementById('course_id').value = session.course_id;
      document.getElementById('session_date').value = session.session_date;
      document.getElementById('session_time').value = session.session_time;
      document.getElementById('session_type').value = session.session_type;
      document.getElementById('location').value = session.location;
      document.getElementById('instructor_notes').value = session.instructor_notes || '';
      document.getElementById('materials_required').value = session.materials_required || '';
      
      // Change form action to update
      const form = document.querySelector('form');
      form.querySelector('input[name="action"]').value = 'update';
      form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="session_id" value="' + session.id + '">');
      
      // Scroll to form
      form.scrollIntoView({ behavior: 'smooth' });
    }
  </script>
</body>
</html>
