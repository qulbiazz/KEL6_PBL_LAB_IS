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
    $education_id = (int)$_POST['education_id'];
    
    $data = [
        'institution' => $_POST['institution'],
        'degree' => $_POST['degree'],
        'field_of_study' => $_POST['field_of_study'],
        'description' => $_POST['description'] ?? '',
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date']
    ];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $lecturerModel = new LecturerDetailModel($conn);
        
        if ($lecturerModel->updateEducation($education_id, $lecturer_id, $data)) {
            $_SESSION['success'] = 'Education updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update education.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: ../lecturer_details.php?lecturer_id=' . $lecturer_id . '#education');
    exit();
}

header('Location: ../lecturer_details.php');
exit();
