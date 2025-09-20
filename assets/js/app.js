// Zyra Video Conferencing Application JavaScript

class ZyraApp {
    constructor() {
        this.apiKey = '264a55c3-2c83-4f43-b36a-002d37fbcf1e';
        this.secretKey = '67d52398243cbf43d0c5a8c15fe515dad43d40cabcf6951ec7f34a8d2aef2ed9';
        this.token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiIyNjRhNTVjMy0yYzgzLTRmNDMtYjM2YS0wMDJkMzdmYmNmMWUiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODM2MTkwMCwiZXhwIjoxNzU4OTY2NzAwfQ.tQLdxahiITv6Cnb82y61oQDNF9SjcfGRe4wqNIn1P_o';
        
        this.meeting = null;
        this.localStream = null;
        this.isMuted = false;
        this.isVideoOff = false;
        this.isScreenSharing = false;
        this.participants = new Map();
        
        this.init();
    }
    
    init() {
        console.log('Initializing Zyra Video Conferencing App...');
        
        // Wait for VideoSDK to load
        this.waitForVideoSDK();
        
        // Bind event listeners
        this.bindEventListeners();
        
        // Initialize UI
        this.initializeUI();
    }
    
    waitForVideoSDK() {
        const maxAttempts = 100; // 10 seconds max
        let attempts = 0;
        
        const checkVideoSDK = () => {
            attempts++;
            
            if (typeof VideoSDK !== 'undefined') {
                try {
                    VideoSDK.config(this.token);
                    console.log('VideoSDK initialized successfully');
                    this.showNotification('VideoSDK loaded successfully!', 'success');
                } catch (error) {
                    console.error('Error initializing VideoSDK:', error);
                    this.showNotification('Error initializing VideoSDK. Please refresh the page.', 'error');
                }
            } else if (attempts < maxAttempts) {
                setTimeout(checkVideoSDK, 100); // Check every 100ms
            } else {
                console.error('VideoSDK failed to load after maximum attempts');
                this.showNotification('VideoSDK is taking longer than expected to load. Please check your internet connection.', 'warning');
            }
        };
        
        checkVideoSDK();
    }
    
