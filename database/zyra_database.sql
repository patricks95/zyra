-- Zyra Video Conferencing Database Schema
-- Created for enhanced functionality and analytics

-- Create database (uncomment if needed)
-- CREATE DATABASE zyra_conferences;
-- USE zyra_conferences;

-- Users table (optional - for future user management)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    avatar_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Meetings table
CREATE TABLE IF NOT EXISTS meetings (
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
);

-- Meeting participants table
CREATE TABLE IF NOT EXISTS meeting_participants (
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
);

-- Meeting recordings table
CREATE TABLE IF NOT EXISTS meeting_recordings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    recording_url VARCHAR(500) NOT NULL,
    recording_duration INT NOT NULL,
    file_size_mb DECIMAL(10,2),
    recording_quality VARCHAR(20) DEFAULT 'HD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE
);

-- Meeting chat messages table
CREATE TABLE IF NOT EXISTS meeting_chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    participant_id INT,
    message TEXT NOT NULL,
    message_type ENUM('text', 'file', 'image', 'system') DEFAULT 'text',
    file_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES meeting_participants(id) ON DELETE SET NULL
);

-- System settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Analytics table
CREATE TABLE IF NOT EXISTS analytics (
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
);

-- Insert sample users
INSERT INTO users (username, email, display_name, avatar_url, last_login) VALUES
('admin', 'admin@zyra.com', 'Administrator', 'https://via.placeholder.com/150/1a4d3a/ffffff?text=A', NOW()),
('john_doe', 'john@example.com', 'John Doe', 'https://via.placeholder.com/150/2d5a27/ffffff?text=J', NOW()),
('jane_smith', 'jane@example.com', 'Jane Smith', 'https://via.placeholder.com/150/4a4a1a/ffffff?text=J', NOW()),
('mike_wilson', 'mike@example.com', 'Mike Wilson', 'https://via.placeholder.com/150/5d1a1a/ffffff?text=M', NOW()),
('sarah_jones', 'sarah@example.com', 'Sarah Jones', 'https://via.placeholder.com/150/1a4d3a/ffffff?text=S', NOW());

-- Insert sample meetings
INSERT INTO meetings (meeting_id, meeting_name, created_by, status, start_time, end_time, duration_minutes, max_participants, is_recording, meeting_password) VALUES
('mtg_001_2024', 'Weekly Team Standup', 1, 'ended', '2024-01-15 09:00:00', '2024-01-15 09:30:00', 30, 10, TRUE, 'standup123'),
('mtg_002_2024', 'Project Planning Session', 2, 'active', '2024-01-15 14:00:00', NULL, 0, 15, FALSE, 'planning456'),
('mtg_003_2024', 'Client Presentation', 1, 'scheduled', '2024-01-16 10:00:00', NULL, 0, 25, TRUE, 'client789'),
('mtg_004_2024', 'Training Workshop', 3, 'ended', '2024-01-14 13:00:00', '2024-01-14 15:00:00', 120, 50, TRUE, 'training101'),
('mtg_005_2024', 'Quick Sync Meeting', 4, 'cancelled', '2024-01-15 16:00:00', NULL, 0, 5, FALSE, 'sync2024');

-- Insert sample meeting participants
INSERT INTO meeting_participants (meeting_id, user_id, participant_name, participant_email, join_time, leave_time, duration_minutes, is_host, is_muted, is_video_on, device_type, browser_info, ip_address) VALUES
(1, 1, 'Administrator', 'admin@zyra.com', '2024-01-15 08:58:00', '2024-01-15 09:32:00', 34, TRUE, FALSE, TRUE, 'desktop', 'Chrome 120.0.0.0', '192.168.1.100'),
(1, 2, 'John Doe', 'john@example.com', '2024-01-15 08:59:00', '2024-01-15 09:31:00', 32, FALSE, FALSE, TRUE, 'desktop', 'Firefox 121.0', '192.168.1.101'),
(1, 3, 'Jane Smith', 'jane@example.com', '2024-01-15 09:00:00', '2024-01-15 09:30:00', 30, FALSE, TRUE, TRUE, 'mobile', 'Safari 17.2', '192.168.1.102'),
(1, 4, 'Mike Wilson', 'mike@example.com', '2024-01-15 09:01:00', '2024-01-15 09:29:00', 28, FALSE, FALSE, FALSE, 'desktop', 'Edge 120.0.0.0', '192.168.1.103'),
(2, 2, 'John Doe', 'john@example.com', '2024-01-15 13:58:00', NULL, 0, TRUE, FALSE, TRUE, 'desktop', 'Chrome 120.0.0.0', '192.168.1.101'),
(2, 3, 'Jane Smith', 'jane@example.com', '2024-01-15 13:59:00', NULL, 0, FALSE, FALSE, TRUE, 'mobile', 'Safari 17.2', '192.168.1.102'),
(2, 5, 'Sarah Jones', 'sarah@example.com', '2024-01-15 14:00:00', NULL, 0, FALSE, TRUE, TRUE, 'tablet', 'Chrome 120.0.0.0', '192.168.1.104'),
(4, 3, 'Jane Smith', 'jane@example.com', '2024-01-14 12:58:00', '2024-01-14 15:02:00', 124, TRUE, FALSE, TRUE, 'desktop', 'Chrome 120.0.0.0', '192.168.1.102'),
(4, 1, 'Administrator', 'admin@zyra.com', '2024-01-14 12:59:00', '2024-01-14 15:01:00', 122, FALSE, FALSE, TRUE, 'desktop', 'Firefox 121.0', '192.168.1.100'),
(4, 2, 'John Doe', 'john@example.com', '2024-01-14 13:00:00', '2024-01-14 15:00:00', 120, FALSE, TRUE, TRUE, 'mobile', 'Safari 17.2', '192.168.1.101');

