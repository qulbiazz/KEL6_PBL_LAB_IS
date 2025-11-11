<?php
/**
 * File Upload Helper
 * Handles file uploads with validation and storage
 */

require_once __DIR__ . '/config.php';

class FileUpload
{
    /**
     * Upload file to specified directory
     * 
     * @param array $file - $_FILES['field_name']
     * @param string $directory - subdirectory in uploads (e.g., 'banners', 'products')
     * @param array $options - optional settings
     * @return array - ['success' => bool, 'filename' => string, 'error' => string]
     */
    public static function upload($file, $directory, $options = [])
    {
        // Default options
        $defaults = [
            'max_size' => 5 * 1024 * 1024, // 5MB
            'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'unique_name' => true,
            'prefix' => '',
        ];
        
        $options = array_merge($defaults, $options);
        
        // Validate file
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        // Check file size
        if ($file['size'] > $options['max_size']) {
            $maxSizeMB = $options['max_size'] / (1024 * 1024);
            return ['success' => false, 'error' => "File too large. Max size: {$maxSizeMB}MB"];
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $options['allowed_types'])) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $options['allowed_extensions'])) {
            return ['success' => false, 'error' => 'Invalid file extension'];
        }
        
        // Generate filename
        if ($options['unique_name']) {
            $filename = $options['prefix'] . uniqid() . '_' . time() . '.' . $extension;
        } else {
            $filename = $options['prefix'] . self::sanitizeFilename($file['name']);
        }
        
        // Ensure upload directory exists
        $uploadPath = Config::uploadPath($directory);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Move uploaded file
        $destination = $uploadPath . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Set proper permissions
            chmod($destination, 0644);
            
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $destination,
                'url' => Config::uploadUrl($directory . '/' . $filename)
            ];
        } else {
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }
    }
    
    /**
     * Delete uploaded file
     */
    public static function delete($filename, $directory)
    {
        $filePath = Config::uploadPath($directory . '/' . $filename);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }
    
    /**
     * Check if file exists
     */
    public static function exists($filename, $directory)
    {
        $filePath = Config::uploadPath($directory . '/' . $filename);
        return file_exists($filePath);
    }
    
    /**
     * Get file URL
     */
    public static function url($filename, $directory)
    {
        if (self::exists($filename, $directory)) {
            return Config::uploadUrl($directory . '/' . $filename);
        }
        
        // Return placeholder if file doesn't exist
        return Config::assetUrl('images/placeholder.jpg');
    }
    
    /**
     * Sanitize filename
     */
    private static function sanitizeFilename($filename)
    {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        
        return $filename;
    }
    
    /**
     * Get file size in human readable format
     */
    public static function getFileSize($filename, $directory)
    {
        $filePath = Config::uploadPath($directory . '/' . $filename);
        
        if (!file_exists($filePath)) {
            return '0 B';
        }
        
        $bytes = filesize($filePath);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
