<?php
/**
 * Authentication and Session Management
 * Handles login, logout, and session validation
 */

session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect to login if not specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: unauthorized.php');
        exit();
    }
}

// Login function - simplified version
function login($username, $password) {
    require_once 'db_connect.php';
    
    // Simple query
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            // Get student name if applicable
            $student_name = null;
            if ($row['student_id']) {
                $student_sql = "SELECT name FROM students WHERE id = ?";
                $student_stmt = mysqli_prepare($conn, $student_sql);
                mysqli_stmt_bind_param($student_stmt, "i", $row['student_id']);
                mysqli_stmt_execute($student_stmt);
                $student_result = mysqli_stmt_get_result($student_stmt);
                if ($student_row = mysqli_fetch_assoc($student_result)) {
                    $student_name = $student_row['name'];
                }
                mysqli_stmt_close($student_stmt);
            }
            
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['user_role'] = $row['role'];
            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['student_name'] = $student_name;
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            return true;
        }
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return false;
}

// Logout function
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['user_role'],
        'student_id' => $_SESSION['student_id'],
        'student_name' => $_SESSION['student_name']
    ];
}
?>