-- Insert sample meeting recordings
INSERT INTO meeting_recordings (meeting_id, recording_url, recording_duration, file_size_mb, recording_quality) VALUES
(1, 'https://recordings.zyra.com/meetings/mtg_001_2024_recording.mp4', 30, 125.5, 'HD'),
(4, 'https://recordings.zyra.com/meetings/mtg_004_2024_recording.mp4', 120, 450.2, 'HD');

-- Insert sample chat messages
INSERT INTO meeting_chat (meeting_id, participant_id, message, message_type, created_at) VALUES
(1, 1, 'Welcome everyone to our weekly standup!', 'system', '2024-01-15 09:00:00'),
(1, 2, 'Good morning everyone!', 'text', '2024-01-15 09:00:15'),
(1, 3, 'Morning! Ready to start', 'text', '2024-01-15 09:00:30'),
(1, 1, 'Let\'s go around and share updates', 'text', '2024-01-15 09:01:00'),
(1, 4, 'I\'ll start with my updates...', 'text', '2024-01-15 09:01:30'),
(2, 2, 'Let\'s discuss the project timeline', 'text', '2024-01-15 14:00:00'),
(2, 3, 'I have some concerns about the deadline', 'text', '2024-01-15 14:01:00'),
(2, 5, 'Can we review the requirements?', 'text', '2024-01-15 14:02:00');

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('app_name', 'Zyra Video Conferencing', 'string', 'Application name'),
('max_meeting_duration', '1440', 'number', 'Maximum meeting duration in minutes'),
('default_max_participants', '100', 'number', 'Default maximum participants per meeting'),
('recording_enabled', 'true', 'boolean', 'Enable meeting recordings'),
('public_meetings_enabled', 'true', 'boolean', 'Allow public meetings without password'),
('require_registration', 'false', 'boolean', 'Require user registration to create meetings'),
('timezone', 'UTC', 'string', 'Default timezone for meetings'),
('video_quality', 'HD', 'string', 'Default video quality'),
('audio_quality', 'high', 'string', 'Default audio quality'),
('branding', '{"logo_url":"","primary_color":"#1a4d3a","secondary_color":"#2d5a27"}', 'json', 'Application branding settings');

-- Insert sample analytics
INSERT INTO analytics (event_type, event_data, user_id, meeting_id, ip_address, user_agent) VALUES
('meeting_created', '{"meeting_id":"mtg_001_2024","participants":4}', 1, 1, '192.168.1.100', 'Chrome 120.0.0.0'),
('meeting_joined', '{"participant_name":"John Doe","device":"desktop"}', 2, 1, '192.168.1.101', 'Firefox 121.0'),
('meeting_ended', '{"duration":30,"participants":4}', 1, 1, '192.168.1.100', 'Chrome 120.0.0.0'),
('recording_started', '{"meeting_id":"mtg_001_2024"}', 1, 1, '192.168.1.100', 'Chrome 120.0.0.0'),
('recording_stopped', '{"duration":30,"file_size":"125.5MB"}', 1, 1, '192.168.1.100', 'Chrome 120.0.0.0');

-- Create indexes for better performance
CREATE INDEX idx_meetings_meeting_id ON meetings(meeting_id);
CREATE INDEX idx_meetings_status ON meetings(status);
CREATE INDEX idx_meetings_created_at ON meetings(created_at);
CREATE INDEX idx_participants_meeting_id ON meeting_participants(meeting_id);
CREATE INDEX idx_participants_user_id ON meeting_participants(user_id);
CREATE INDEX idx_chat_meeting_id ON meeting_chat(meeting_id);
CREATE INDEX idx_analytics_event_type ON analytics(event_type);
CREATE INDEX idx_analytics_created_at ON analytics(created_at);

-- Create views for common queries
CREATE VIEW active_meetings AS
SELECT 
    m.id,
    m.meeting_id,
    m.meeting_name,
    u.display_name as created_by_name,
    m.start_time,
    COUNT(p.id) as current_participants,
    m.max_participants
FROM meetings m
LEFT JOIN users u ON m.created_by = u.id
LEFT JOIN meeting_participants p ON m.id = p.meeting_id AND p.leave_time IS NULL
WHERE m.status = 'active'
GROUP BY m.id, m.meeting_id, m.meeting_name, u.display_name, m.start_time, m.max_participants;

CREATE VIEW meeting_statistics AS
SELECT 
    m.meeting_id,
    m.meeting_name,
    m.duration_minutes,
    COUNT(DISTINCT p.id) as total_participants,
    COUNT(DISTINCT CASE WHEN p.is_host = TRUE THEN p.id END) as hosts,
    m.is_recording,
    r.recording_url
FROM meetings m
LEFT JOIN meeting_participants p ON m.id = p.meeting_id
LEFT JOIN meeting_recordings r ON m.id = r.meeting_id
WHERE m.status = 'ended'
GROUP BY m.id, m.meeting_id, m.meeting_name, m.duration_minutes, m.is_recording, r.recording_url;

-- Sample queries for testing
-- Get all active meetings
-- SELECT * FROM active_meetings;

-- Get meeting statistics
-- SELECT * FROM meeting_statistics;

-- Get user meeting history
-- SELECT m.meeting_name, m.start_time, m.duration_minutes, p.is_host 
-- FROM meetings m 
-- JOIN meeting_participants p ON m.id = p.meeting_id 
-- WHERE p.user_id = 1;

-- Get meeting participants
-- SELECT p.participant_name, p.join_time, p.leave_time, p.duration_minutes 
-- FROM meeting_participants p 
-- JOIN meetings m ON p.meeting_id = m.id 
-- WHERE m.meeting_id = 'mtg_001_2024';
