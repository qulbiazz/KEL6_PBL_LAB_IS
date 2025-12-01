<?php
/**
 * AJAX Handler for Site Settings Update
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/SiteSettingsModel.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $siteSettingsModel = new SiteSettingsModel($db);
    
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['admin_id'];
    
    switch ($action) {
        case 'update_logo':
            // Handle logo upload
            if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
                $result = $siteSettingsModel->uploadLogo($_FILES['logo_file'], $user_id);
                echo json_encode($result);
            } else {
                // Update only logo alt text
                $data = ['logo_alt' => $_POST['logo_alt'] ?? ''];
                $result = $siteSettingsModel->updateSettings($data, $user_id);
                echo json_encode($result);
            }
            break;
            
        case 'update_footer_about':
            $data = [
                'footer_about_title' => $_POST['footer_about_title'] ?? '',
                'footer_about_text' => $_POST['footer_about_text'] ?? '',
                'footer_copyright' => $_POST['footer_copyright'] ?? ''
            ];
            $result = $siteSettingsModel->updateSettings($data, $user_id);
            echo json_encode($result);
            break;
            
        case 'update_contact':
            $data = [
                'contact_email' => $_POST['contact_email'] ?? '',
                'contact_phone' => $_POST['contact_phone'] ?? '',
                'contact_address' => $_POST['contact_address'] ?? ''
            ];
            $result = $siteSettingsModel->updateSettings($data, $user_id);
            echo json_encode($result);
            break;
            
        case 'update_social':
            $data = [
                'social_facebook' => $_POST['social_facebook'] ?? '',
                'social_instagram' => $_POST['social_instagram'] ?? '',
                'social_youtube' => $_POST['social_youtube'] ?? '',
                'social_linkedin' => $_POST['social_linkedin'] ?? '',
                'social_twitter' => $_POST['social_twitter'] ?? ''
            ];
            $result = $siteSettingsModel->updateSettings($data, $user_id);
            echo json_encode($result);
            break;
            
        case 'update_quick_links':
            // Validate and process quick links
            $links = [];
            
            if (isset($_POST['links']) && is_array($_POST['links'])) {
                foreach ($_POST['links'] as $link) {
                    if (!empty($link['label']) && !empty($link['url'])) {
                        $links[] = [
                            'label' => trim($link['label']),
                            'url' => trim($link['url'])
                        ];
                    }
                }
                
                // Limit to 8 links
                if (count($links) > 8) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Maximum 8 quick links allowed'
                    ]);
                    break;
                }
            }
            
            $data = ['quick_links' => $links];
            $result = $siteSettingsModel->updateSettings($data, $user_id);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Site settings update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
