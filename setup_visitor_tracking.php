<?php
/**
 * Setup Visitor Tracking Database
 * Run this file once to create the visitors table
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Read SQL file
    $sql = file_get_contents('database/create_visitors_table.sql');
    
    // Execute SQL
    $db->exec($sql);
    
    echo "✅ Visitors table created successfully!\n";
    echo "📊 Visitor tracking is now enabled.\n";
    
} catch (PDOException $e) {
    echo "❌ Error creating visitors table: " . $e->getMessage() . "\n";
}
