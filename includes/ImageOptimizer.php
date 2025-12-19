<?php
/**
 * Image Optimizer Helper
 * Automatically compress and optimize images for web
 * 
 * Features:
 * - Auto resize to max dimensions
 * - Quality compression (JPEG: 85%, PNG: 8, WebP: 85%)
 * - WebP conversion support
 * - Maintains aspect ratio
 * - Progressive JPEG for better loading
 */

class ImageOptimizer {
    
    // Configuration
    private $maxWidth = 1920;           // Max width for banners
    private $maxHeight = 1080;          // Max height for banners
    private $jpegQuality = 85;          // JPEG quality (0-100)
    private $pngCompression = 8;        // PNG compression (0-9)
    private $webpQuality = 85;          // WebP quality (0-100)
    
    /**
     * Optimize uploaded image
     * @param string $sourcePath - Path to original uploaded file
     * @param string $destPath - Path where optimized image will be saved
     * @param array $options - Optional settings override
     * @return array - ['success' => bool, 'message' => string, 'saved' => int bytes]
     */
    public function optimize($sourcePath, $destPath, $options = []) {
        try {
            // Merge options with defaults
            $maxWidth = $options['maxWidth'] ?? $this->maxWidth;
            $maxHeight = $options['maxHeight'] ?? $this->maxHeight;
            $jpegQuality = $options['jpegQuality'] ?? $this->jpegQuality;
            
            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return ['success' => false, 'message' => 'Invalid image file'];
            }
            
            list($width, $height, $type) = $imageInfo;
            $originalSize = filesize($sourcePath);
            
            // Calculate new dimensions (maintain aspect ratio)
            $newDimensions = $this->calculateDimensions($width, $height, $maxWidth, $maxHeight);
            $newWidth = $newDimensions['width'];
            $newHeight = $newDimensions['height'];
            
            // Create image resource based on type
            $sourceImage = $this->createImageResource($sourcePath, $type);
            if (!$sourceImage) {
                return ['success' => false, 'message' => 'Failed to create image resource'];
            }
            
            // Create new image canvas
            $destImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
                imagealphablending($destImage, false);
                imagesavealpha($destImage, true);
                $transparent = imagecolorallocatealpha($destImage, 0, 0, 0, 127);
                imagefill($destImage, 0, 0, $transparent);
            }
            
            // Resample image
            imagecopyresampled(
                $destImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $width, $height
            );
            
            // Save optimized image
            $saved = $this->saveOptimizedImage($destImage, $destPath, $type, $jpegQuality);
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($destImage);
            
            if (!$saved) {
                return ['success' => false, 'message' => 'Failed to save optimized image'];
            }
            
            // Calculate savings
            $optimizedSize = filesize($destPath);
            $savedBytes = $originalSize - $optimizedSize;
            $savedPercent = round(($savedBytes / $originalSize) * 100, 1);
            
            return [
                'success' => true,
                'message' => 'Image optimized successfully',
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'saved_bytes' => $savedBytes,
                'saved_percent' => $savedPercent,
                'dimensions' => [
                    'original' => ['width' => $width, 'height' => $height],
                    'optimized' => ['width' => $newWidth, 'height' => $newHeight]
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Calculate new dimensions maintaining aspect ratio
     */
    private function calculateDimensions($width, $height, $maxWidth, $maxHeight) {
        // No resize needed if within limits
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return ['width' => $width, 'height' => $height];
        }
        
        $ratio = $width / $height;
        
        if ($width > $height) {
            // Landscape
            $newWidth = min($width, $maxWidth);
            $newHeight = round($newWidth / $ratio);
            
            if ($newHeight > $maxHeight) {
                $newHeight = $maxHeight;
                $newWidth = round($newHeight * $ratio);
            }
        } else {
            // Portrait or square
            $newHeight = min($height, $maxHeight);
            $newWidth = round($newHeight * $ratio);
            
            if ($newWidth > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = round($newWidth / $ratio);
            }
        }
        
        return ['width' => (int)$newWidth, 'height' => (int)$newHeight];
    }
    
    /**
     * Create image resource from file
     */
    private function createImageResource($path, $type) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($path);
            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($path);
            default:
                return false;
        }
    }
    
    /**
     * Save optimized image
     */
    private function saveOptimizedImage($image, $path, $type, $quality) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                // Progressive JPEG for better web loading
                imageinterlace($image, 1);
                return imagejpeg($image, $path, $quality);
                
            case IMAGETYPE_PNG:
                // PNG compression level 0-9 (we use 8 for good balance)
                return imagepng($image, $path, $this->pngCompression);
                
            case IMAGETYPE_GIF:
                return imagegif($image, $path);
                
            case IMAGETYPE_WEBP:
                if (function_exists('imagewebp')) {
                    return imagewebp($image, $path, $this->webpQuality);
                }
                // Fallback to JPEG if WebP not supported
                return imagejpeg($image, $path, $quality);
                
            default:
                return false;
        }
    }
    
    /**
     * Generate WebP version of image (modern format, better compression)
     */
    public function generateWebP($sourcePath, $destPath = null) {
        if (!function_exists('imagewebp')) {
            return ['success' => false, 'message' => 'WebP not supported on this server'];
        }
        
        if (!$destPath) {
            $destPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $sourcePath);
        }
        
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return ['success' => false, 'message' => 'Invalid image file'];
        }
        
        list($width, $height, $type) = $imageInfo;
        $sourceImage = $this->createImageResource($sourcePath, $type);
        
        if (!$sourceImage) {
            return ['success' => false, 'message' => 'Failed to create image resource'];
        }
        
        $success = imagewebp($sourceImage, $destPath, $this->webpQuality);
        imagedestroy($sourceImage);
        
        return [
            'success' => $success,
            'message' => $success ? 'WebP version created' : 'Failed to create WebP',
            'path' => $destPath
        ];
    }
    
    /**
     * Optimize multiple images in batch
     */
    public function optimizeBatch($files, $outputDir, $options = []) {
        $results = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            $destPath = $outputDir . '/' . $filename;
            
            $result = $this->optimize($file, $destPath, $options);
            $results[$filename] = $result;
        }
        
        return $results;
    }
    
    /**
     * Format bytes to human readable
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Validate image file
     */
    public static function validateImage($file, $maxSize = 5242880) { // 5MB default
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Check if file was uploaded
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['valid' => false, 'message' => 'Invalid file upload'];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Upload error code: ' . $file['error']];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $maxMB = round($maxSize / 1048576, 1);
            return ['valid' => false, 'message' => "File too large. Max size: {$maxMB}MB"];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['valid' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, WebP allowed'];
        }
        
        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'message' => 'Invalid file extension'];
        }
        
        return ['valid' => true, 'message' => 'Valid image file'];
    }
}
?>
