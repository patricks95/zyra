<?php
require_once '../config.php';

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
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $meetingId = isset($input['meetingId']) ? sanitizeInput($input['meetingId']) : generateMeetingId();
        $meetingName = isset($input['meetingName']) ? sanitizeInput($input['meetingName']) : 'Zyra Meeting';
        $participantName = isset($input['participantName']) ? sanitizeInput($input['participantName']) : 'User';
        
        // Validate meeting ID
        if (!validateMeetingId($meetingId)) {
            throw new Exception('Invalid meeting ID format');
        }
        
        // Create meeting data
        $meetingData = [
            'meetingId' => $meetingId,
            'meetingName' => $meetingName,
            'participantName' => $participantName,
            'createdAt' => date('Y-m-d H:i:s'),
            'status' => 'active',
            'participants' => [],
            'maxParticipants' => MAX_PARTICIPANTS,
            'duration' => MEETING_DURATION
        ];
        
        // Store meeting in session (in production, use database)
        $_SESSION['meetings'][$meetingId] = $meetingData;
        
        // Log activity
        logActivity("Meeting created: $meetingId by $participantName");
        
        echo json_encode([
            'success' => true,
            'meeting' => $meetingData,
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
    try {
        $meetingId = isset($_GET['meetingId']) ? sanitizeInput($_GET['meetingId']) : null;
        
        if (!$meetingId) {
            throw new Exception('Meeting ID is required');
        }
        
        if (!validateMeetingId($meetingId)) {
            throw new Exception('Invalid meeting ID format');
        }
        
        // Get meeting from session (in production, use database)
        if (isset($_SESSION['meetings'][$meetingId])) {
            $meeting = $_SESSION['meetings'][$meetingId];
            echo json_encode([
                'success' => true,
                'meeting' => $meeting
            ]);
        } else {
            throw new Exception('Meeting not found');
        }
        
    } catch (Exception $e) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function handleUpdateMeeting() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $meetingId = isset($input['meetingId']) ? sanitizeInput($input['meetingId']) : null;
        
        if (!$meetingId) {
            throw new Exception('Meeting ID is required');
        }
        
        if (!validateMeetingId($meetingId)) {
            throw new Exception('Invalid meeting ID format');
        }
        
        if (!isset($_SESSION['meetings'][$meetingId])) {
            throw new Exception('Meeting not found');
        }
        
        // Update meeting data
        $meeting = $_SESSION['meetings'][$meetingId];
        
        if (isset($input['participants'])) {
            $meeting['participants'] = $input['participants'];
        }
        
        if (isset($input['status'])) {
            $meeting['status'] = sanitizeInput($input['status']);
        }
        
        $meeting['updatedAt'] = date('Y-m-d H:i:s');
        $_SESSION['meetings'][$meetingId] = $meeting;
        
        // Log activity
        logActivity("Meeting updated: $meetingId");
        
        echo json_encode([
            'success' => true,
            'meeting' => $meeting
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
    try {
        $meetingId = isset($_GET['meetingId']) ? sanitizeInput($_GET['meetingId']) : null;
        
        if (!$meetingId) {
            throw new Exception('Meeting ID is required');
        }
        
        if (!validateMeetingId($meetingId)) {
            throw new Exception('Invalid meeting ID format');
        }
        
        if (isset($_SESSION['meetings'][$meetingId])) {
            unset($_SESSION['meetings'][$meetingId]);
            
            // Log activity
            logActivity("Meeting deleted: $meetingId");
            
            echo json_encode([
                'success' => true,
                'message' => 'Meeting deleted successfully'
            ]);
        } else {
            throw new Exception('Meeting not found');
        }
        
    } catch (Exception $e) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
?>
