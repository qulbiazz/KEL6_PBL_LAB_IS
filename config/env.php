<?php
/**
 * Environment Variable Loader
 * Loads .env file and makes variables available via getenv()
 */

class Env
{
    /**
     * Load environment variables from .env file
     */
    public static function load($path = __DIR__ . '/../.env')
    {
        if (!file_exists($path)) {
            // In production, environment variables should be set by hosting platform
            if (self::getenv('APP_ENV') === 'production') {
                return;
            }
            throw new Exception('.env file not found at: ' . $path);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                // Set environment variable
                if (!array_key_exists($key, $_ENV)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    /**
     * Get environment variable with fallback
     */
    public static function get($key, $default = null)
    {
        $value = self::getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Convert string booleans to actual booleans
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }
        
        return $value;
    }

    /**
     * Check if running in production
     */
    public static function isProduction()
    {
        return self::get('APP_ENV') === 'production';
    }

    /**
     * Check if running in development
     */
    public static function isDevelopment()
    {
        return self::get('APP_ENV') === 'development';
    }

    /**
     * Get environment variable (supports both getenv and $_ENV)
     */
    private static function getenv($key)
    {
        // Try $_ENV first
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Try $_SERVER
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        // Try getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return false;
    }
}
