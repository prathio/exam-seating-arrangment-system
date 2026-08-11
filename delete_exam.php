<?php
require_once 'config.php';
requireAdmin();
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM seating WHERE exam_id=$id");
    $conn->query("DELETE FROM exams WHERE id=$id");
}
header("Location: index.php");
exit;
?>
