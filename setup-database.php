<?php
// Zyra Database Setup Script
// Run this script to set up the database with sample data

require_once 'config.php';

// Database connection parameters
$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🎥 Zyra Video Conferencing - Database Setup</h2>";
    echo "<p>Setting up database and sample data...</p>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$dbname' created/verified<br>";
    
    // Use the database
    $pdo->exec("USE `$dbname`");
    
    // Read and execute SQL file
    $sqlFile = 'database/zyra_database.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement) && !preg_match('/^\/\*/', $statement)) {
                try {
                    $pdo->exec($statement);
                    $successCount++;
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "⚠️ Warning: " . $e->getMessage() . "<br>";
                        $errorCount++;
                    }
                }
            }
        }
        
        echo "✅ Executed $successCount SQL statements successfully<br>";
        if ($errorCount > 0) {
            echo "⚠️ $errorCount statements had warnings (usually table already exists)<br>";
        }
    } else {
        echo "❌ SQL file not found: $sqlFile<br>";
    }
    
    // Test database connection
    $testQuery = "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '$dbname'";
    $result = $pdo->query($testQuery)->fetch();
    echo "✅ Database contains {$result['table_count']} tables<br>";
    
    // Test sample data
    $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $meetingCount = $pdo->query("SELECT COUNT(*) as count FROM meetings")->fetch()['count'];
    $participantCount = $pdo->query("SELECT COUNT(*) as count FROM meeting_participants")->fetch()['count'];
    
    echo "<h3>📊 Sample Data Loaded:</h3>";
    echo "👥 Users: $userCount<br>";
    echo "🎥 Meetings: $meetingCount<br>";
    echo "👤 Participants: $participantCount<br>";
    
    echo "<h3>🎉 Database Setup Complete!</h3>";
    echo "<p>Your Zyra video conferencing database is ready to use.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Update your application to use the enhanced API endpoints</li>";
    echo "<li>Test meeting creation and management features</li>";
    echo "<li>View analytics and meeting history</li>";
    echo "</ul>";
    
    echo "<h3>🔗 Available API Endpoints:</h3>";
    echo "<ul>";
    echo "<li><code>POST /api/meeting-enhanced.php</code> - Create meeting with database tracking</li>";
    echo "<li><code>GET /api/meeting-enhanced.php?meetingId=xxx</code> - Get meeting details</li>";
    echo "<li><code>PUT /api/meeting-enhanced.php</code> - Update meeting (join/leave/end)</li>";
    echo "<li><code>DELETE /api/meeting-enhanced.php?meetingId=xxx</code> - Delete meeting</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Setup Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in config.php</p>";
    echo "<p>Make sure MySQL is running and the credentials are correct.</p>";
}

// Display current configuration
echo "<h3>⚙️ Current Configuration:</h3>";
echo "<ul>";
echo "<li>Host: " . DB_HOST . "</li>";
echo "<li>Database: " . DB_NAME . "</li>";
echo "<li>Username: " . DB_USER . "</li>";
echo "<li>Password: " . (DB_PASS ? '***' : 'Not set') . "</li>";
echo "</ul>";
?>
