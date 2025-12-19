<?php
/**
 * Site Settings Model
 * Manages dynamic site configuration (logo, footer content, contact info)
 */

class SiteSettingsModel {
    private $conn;
    private $table_name = "site_settings";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all site settings
     */
    public function getSettings() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            if ($result && isset($result['quick_links'])) {
                $result['quick_links'] = json_decode($result['quick_links'], true) ?: [];
            }
            
            return $result ?: $this->getDefaultSettings();
        } catch(PDOException $e) {
            error_log("Error getting site settings: " . $e->getMessage());
            return $this->getDefaultSettings();
        }
    }

    /**
     * Update site settings
     */
    public function updateSettings($data, $user_id = null) {
        try {
            // Build update query dynamically based on provided fields
            $allowed_fields = [
                'logo_path', 'logo_alt',
                'footer_about_title', 'footer_about_text', 'footer_copyright',
                'contact_email', 'contact_phone', 'contact_address',
                'social_facebook', 'social_instagram', 'social_youtube',
                'social_linkedin', 'social_twitter', 'quick_links',
                'borrowing_notification_email'
            ];
            
            $update_fields = [];
            $params = [];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    // Handle JSON fields
                    if ($field === 'quick_links' && is_array($data[$field])) {
                        $update_fields[] = "$field = :$field";
                        $params[":$field"] = json_encode($data[$field]);
                    } else {
                        $update_fields[] = "$field = :$field";
                        $params[":$field"] = $data[$field];
                    }
                }
            }
            
            if (empty($update_fields)) {
                return ['success' => false, 'message' => 'No fields to update'];
            }
            
            // Add updated_by if provided
            if ($user_id) {
                $update_fields[] = "updated_by = :updated_by";
                $params[':updated_by'] = $user_id;
            }
            
            $query = "UPDATE " . $this->table_name . " 
                     SET " . implode(", ", $update_fields) . " 
                     WHERE id = 1";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Settings updated successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to update settings'
            ];
            
        } catch(PDOException $e) {
            error_log("Error updating site settings: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload and update logo
     */
    public function uploadLogo($file, $user_id = null) {
        try {
            // Validate file
            $allowed_types = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file['type'], $allowed_types)) {
                return [
                    'success' => false,
                    'message' => 'Invalid file type. Allowed: JPG, PNG, SVG, WEBP'
                ];
            }
            
            if ($file['size'] > $max_size) {
                return [
                    'success' => false,
                    'message' => 'File too large. Maximum size: 2MB'
                ];
            }
            
            // Create upload directory if not exists
            $upload_dir = __DIR__ . '/../assets/images/site/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $extension;
            $upload_path = $upload_dir . $filename;
            $db_path = 'assets/images/site/' . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old logo if exists and not default
                $current_settings = $this->getSettings();
                if ($current_settings && 
                    !empty($current_settings['logo_path']) && 
                    $current_settings['logo_path'] !== 'assets/images/inlet_logo_only.png' &&
                    file_exists(__DIR__ . '/../' . $current_settings['logo_path'])) {
                    unlink(__DIR__ . '/../' . $current_settings['logo_path']);
                }
                
                // Update database
                $update_result = $this->updateSettings([
                    'logo_path' => $db_path
                ], $user_id);
                
                if ($update_result['success']) {
                    return [
                        'success' => true,
                        'message' => 'Logo uploaded successfully',
                        'logo_path' => $db_path
                    ];
                }
                
                return $update_result;
            }
            
            return [
                'success' => false,
                'message' => 'Failed to upload file'
            ];
            
        } catch(Exception $e) {
            error_log("Error uploading logo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Upload error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get specific setting value
     */
    public function getSetting($key) {
        $settings = $this->getSettings();
        return isset($settings[$key]) ? $settings[$key] : null;
    }

    /**
     * Get logo path
     */
    public function getLogoPath() {
        return $this->getSetting('logo_path') ?: 'assets/images/inlet_logo_only.png';
    }

    /**
     * Get footer about text
     */
    public function getFooterAbout() {
        return [
            'title' => $this->getSetting('footer_about_title') ?: 'INLET',
            'text' => $this->getSetting('footer_about_text') ?: ''
        ];
    }

    /**
     * Get contact information
     */
    public function getContactInfo() {
        return [
            'email' => $this->getSetting('contact_email'),
            'phone' => $this->getSetting('contact_phone'),
            'address' => $this->getSetting('contact_address')
        ];
    }

    /**
     * Get social media links
     */
    public function getSocialLinks() {
        return [
            'facebook' => $this->getSetting('social_facebook'),
            'instagram' => $this->getSetting('social_instagram'),
            'youtube' => $this->getSetting('social_youtube'),
            'linkedin' => $this->getSetting('social_linkedin'),
            'twitter' => $this->getSetting('social_twitter')
        ];
    }

    /**
     * Get default settings (fallback)
     */
    private function getDefaultSettings() {
        return [
            'id' => 1,
            'logo_path' => 'assets/images/inlet_logo_only.png',
            'logo_alt' => 'INLET Logo',
            'footer_about_title' => 'INLET',
            'footer_about_text' => 'Innovation and Laboratory of Electrical Techniques',
            'footer_copyright' => '© ' . date('Y') . ' Information and Learning Engineering Technology Laboratory. All rights reserved.',
            'contact_email' => 'inlet@polinema.ac.id',
            'contact_phone' => '+62 341 404424',
            'contact_address' => 'Jl. Soekarno Hatta No.9, Jatimulyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65141',
            'social_facebook' => 'https://facebook.com/inlet.polinema',
            'social_instagram' => 'https://instagram.com/inlet.polinema',
            'social_youtube' => 'https://youtube.com/@inlet.polinema',
            'social_linkedin' => null,
            'social_twitter' => null,
            'quick_links' => [],
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => null
        ];
    }

    /**
     * Reset to default settings
     */
    public function resetToDefaults($user_id = null) {
        try {
            $defaults = $this->getDefaultSettings();
            unset($defaults['id'], $defaults['updated_at'], $defaults['updated_by']);
            
            return $this->updateSettings($defaults, $user_id);
        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => 'Error resetting settings: ' . $e->getMessage()
            ];
        }
    }
}
?>
