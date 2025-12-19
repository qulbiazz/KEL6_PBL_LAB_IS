<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../../config/database.php';
require_once '../../models/LecturerDetailModel.php';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$lecturer_id = (int)($_GET['lecturer_id'] ?? 0);

if (!$id || !$lecturer_id || !$type) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: ../lecturer_details.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$lecturerModel = new LecturerDetailModel($conn);

$success = false;
switch ($type) {
    case 'project':
        $success = $lecturerModel->deleteProject($id);
        $anchor = '#projects';
        break;
    case 'education':
        $success = $lecturerModel->deleteEducation($id);
        $anchor = '#education';
        break;
    case 'skill':
        $success = $lecturerModel->deleteSkill($id);
        $anchor = '#skills';
        break;
    case 'interest':
        $success = $lecturerModel->deleteInterest($id);
        $anchor = '#interests';
        break;
    case 'publication':
        $success = $lecturerModel->deletePublication($id);
        $anchor = '#publications';
        break;
    default:
        $_SESSION['error'] = 'Invalid type.';
        header('Location: ../lecturer_details.php?lecturer_id=' . $lecturer_id);
        exit();
}

if ($success) {
    $_SESSION['success'] = ucfirst($type) . ' deleted successfully!';
} else {
    $_SESSION['error'] = 'Failed to delete ' . $type . '.';
}

header('Location: ../lecturer_details.php?lecturer_id=' . $lecturer_id . $anchor);
exit();
