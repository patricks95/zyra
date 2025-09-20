<?php
// Zyra Database Setup Script
// Run this script to set up your database with sample data

require_once 'config.php';

// Database connection parameters
$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Zyra Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #1a4d3a; text-align: center; }
        h2 { color: #2d5a27; border-bottom: 2px solid #1a4d3a; padding-bottom: 5px; }
        .btn { background: #1a4d3a; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #2d5a27; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>";

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🎥 Zyra Video Conferencing - Database Setup</h1>";
    echo "<div class='info'>Setting up database and sample data...</div>";
    
    // Use the database
    $pdo->exec("USE `$dbname`");
    echo "<div class='success'>✅ Connected to database: $dbname</div>";
    
    // Create tables
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            display_name VARCHAR(100) NOT NULL,
            avatar_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE
        )",
        
        'meetings' => "CREATE TABLE IF NOT EXISTS meetings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            meeting_id VARCHAR(50) UNIQUE NOT NULL,
            meeting_name VARCHAR(255) NOT NULL,
            created_by INT,
            status ENUM('scheduled', 'active', 'ended', 'cancelled') DEFAULT 'scheduled',
            start_time TIMESTAMP NULL,
            end_time TIMESTAMP NULL,
            duration_minutes INT DEFAULT 0,
            max_participants INT DEFAULT 100,
            is_recording BOOLEAN DEFAULT FALSE,
            recording_url VARCHAR(500),
            meeting_password VARCHAR(50),
            is_public BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )",
        
        'meeting_participants' => "CREATE TABLE IF NOT EXISTS meeting_participants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            meeting_id INT NOT NULL,
            user_id INT,
            participant_name VARCHAR(100) NOT NULL,
            participant_email VARCHAR(100),
            join_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            leave_time TIMESTAMP NULL,
            duration_minutes INT DEFAULT 0,
            is_host BOOLEAN DEFAULT FALSE,
            is_muted BOOLEAN DEFAULT FALSE,
            is_video_on BOOLEAN DEFAULT TRUE,
            device_type VARCHAR(50),
            browser_info VARCHAR(255),
            ip_address VARCHAR(45),
            FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        
        'meeting_recordings' => "CREATE TABLE IF NOT EXISTS meeting_recordings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            meeting_id INT NOT NULL,
            recording_url VARCHAR(500) NOT NULL,
            recording_duration INT NOT NULL,
            file_size_mb DECIMAL(10,2),
            recording_quality VARCHAR(20) DEFAULT 'HD',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE
        )",
        
        'meeting_chat' => "CREATE TABLE IF NOT EXISTS meeting_chat (
            id INT AUTO_INCREMENT PRIMARY KEY,
            meeting_id INT NOT NULL,
            participant_id INT,
            message TEXT NOT NULL,
            message_type ENUM('text', 'file', 'image', 'system') DEFAULT 'text',
            file_url VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
            FOREIGN KEY (participant_id) REFERENCES meeting_participants(id) ON DELETE SET NULL
        )",
        
        'system_settings' => "CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        'analytics' => "CREATE TABLE IF NOT EXISTS analytics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50) NOT NULL,
            event_data JSON,
            user_id INT,
            meeting_id INT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE SET NULL
        )"
    ];
    
    $createdTables = 0;
    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            echo "<div class='success'>✅ Table '$tableName' created successfully</div>";
            $createdTables++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "<div class='warning'>⚠️ Table '$tableName' already exists</div>";
                $createdTables++;
            } else {
                echo "<div class='error'>❌ Error creating table '$tableName': " . $e->getMessage() . "</div>";
            }
        }
    }
    
    // Insert sample data
    echo "<h2>📊 Inserting Sample Data</h2>";
    
    // Insert sample users
    $users = [
        ['admin', 'admin@zyra.com', 'Administrator', 'https://via.placeholder.com/150/1a4d3a/ffffff?text=A'],
        ['john_doe', 'john@example.com', 'John Doe', 'https://via.placeholder.com/150/2d5a27/ffffff?text=J'],
        ['jane_smith', 'jane@example.com', 'Jane Smith', 'https://via.placeholder.com/150/4a4a1a/ffffff?text=J'],
        ['mike_wilson', 'mike@example.com', 'Mike Wilson', 'https://via.placeholder.com/150/5d1a1a/ffffff?text=M'],
        ['sarah_jones', 'sarah@example.com', 'Sarah Jones', 'https://via.placeholder.com/150/1a4d3a/ffffff?text=S']
    ];
    
    $userStmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, display_name, avatar_url, last_login) VALUES (?, ?, ?, ?, NOW())");
    foreach ($users as $user) {
        $userStmt->execute($user);
    }
    echo "<div class='success'>✅ Sample users inserted</div>";
    
    // Insert sample meetings
    $meetings = [
        ['mtg_001_2024', 'Weekly Team Standup', 1, 'ended', '2024-01-15 09:00:00', '2024-01-15 09:30:00', 30, 10, TRUE, 'standup123'],
        ['mtg_002_2024', 'Project Planning Session', 2, 'active', '2024-01-15 14:00:00', NULL, 0, 15, FALSE, 'planning456'],
        ['mtg_003_2024', 'Client Presentation', 1, 'scheduled', '2024-01-16 10:00:00', NULL, 0, 25, TRUE, 'client789'],
        ['mtg_004_2024', 'Training Workshop', 3, 'ended', '2024-01-14 13:00:00', '2024-01-14 15:00:00', 120, 50, TRUE, 'training101'],
        ['mtg_005_2024', 'Quick Sync Meeting', 4, 'cancelled', '2024-01-15 16:00:00', NULL, 0, 5, FALSE, 'sync2024']
    ];
    
    $meetingStmt = $pdo->prepare("INSERT IGNORE INTO meetings (meeting_id, meeting_name, created_by, status, start_time, end_time, duration_minutes, max_participants, is_recording, meeting_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($meetings as $meeting) {
        $meetingStmt->execute($meeting);
    }
    echo "<div class='success'>✅ Sample meetings inserted</div>";
    
    // Insert system settings
    $settings = [
        ['app_name', 'Zyra Video Conferencing', 'string', 'Application name'],
        ['max_meeting_duration', '1440', 'number', 'Maximum meeting duration in minutes'],
        ['default_max_participants', '100', 'number', 'Default maximum participants per meeting'],
        ['recording_enabled', 'true', 'boolean', 'Enable meeting recordings'],
        ['public_meetings_enabled', 'true', 'boolean', 'Allow public meetings without password'],
        ['require_registration', 'false', 'boolean', 'Require user registration to create meetings'],
        ['timezone', 'UTC', 'string', 'Default timezone for meetings'],
        ['video_quality', 'HD', 'string', 'Default video quality'],
        ['audio_quality', 'high', 'string', 'Default audio quality']
    ];
    
    $settingStmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
    foreach ($settings as $setting) {
        $settingStmt->execute($setting);
    }
    echo "<div class='success'>✅ System settings inserted</div>";
    
    // Create indexes
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_meetings_meeting_id ON meetings(meeting_id)",
        "CREATE INDEX IF NOT EXISTS idx_meetings_status ON meetings(status)",
        "CREATE INDEX IF NOT EXISTS idx_meetings_created_at ON meetings(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_participants_meeting_id ON meeting_participants(meeting_id)",
        "CREATE INDEX IF NOT EXISTS idx_participants_user_id ON meeting_participants(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_chat_meeting_id ON meeting_chat(meeting_id)",
        "CREATE INDEX IF NOT EXISTS idx_analytics_event_type ON analytics(event_type)",
        "CREATE INDEX IF NOT EXISTS idx_analytics_created_at ON analytics(created_at)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $pdo->exec($index);
        } catch (PDOException $e) {
            // Index might already exist, ignore error
        }
    }
    echo "<div class='success'>✅ Database indexes created</div>";
    
    // Test database
    $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $meetingCount = $pdo->query("SELECT COUNT(*) as count FROM meetings")->fetch()['count'];
    $settingCount = $pdo->query("SELECT COUNT(*) as count FROM system_settings")->fetch()['count'];
    
    echo "<h2>📊 Database Summary</h2>";
    echo "<div class='info'>";
    echo "👥 Users: $userCount<br>";
    echo "🎥 Meetings: $meetingCount<br>";
    echo "⚙️ Settings: $settingCount<br>";
    echo "📋 Tables: $createdTables<br>";
    echo "</div>";
    
    echo "<h2>🎉 Database Setup Complete!</h2>";
    echo "<div class='success'>Your Zyra video conferencing database is ready to use!</div>";
    
    echo "<h2>🔗 Next Steps</h2>";
    echo "<div class='info'>";
    echo "1. Your database is now configured and ready<br>";
    echo "2. The application will automatically use the database for enhanced features<br>";
    echo "3. You can now track meetings, participants, and analytics<br>";
    echo "4. Test the application at: <a href='index.php'>https://lightgreen-herring-298821.hostingersite.com</a><br>";
    echo "</div>";
    
    echo "<h2>🛠️ Available Features</h2>";
    echo "<div class='info'>";
    echo "✅ Meeting creation and management<br>";
    echo "✅ Participant tracking<br>";
    echo "✅ Meeting history<br>";
    echo "✅ Analytics and reporting<br>";
    echo "✅ System configuration<br>";
    echo "✅ User management (optional)<br>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Setup Failed</h2>";
    echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>Please check your database configuration in config.php</div>";
    echo "<div class='info'>Make sure your hosting provider allows database connections and the credentials are correct.</div>";
}

echo "</div></body></html>";
?>
