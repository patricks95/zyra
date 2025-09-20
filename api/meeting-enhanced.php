<?php
require_once '../database/connection.php';

header('Content-Type: application/json');

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        handleCreateMeeting();
        break;
    case 'GET':
        handleGetMeeting();
        break;
    case 'PUT':
        handleUpdateMeeting();
        break;
    case 'DELETE':
        handleDeleteMeeting();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function handleCreateMeeting() {
    global $meetingManager;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $meetingId = isset($input['meetingId']) ? sanitizeInput($input['meetingId']) : generateMeetingId();
        $meetingName = isset($input['meetingName']) ? sanitizeInput($input['meetingName']) : 'Zyra Meeting';
        $participantName = isset($input['participantName']) ? sanitizeInput($input['participantName']) : 'User';
        $maxParticipants = isset($input['maxParticipants']) ? (int)$input['maxParticipants'] : 100;
        
        // Validate meeting ID
        if (!validateMeetingId($meetingId)) {
            throw new Exception('Invalid meeting ID format');
        }
        
        // Create meeting in database
        $meetingCreated = $meetingManager->createMeeting($meetingId, $meetingName, null, $maxParticipants);
        
        if (!$meetingCreated) {
            throw new Exception('Failed to create meeting in database');
        }
        
        // Start the meeting
        $meetingManager->startMeeting($meetingId);
        
        // Add the creator as a participant
        $meetingManager->addParticipant($meetingId, $participantName, null, null, true);
        
        // Log analytics
        $meetingManager->logAnalytics('meeting_created', [
            'meeting_id' => $meetingId,
            'meeting_name' => $meetingName,
            'max_participants' => $maxParticipants
        ], null, null, $_SERVER['REMOTE_ADDR'] ?? '');
        
        // Get meeting details
        $meeting = $meetingManager->getMeeting($meetingId);
        
        echo json_encode([
            'success' => true,
            'meeting' => $meeting,
            'token' => VIDEOSDK_TOKEN,
            'apiKey' => VIDEOSDK_API_KEY
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function handleGetMeeting() {
    global $meetingManager;
    
    try {
        $meetingId = isset($_GET['meetingId']) ? sanitizeInput($_GET['meetingId']) : null;
        
        if (!$meetingId) {
            throw new Exception('Meeting ID is required');
        }
        
        if (!validateMeetingId($meetingId)) {
            throw new Exception('Invalid meeting ID format');
        }
        
        // Get meeting details
        $meeting = $meetingManager->getMeeting($meetingId);
        
        if (!$meeting) {
            throw new Exception('Meeting not found');
        }
        
        // Get participants
        $participants = $meetingManager->getMeetingParticipants($meetingId);
        $meeting['participants'] = $participants;
        
        echo json_encode([
            'success' => true,
            'meeting' => $meeting
        ]);
        
    } catch (Exception $e) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function handleUpdateMeeting() {
    global $meetingManager;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $meetingId = isset($input['meetingId']) ? sanitizeInput($input['meetingId']) : null;
        
        if (!$meetingId) {
            throw new Exception('Meeting ID is required');
        }
        
        if (!validateMeetingId($meetingId)) {
            throw new Exception('Invalid meeting ID format');
        }
        
        $meeting = $meetingManager->getMeeting($meetingId);
        if (!$meeting) {
            throw new Exception('Meeting not found');
        }
        
        // Handle different update types
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'join':
                    $participantName = $input['participantName'] ?? 'Participant';
                    $participantEmail = $input['participantEmail'] ?? null;
                    $meetingManager->addParticipant($meetingId, $participantName, $participantEmail);
                    break;
                    
                case 'leave':
                    $participantName = $input['participantName'] ?? 'Participant';
                    $meetingManager->removeParticipant($meetingId, $participantName);
                    break;
                    
                case 'end':
                    $meetingManager->endMeeting($meetingId);
                    break;
                    
                case 'start_recording':
                    // Update meeting to indicate recording started
                    $query = "UPDATE meetings SET is_recording = 1 WHERE meeting_id = :meeting_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':meeting_id', $meetingId);
                    $stmt->execute();
                    break;
            }
        }
        
        // Get updated meeting details
        $updatedMeeting = $meetingManager->getMeeting($meetingId);
        $participants = $meetingManager->getMeetingParticipants($meetingId);
        $updatedMeeting['participants'] = $participants;
        
        echo json_encode([
            'success' => true,
            'meeting' => $updatedMeeting
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function handleDeleteMeeting() {
    global $meetingManager;
    
    try {
        $meetingId = isset($_GET['meetingId']) ? sanitizeInput($_GET['meetingId']) : null;
        
        if (!$meetingId) {
            throw new Exception('Meeting ID is required');
        }
        
        if (!validateMeetingId($meetingId)) {
            throw new Exception('Invalid meeting ID format');
        }
        
        $meeting = $meetingManager->getMeeting($meetingId);
        if (!$meeting) {
            throw new Exception('Meeting not found');
        }
        
        // End the meeting first
        $meetingManager->endMeeting($meetingId);
        
        // Log analytics
        $meetingManager->logAnalytics('meeting_deleted', [
            'meeting_id' => $meetingId,
            'duration' => $meeting['duration_minutes']
        ], null, null, $_SERVER['REMOTE_ADDR'] ?? '');
        
        echo json_encode([
            'success' => true,
            'message' => 'Meeting deleted successfully'
        ]);
        
    } catch (Exception $e) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

// Utility functions
function generateMeetingId() {
    return 'mtg_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8) . '_' . date('Y');
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateMeetingId($meetingId) {
    return preg_match('/^[a-zA-Z0-9_-]{8,50}$/', $meetingId);
}
?>
