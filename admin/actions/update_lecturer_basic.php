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
    
    // Validate lecturer_id
    if (!$lecturer_id) {
        $_SESSION['error'] = 'Invalid lecturer ID.';
        header('Location: ../lecturer_details.php');
        exit();
    }
    
    $data = [
        'address' => trim($_POST['address'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'bio' => trim($_POST['bio'] ?? ''),
        'linkedin_url' => trim($_POST['linkedin_url'] ?? ''),
        'google_scholar_url' => trim($_POST['google_scholar_url'] ?? ''),
        'researchgate_url' => trim($_POST['researchgate_url'] ?? ''),
        'orcid_url' => trim($_POST['orcid_url'] ?? '')
    ];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $lecturerModel = new LecturerDetailModel($conn);
        
        if ($lecturerModel->updateBasicInfo($lecturer_id, $data)) {
            $_SESSION['success'] = 'Basic information updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update basic information.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: ../lecturer_details.php?lecturer_id=' . $lecturer_id);
    exit();
}

header('Location: ../lecturer_details.php');
exit();
