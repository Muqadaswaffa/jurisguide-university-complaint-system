<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

if(isset($_GET['id']) && isset($_GET['status'])){
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    
    // Update status with appropriate date tracking
    if($status == 'In Progress') {
        $stmt = $conn->prepare("UPDATE complaints SET status=?, in_progress_date=NOW() WHERE id=?");
        $stmt->bind_param("si", $status, $id);
    } 
    elseif($status == 'Resolved') {
        $stmt = $conn->prepare("UPDATE complaints SET status=?, resolved_date=NOW() WHERE id=?");
        $stmt->bind_param("si", $status, $id);
    } 
    elseif($status == 'Closed') {
        $stmt = $conn->prepare("UPDATE complaints SET status=?, closed_date=NOW() WHERE id=?");
        $stmt->bind_param("si", $status, $id);
    } 
    else {
        $stmt = $conn->prepare("UPDATE complaints SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
    }
    
    if($stmt->execute()){
        $stmt->close();
        header("Location: all_complaints.php");
        exit;
    } else {
        echo "Error updating status: " . $conn->error;
    }
}

header("Location: all_complaints.php");
exit;
?>