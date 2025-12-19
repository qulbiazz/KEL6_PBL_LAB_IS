<?php
/**
 * Application Configuration
 * Centralized configuration for paths, URLs, and settings
 */

require_once __DIR__ . '/env.php';

class Config
{
    /**
     * Get base path of the application
     */
    public static function basePath($path = '')
    {
        $basePath = dirname(__DIR__);
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }

    /**
     * Get upload directory path
     */
    public static function uploadPath($path = '')
    {
        $uploadDir = Env::get('UPLOAD_DIR', 'uploads');
        $uploadPath = self::basePath($uploadDir);
        
        // Create directory if not exists
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        return $path ? $uploadPath . '/' . ltrim($path, '/') : $uploadPath;
    }

    /**
     * Get upload URL
     */
    public static function uploadUrl($path = '')
    {
        $uploadDir = Env::get('UPLOAD_DIR', 'uploads');
        $baseUrl = rtrim(Env::get('APP_URL', ''), '/');
        
        return $path ? $baseUrl . '/' . $uploadDir . '/' . ltrim($path, '/') : $baseUrl . '/' . $uploadDir;
    }

    /**
     * Get asset path
     */
    public static function assetPath($path = '')
    {
        return self::basePath('assets/' . ltrim($path, '/'));
    }

    /**
     * Get asset URL
     */
    public static function assetUrl($path = '')
    {
        $baseUrl = rtrim(Env::get('APP_URL', ''), '/');
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }

    /**
     * Get app URL
     */
    public static function url($path = '')
    {
        $baseUrl = rtrim(Env::get('APP_URL', ''), '/');
        return $path ? $baseUrl . '/' . ltrim($path, '/') : $baseUrl;
    }

    /**
     * Get app name
     */
    public static function appName()
    {
        return Env::get('APP_NAME', 'InLet Laboratory');
    }

    /**
     * Check if app is in debug mode
     */
    public static function isDebug()
    {
        return Env::isDevelopment();
    }

    /**
     * Get session lifetime
     */
    public static function sessionLifetime()
    {
        return (int) Env::get('SESSION_LIFETIME', 7200);
    }

    /**
     * Ensure upload directories exist
     */
    public static function ensureUploadDirectories()
    {
        $directories = [
            'banners',
            'products',
            'partners',
            'team',
            'news',
            'gallery',
            'research'
        ];

        foreach ($directories as $dir) {
            $path = self::uploadPath($dir);
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Get relative path from absolute path
     */
    public static function getRelativePath($absolutePath)
    {
        $basePath = self::basePath();
        return str_replace($basePath . '/', '', $absolutePath);
    }

    /**
     * Get database configuration
     */
    public static function database()
    {
        return [
            'host' => Env::get('DB_HOST', 'localhost'),
            'port' => Env::get('DB_PORT', '5432'),
            'database' => Env::get('DB_NAME', 'inlet_db'),
            'username' => Env::get('DB_USER', 'postgres'),
            'password' => Env::get('DB_PASSWORD', ''),
        ];
    }
}
