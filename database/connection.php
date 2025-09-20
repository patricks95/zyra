<?php
// Zyra Video Conferencing Database Connection
require_once '../config.php';

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
    }
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            logActivity("Database connection failed: " . $exception->getMessage());
        }
        
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
}

// Meeting Management Class
class MeetingManager {
    private $conn;
    private $table_name = "meetings";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create a new meeting
    public function createMeeting($meeting_id, $meeting_name, $created_by = null, $max_participants = 100) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (meeting_id, meeting_name, created_by, max_participants, status, start_time) 
                  VALUES (:meeting_id, :meeting_name, :created_by, :max_participants, 'scheduled', NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':meeting_id', $meeting_id);
        $stmt->bindParam(':meeting_name', $meeting_name);
        $stmt->bindParam(':created_by', $created_by);
        $stmt->bindParam(':max_participants', $max_participants);
        
        if($stmt->execute()) {
            logActivity("Meeting created: $meeting_id");
            return true;
        }
        return false;
    }
    
    // Start a meeting
    public function startMeeting($meeting_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'active', start_time = NOW() 
                  WHERE meeting_id = :meeting_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meeting_id', $meeting_id);
        
        if($stmt->execute()) {
            logActivity("Meeting started: $meeting_id");
            return true;
        }
        return false;
    }
    
    // End a meeting
    public function endMeeting($meeting_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'ended', end_time = NOW(), 
                      duration_minutes = TIMESTAMPDIFF(MINUTE, start_time, NOW())
                  WHERE meeting_id = :meeting_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meeting_id', $meeting_id);
        
        if($stmt->execute()) {
            logActivity("Meeting ended: $meeting_id");
            return true;
        }
        return false;
    }
    
    // Get meeting details
    public function getMeeting($meeting_id) {
        $query = "SELECT m.*, u.display_name as created_by_name 
                  FROM " . $this->table_name . " m 
                  LEFT JOIN users u ON m.created_by = u.id 
                  WHERE m.meeting_id = :meeting_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meeting_id', $meeting_id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Get active meetings
    public function getActiveMeetings() {
        $query = "SELECT * FROM active_meetings ORDER BY start_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Add participant to meeting
    public function addParticipant($meeting_id, $participant_name, $participant_email = null, $user_id = null, $is_host = false) {
        $meeting = $this->getMeeting($meeting_id);
        if (!$meeting) {
            return false;
        }
        
        $query = "INSERT INTO meeting_participants 
                  (meeting_id, user_id, participant_name, participant_email, is_host, join_time) 
                  VALUES (:meeting_id, :user_id, :participant_name, :participant_email, :is_host, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meeting_id', $meeting['id']);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':participant_name', $participant_name);
        $stmt->bindParam(':participant_email', $participant_email);
        $stmt->bindParam(':is_host', $is_host);
        
        if($stmt->execute()) {
            logActivity("Participant joined: $participant_name to meeting $meeting_id");
            return true;
        }
        return false;
    }
    
    // Remove participant from meeting
    public function removeParticipant($meeting_id, $participant_name) {
        $meeting = $this->getMeeting($meeting_id);
        if (!$meeting) {
            return false;
        }
        
        $query = "UPDATE meeting_participants 
                  SET leave_time = NOW(), 
                      duration_minutes = TIMESTAMPDIFF(MINUTE, join_time, NOW())
                  WHERE meeting_id = :meeting_id AND participant_name = :participant_name AND leave_time IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meeting_id', $meeting['id']);
        $stmt->bindParam(':participant_name', $participant_name);
        
        if($stmt->execute()) {
            logActivity("Participant left: $participant_name from meeting $meeting_id");
            return true;
        }
        return false;
    }
    
    // Get meeting participants
    public function getMeetingParticipants($meeting_id) {
        $meeting = $this->getMeeting($meeting_id);
        if (!$meeting) {
            return [];
        }
        
        $query = "SELECT * FROM meeting_participants 
                  WHERE meeting_id = :meeting_id 
                  ORDER BY join_time ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meeting_id', $meeting['id']);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Log analytics event
    public function logAnalytics($event_type, $event_data, $user_id = null, $meeting_id = null, $ip_address = null) {
        $query = "INSERT INTO analytics (event_type, event_data, user_id, meeting_id, ip_address, user_agent) 
                  VALUES (:event_type, :event_data, :user_id, :meeting_id, :ip_address, :user_agent)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_type', $event_type);
        $stmt->bindParam(':event_data', json_encode($event_data));
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':meeting_id', $meeting_id);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        
        return $stmt->execute();
    }
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize meeting manager
$meetingManager = new MeetingManager($db);
?>
