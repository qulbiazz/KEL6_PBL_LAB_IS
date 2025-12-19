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
    $skill_id = (int)$_POST['skill_id'];
    
    $data = [
        'skill_name' => $_POST['skill_name'],
        'category' => $_POST['category'],
        'skill_icon' => $_POST['skill_icon'] ?? ''
    ];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $lecturerModel = new LecturerDetailModel($conn);
        
        if ($lecturerModel->updateSkill($skill_id, $lecturer_id, $data)) {
            $_SESSION['success'] = 'Skill updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update skill.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: ../lecturer_details.php?lecturer_id=' . $lecturer_id . '#skills');
    exit();
}

header('Location: ../lecturer_details.php');
exit();
