<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../../config/database.php';
require_once '../../models/LecturerDetailModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lecturer_id = (int)$_POST['lecturer_id'];
    $interest_id = (int)$_POST['interest_id'];
    
    $data = [
        'interest_text' => $_POST['interest_text']
    ];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $lecturerModel = new LecturerDetailModel($conn);
        
        if ($lecturerModel->updateInterest($interest_id, $lecturer_id, $data)) {
            $_SESSION['success'] = 'Interest updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update interest.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: ../lecturer_details.php?lecturer_id=' . $lecturer_id . '#interests');
    exit();
}

header('Location: ../lecturer_details.php');
exit();
