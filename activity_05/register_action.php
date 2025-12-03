<?php
  // Get form data
  $name    = isset($_POST['name']) ? trim($_POST['name']) : "";
  $email   = isset($_POST['email']) ? trim($_POST['email']) : "";
  $program = isset($_POST['program']) ? trim($_POST['program']) : "";

  // Validation
  $errors = [];
  if ($name === "")    { $errors[] = "Name is required."; }
  if ($email === "")   { $errors[] = "Email is required."; }
  if ($program === "") { $errors[] = "Program is required."; }

  $page_title = "Registration Result";
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
    <?php if (!empty($errors)): ?>
      <div class="alert error">
        <h3>Please fix the following:</h3>
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="card">
        <a href="register.php" class="btn">Go Back</a>
      </div>
    <?php else: ?>
      <?php
        require_once 'db_connect.php';

        // Clean data
        $name = mysqli_real_escape_string($conn, $name);
        $email = mysqli_real_escape_string($conn, $email);
        $program = mysqli_real_escape_string($conn, $program);

        // Insert into database
        $sql = "INSERT INTO students (name, email, program) VALUES ('$name', '$email', '$program')";
        $success = mysqli_query($conn, $sql);

        if (!$success) {
          $db_error = mysqli_error($conn);
        }

        mysqli_close($conn);
      ?>

      <?php if (isset($db_error)): ?>
        <div class="alert error">
          <h3>Database Error</h3>
          <p><?= htmlspecialchars($db_error) ?></p>
        </div>
        <div class="card">
          <a href="register.php" class="btn">Go Back</a>
        </div>
      <?php else: ?>
        <div class="success-box">
          <h2>✓ Registration Successful!</h2>
          <p>Student has been registered successfully</p>
        </div>

        <div class="card">
          <h2>Student Details</h2>
          <table class="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Program</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= htmlspecialchars($name) ?></td>
                <td><?= htmlspecialchars($email) ?></td>
                <td><?= htmlspecialchars($program) ?></td>
              </tr>
            </tbody>
          </table>

          <div class="quick-links">
            <a href="register.php" class="btn">Add Another Student</a>
            <a href="student_list.php" class="btn outline">View All Students</a>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </main>
</body>
</html>
