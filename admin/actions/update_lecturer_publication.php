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
    $publication_id = (int)$_POST['publication_id'];
    
    $data = [
        'title' => $_POST['title'],
        'year' => (int)$_POST['year'],
        'url' => $_POST['url'] ?? ''
    ];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $lecturerModel = new LecturerDetailModel($conn);
        
        if ($lecturerModel->updatePublication($publication_id, $lecturer_id, $data)) {
            $_SESSION['success'] = 'Publication updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update publication.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: ../lecturer_details.php?lecturer_id=' . $lecturer_id . '#publications');
    exit();
}

header('Location: ../lecturer_details.php');
exit();
