<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complaint_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $complaint_id = mysqli_real_escape_string($conn, $_POST['complaint_id']);

    $query = "UPDATE complaints SET status = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $complaint_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request or unauthorized']);
}
?>