    bindEventListeners() {
        // Create meeting button
        const createBtn = document.querySelector('[onclick="createMeeting()"]');
        if (createBtn) {
            createBtn.addEventListener('click', () => this.createMeeting());
        }
        
        // Join meeting button
        const joinBtn = document.querySelector('[onclick="joinMeeting()"]');
        if (joinBtn) {
            joinBtn.addEventListener('click', () => this.joinMeeting());
        }
        
        // Meeting controls
        const muteBtn = document.getElementById('muteBtn');
        if (muteBtn) {
            muteBtn.addEventListener('click', () => this.toggleMute());
        }
        
        const videoBtn = document.getElementById('videoBtn');
        if (videoBtn) {
            videoBtn.addEventListener('click', () => this.toggleVideo());
        }
        
        const screenShareBtn = document.getElementById('screenShareBtn');
        if (screenShareBtn) {
            screenShareBtn.addEventListener('click', () => this.toggleScreenShare());
        }
        
        // Close meeting modal
        const closeBtn = document.querySelector('[onclick="closeMeeting()"]');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeMeeting());
        }
        
        // Leave meeting button
        const leaveBtn = document.querySelector('[onclick="leaveMeeting()"]');
        if (leaveBtn) {
            leaveBtn.addEventListener('click', () => this.leaveMeeting());
        }
        
        // Enter key for meeting ID input
        const meetingIdInput = document.getElementById('meetingId');
        if (meetingIdInput) {
            meetingIdInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.joinMeeting();
                }
            });
        }
    }
    
    initializeUI() {
        // Add loading states
        this.addLoadingStates();
        
        // Initialize animations
        this.initializeAnimations();
        
        // Check browser compatibility
        this.checkBrowserCompatibility();
    }
    
    addLoadingStates() {
        const buttons = document.querySelectorAll('button');
        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.classList.contains('loading')) {
                    this.classList.add('loading');
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
                    
                    setTimeout(() => {
                        this.classList.remove('loading');
                        this.innerHTML = originalText;
                    }, 2000);
                }
            });
        });
    }
    
    initializeAnimations() {
        // Add staggered animation to cards
        const cards = document.querySelectorAll('.glass-effect');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.2}s`;
        });
    }
    
    checkBrowserCompatibility() {
        const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
        const isFirefox = /Firefox/.test(navigator.userAgent);
        const isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
        const isEdge = /Edg/.test(navigator.userAgent);
        
        if (!isChrome && !isFirefox && !isSafari && !isEdge) {
            this.showNotification('For best experience, please use Chrome, Firefox, Safari, or Edge browser.', 'warning');
        }
        
        // Check for required APIs
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            this.showNotification('Your browser does not support video calling. Please use a modern browser.', 'error');
        }
    }
    
    createMeeting() {
        try {
            const meetingId = this.generateMeetingId();
            this.joinMeetingWithId(meetingId);
        } catch (error) {
            console.error('Error creating meeting:', error);
            this.showNotification('Failed to create meeting. Please try again.', 'error');
        }
    }
    
    joinMeeting() {
        const meetingId = document.getElementById('meetingId').value.trim();
        if (!meetingId) {
            this.showNotification('Please enter a meeting ID', 'warning');
            return;
        }
        
        if (!this.validateMeetingId(meetingId)) {
            this.showNotification('Invalid meeting ID format', 'error');
            return;
        }
        
        this.joinMeetingWithId(meetingId);
    }
    
    joinMeetingWithId(meetingId) {
        try {
            if (typeof VideoSDK === 'undefined') {
                this.showNotification('VideoSDK is still loading. Please wait a moment and try again.', 'warning');
                // Retry after a short delay
                setTimeout(() => {
                    this.joinMeetingWithId(meetingId);
                }, 2000);
                return;
            }
            
            this.meeting = VideoSDK.initMeeting({
                meetingId: meetingId,
                name: 'User',
                micEnabled: true,
                webcamEnabled: true,
                maxResolution: 'hd'
            });
            
            this.setupMeetingEventListeners();
            this.meeting.join();
            
        } catch (error) {
            console.error('Error joining meeting:', error);
            this.showNotification('Failed to join meeting. Please check your connection.', 'error');
        }
    }
    
    setupMeetingEventListeners() {
        this.meeting.on('meeting-joined', () => {
            console.log('Meeting joined successfully');
            this.showMeetingModal();
            this.setupLocalVideo();
            this.showNotification('Successfully joined meeting!', 'success');
        });
        
        this.meeting.on('meeting-left', () => {
            console.log('Meeting left');
            this.closeMeeting();
            this.showNotification('Left meeting successfully', 'success');
        });
        
        this.meeting.on('participant-joined', (participant) => {
            console.log('Participant joined:', participant);
            this.participants.set(participant.id, participant);
            this.updateParticipantCount();
            this.setupRemoteVideo(participant);
            this.showNotification(`${participant.name} joined the meeting`, 'success');
        });
        
        this.meeting.on('participant-left', (participant) => {
            console.log('Participant left:', participant);
            this.participants.delete(participant.id);
            this.updateParticipantCount();
            this.showNotification(`${participant.name} left the meeting`, 'warning');
        });
        
        this.meeting.on('stream-changed', (data) => {
            console.log('Stream changed:', data);
            if (data.stream && data.stream.kind === 'video') {
                this.setupRemoteVideo(data.participant);
            }
        });
        
        this.meeting.on('error', (error) => {
            console.error('Meeting error:', error);
            this.showNotification('Meeting error occurred. Please try again.', 'error');
        });
    }
    
    setupLocalVideo() {
        const localVideo = document.getElementById('localVideo');
        if (localVideo && this.meeting) {
            this.meeting.getLocalVideoStream().then((stream) => {
                this.localStream = stream;
                localVideo.srcObject = stream;
            }).catch(error => {
                console.error('Error getting local video stream:', error);
                this.showNotification('Failed to access camera. Please check permissions.', 'error');
            });
        }
    }
    
    setupRemoteVideo(participant) {
        const remoteVideo = document.getElementById('remoteVideo');
        if (remoteVideo && participant.streams) {
            const videoStream = participant.streams.find(stream => stream.kind === 'video');
            if (videoStream) {
                remoteVideo.srcObject = videoStream.track;
            }
        }
    }
    
    toggleMute() {
        if (!this.meeting) return;
        
        try {
            if (this.isMuted) {
                this.meeting.unmuteMic();
                this.updateMuteButton(false);
            } else {
                this.meeting.muteMic();
                this.updateMuteButton(true);
            }
            this.isMuted = !this.isMuted;
        } catch (error) {
            console.error('Error toggling mute:', error);
            this.showNotification('Failed to toggle microphone', 'error');
        }
    }
    
    toggleVideo() {
        if (!this.meeting) return;
        
        try {
            if (this.isVideoOff) {
                this.meeting.enableWebcam();
                this.updateVideoButton(false);
            } else {
                this.meeting.disableWebcam();
                this.updateVideoButton(true);
            }
            this.isVideoOff = !this.isVideoOff;
        } catch (error) {
            console.error('Error toggling video:', error);
            this.showNotification('Failed to toggle camera', 'error');
        }
    }
    
    toggleScreenShare() {
        if (!this.meeting) return;
        
        try {
            if (this.isScreenSharing) {
                this.meeting.stopScreenShare();
                this.updateScreenShareButton(false);
            } else {
                this.meeting.startScreenShare();
                this.updateScreenShareButton(true);
            }
            this.isScreenSharing = !this.isScreenSharing;
        } catch (error) {
            console.error('Error toggling screen share:', error);
            this.showNotification('Failed to toggle screen share', 'error');
        }
    }
    
    updateMuteButton(isMuted) {
        const muteBtn = document.getElementById('muteBtn');
        if (muteBtn) {
            if (isMuted) {
                muteBtn.innerHTML = '<i class="fas fa-microphone-slash mr-2"></i>Mute';
                muteBtn.className = 'w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition control-btn';
            } else {
                muteBtn.innerHTML = '<i class="fas fa-microphone mr-2"></i>Unmute';
                muteBtn.className = 'w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition control-btn';
            }
        }
    }
    
    updateVideoButton(isVideoOff) {
        const videoBtn = document.getElementById('videoBtn');
        if (videoBtn) {
            if (isVideoOff) {
                videoBtn.innerHTML = '<i class="fas fa-video-slash mr-2"></i>Video Off';
                videoBtn.className = 'w-full bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition control-btn';
            } else {
                videoBtn.innerHTML = '<i class="fas fa-video mr-2"></i>Video On';
                videoBtn.className = 'w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition control-btn';
            }
        }
    }
    
    updateScreenShareButton(isScreenSharing) {
        const screenShareBtn = document.getElementById('screenShareBtn');
        if (screenShareBtn) {
            if (isScreenSharing) {
                screenShareBtn.innerHTML = '<i class="fas fa-desktop mr-2"></i>Share Screen';
                screenShareBtn.className = 'w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition control-btn';
            } else {
                screenShareBtn.innerHTML = '<i class="fas fa-stop mr-2"></i>Stop Share';
                screenShareBtn.className = 'w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition control-btn';
            }
        }
    }
    
    showMeetingModal() {
        const modal = document.getElementById('meetingModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }
    
    closeMeeting() {
        const modal = document.getElementById('meetingModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        if (this.meeting) {
            this.meeting.leave();
            this.meeting = null;
        }
        
        this.resetMeetingState();
    }
    
    leaveMeeting() {
        if (this.meeting) {
            this.meeting.leave();
        }
    }
    
    resetMeetingState() {
        this.isMuted = false;
        this.isVideoOff = false;
        this.isScreenSharing = false;
        this.participants.clear();
        
        // Reset UI
        this.updateMuteButton(false);
        this.updateVideoButton(false);
        this.updateScreenShareButton(false);
        
        // Clear video streams
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        if (localVideo) localVideo.srcObject = null;
        if (remoteVideo) remoteVideo.srcObject = null;
    }
    
    updateParticipantCount() {
        const countElement = document.getElementById('participantCount');
        if (countElement) {
            const count = this.participants.size + 1; // +1 for local participant
            countElement.textContent = count;
        }
    }
    
    generateMeetingId() {
        return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    }
    
    validateMeetingId(meetingId) {
        return /^[a-zA-Z0-9]{8,20}$/.test(meetingId);
    }
    
    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${this.getNotificationIcon(type)} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Global functions for backward compatibility
function createMeeting() {
    if (window.zyraApp) {
        window.zyraApp.createMeeting();
    }
}

function joinMeeting() {
    if (window.zyraApp) {
        window.zyraApp.joinMeeting();
    }
}

function toggleMute() {
    if (window.zyraApp) {
        window.zyraApp.toggleMute();
    }
}

function toggleVideo() {
    if (window.zyraApp) {
        window.zyraApp.toggleVideo();
    }
}

function toggleScreenShare() {
    if (window.zyraApp) {
        window.zyraApp.toggleScreenShare();
    }
}

function closeMeeting() {
    if (window.zyraApp) {
        window.zyraApp.closeMeeting();
    }
}

function leaveMeeting() {
    if (window.zyraApp) {
        window.zyraApp.leaveMeeting();
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.zyraApp = new ZyraApp();
});
