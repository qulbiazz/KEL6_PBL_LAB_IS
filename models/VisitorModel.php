<?php
/**
 * VisitorModel - Handle visitor tracking and statistics
 */

class VisitorModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Track visitor and return visitor statistics
     */
    public function trackVisitor()
    {
        // Get visitor information
        $ip = $this->getVisitorIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $country = $this->getCountryFromIP($ip);
        $page_url = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Use INSERT ON CONFLICT to handle duplicate keys (PostgreSQL UPSERT)
        // This will insert new record or update if IP exists within last 30 minutes
        $query = "
            WITH recent_visitor AS (
                SELECT id FROM visitors 
                WHERE ip_address = :ip 
                AND visited_at > NOW() - INTERVAL '30 minutes'
                ORDER BY visited_at DESC 
                LIMIT 1
            )
            INSERT INTO visitors (ip_address, user_agent, country, country_code, page_url, visited_at)
            SELECT :ip, :user_agent, :country, :country_code, :page_url, NOW()
            WHERE NOT EXISTS (SELECT 1 FROM recent_visitor)
            RETURNING id
        ";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':ip', $ip);
            $stmt->bindParam(':user_agent', $user_agent);
            $stmt->bindParam(':country', $country['name']);
            $stmt->bindParam(':country_code', $country['code']);
            $stmt->bindParam(':page_url', $page_url);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If no new record inserted, update existing one
            if (!$result) {
                $update_query = "
                    UPDATE visitors 
                    SET visited_at = NOW(), page_url = :page_url 
                    WHERE ip_address = :ip 
                    AND visited_at > NOW() - INTERVAL '30 minutes'
                ";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(':page_url', $page_url);
                $update_stmt->bindParam(':ip', $ip);
                $update_stmt->execute();
            }
        } catch (PDOException $e) {
            // Log error but don't break the application
            error_log("Visitor tracking error: " . $e->getMessage());
        }
        
        return $this->getVisitorStats();
    }

    /**
     * Get visitor statistics
     */
    public function getVisitorStats()
    {
        $stats = [];
        
        // Total unique visitors
        $total_query = "SELECT COUNT(DISTINCT ip_address) as total FROM visitors";
        $total_stmt = $this->conn->prepare($total_query);
        $total_stmt->execute();
        $stats['total'] = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Today's visitors
        $today_query = "SELECT COUNT(DISTINCT ip_address) as today 
                       FROM visitors 
                       WHERE DATE(visited_at) = CURRENT_DATE";
        $today_stmt = $this->conn->prepare($today_query);
        $today_stmt->execute();
        $stats['today'] = $today_stmt->fetch(PDO::FETCH_ASSOC)['today'];
        
        // Online now (last 5 minutes) - count most recent activity per IP
        $online_query = "SELECT COUNT(DISTINCT ip_address) as online 
                        FROM (
                            SELECT ip_address, MAX(visited_at) as last_visit
                            FROM visitors 
                            GROUP BY ip_address
                        ) as latest_visits
                        WHERE last_visit > NOW() - INTERVAL '5 minutes'";
        $online_stmt = $this->conn->prepare($online_query);
        $online_stmt->execute();
        $stats['online'] = $online_stmt->fetch(PDO::FETCH_ASSOC)['online'];
        
        // Top countries (limit 10)
        $countries_query = "SELECT country, country_code, COUNT(DISTINCT ip_address) as count 
                           FROM visitors 
                           WHERE country IS NOT NULL AND country != 'Unknown'
                           GROUP BY country, country_code 
                           ORDER BY count DESC 
                           LIMIT 10";
        $countries_stmt = $this->conn->prepare($countries_query);
        $countries_stmt->execute();
        $stats['countries'] = $countries_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Total page views
        $views_query = "SELECT COUNT(*) as views FROM visitors";
        $views_stmt = $this->conn->prepare($views_query);
        $views_stmt->execute();
        $stats['total_views'] = $views_stmt->fetch(PDO::FETCH_ASSOC)['views'];
        
        return $stats;
    }

    /**
     * Get visitor IP address
     */
    private function getVisitorIP()
    {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        return $ip;
    }

    /**
     * Get country from IP address using free API
     */
    private function getCountryFromIP($ip)
    {
        // Skip for local IPs
        if ($ip == '127.0.0.1' || $ip == '::1' || strpos($ip, '192.168.') === 0) {
            return ['name' => 'Local', 'code' => 'LOCAL'];
        }
        
        try {
            // Use ip-api.com free API (no key required, 45 req/min limit)
            $url = "http://ip-api.com/json/{$ip}?fields=country,countryCode";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['country']) && isset($data['countryCode'])) {
                    return [
                        'name' => $data['country'],
                        'code' => $data['countryCode']
                    ];
                }
            }
        } catch (Exception $e) {
            // Fail silently
        }
        
        return ['name' => 'Unknown', 'code' => 'XX'];
    }

    /**
     * Get country flag emoji
     */
    public static function getCountryFlag($countryCode)
    {
        if ($countryCode == 'LOCAL' || $countryCode == 'XX') {
            return '🌐';
        }
        
        $countryCode = strtoupper($countryCode);
        $flag = '';
        
        // Convert country code to flag emoji
        for ($i = 0; $i < strlen($countryCode); $i++) {
            $flag .= mb_chr(ord($countryCode[$i]) + 127397, 'UTF-8');
        }
        
        return $flag;
    }
}
