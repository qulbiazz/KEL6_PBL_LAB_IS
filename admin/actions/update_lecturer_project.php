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
    $project_id = (int)$_POST['project_id'];
    
    $data = [
        'title' => $_POST['title'],
        'category' => $_POST['category'] ?? '',
        'description' => $_POST['description'],
        'start_date' => $_POST['start_date'],
        'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null
    ];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $lecturerModel = new LecturerDetailModel($conn);
        
        if ($lecturerModel->updateProject($project_id, $lecturer_id, $data)) {
            $_SESSION['success'] = 'Project updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update project.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: ../lecturer_details.php?lecturer_id=' . $lecturer_id . '#projects');
    exit();
}

header('Location: ../lecturer_details.php');
exit();
