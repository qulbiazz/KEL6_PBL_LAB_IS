<?php
/**
 * Environment Variable Loader
 * Simple .env file parser for PHP
 */

class EnvLoader {
    private static $loaded = false;
    private static $env = [];
    
    /**
     * Load .env file from project root
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }
        
        if (!file_exists($path)) {
            error_log("EnvLoader: .env file not found at {$path}");
            self::$loaded = true;
            return;
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
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }
                
                // Store in $_ENV, $_SERVER, and internal cache
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                self::$env[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment variable
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$env[$key] ?? $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    
    /**
     * Check if environment variable exists
     */
    public static function has($key) {
        if (!self::$loaded) {
            self::load();
        }
        
        return isset(self::$env[$key]) || isset($_ENV[$key]) || isset($_SERVER[$key]);
    }
}
