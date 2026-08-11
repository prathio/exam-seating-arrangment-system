<?php
// config.php - Database + Session Config
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'exam_seating');
define('SITE_NAME', 'ExamSeat Pro');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;padding:2rem;background:#fee;border:2px solid red;border-radius:8px;margin:2rem'>
        <h3>❌ Database Connection Failed</h3>
        <p>".$conn->connect_error."</p>
        <p>Make sure XAMPP MySQL is running and database is created.</p>
    </div>");
}
$conn->set_charset("utf8");

// ---- Helper Functions ----

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: student_dashboard.php");
        exit;
    }
}

function getCurrentUser() {
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? '',
        'role'     => $_SESSION['role'] ?? '',
        'name'     => $_SESSION['name'] ?? '',
    ];
}
?>
