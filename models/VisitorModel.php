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
        
        // Check if visitor exists in current session (within last 30 minutes)
        $check_query = "SELECT id, visited_at FROM visitors WHERE ip_address = :ip 
                       AND visited_at > NOW() - INTERVAL '30 minutes' 
                       ORDER BY visited_at DESC LIMIT 1";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':ip', $ip);
        $check_stmt->execute();
        
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing record to keep visitor "online"
            $update_query = "UPDATE visitors SET visited_at = NOW(), page_url = :page_url 
                           WHERE id = :id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':page_url', $page_url);
            $update_stmt->bindParam(':id', $existing['id']);
            $update_stmt->execute();
        } else {
            // Insert new visitor record
            $insert_query = "INSERT INTO visitors (ip_address, user_agent, country, country_code, page_url, visited_at) 
                            VALUES (:ip, :user_agent, :country, :country_code, :page_url, NOW())";
            $insert_stmt = $this->conn->prepare($insert_query);
            $insert_stmt->bindParam(':ip', $ip);
            $insert_stmt->bindParam(':user_agent', $user_agent);
            $insert_stmt->bindParam(':country', $country['name']);
            $insert_stmt->bindParam(':country_code', $country['code']);
            $insert_stmt->bindParam(':page_url', $page_url);
            $insert_stmt->execute();
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
