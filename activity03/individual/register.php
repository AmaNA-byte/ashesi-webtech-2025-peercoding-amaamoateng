<?php
  $page_title = "Register Student";
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
    <div class="welcome-banner">
      <h2>Register New Student</h2>
      <p>Fill in the student information below</p>
    </div>

    <form class="card form" method="POST" action="register_action.php">
      <h2>Student Information</h2>
      
      <div class="form-row">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="Enter full name" required>
      </div>

      <div class="form-row">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="student@example.com" required>
      </div>

      <div class="form-row">
        <label for="program">Program</label>
        <select id="program" name="program" required>
          <option value="">-- Select Program --</option>
          <option value="Computer Science">Computer Science</option>
          <option value="Business Administration">Business Administration</option>
          <option value="Management Information Systems">Management Information Systems</option>
          <option value="Computer Engineering">Computer Engineering</option>
          <option value="Electrical Engineering">Electrical Engineering</option>
          <option value="Mechanical Engineering">Mechanical Engineering</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="submit">Register Student</button>
        <a class="btn outline" href="index.php">Cancel</a>
      </div>
    </form>
  </main>
</body>
</html>
