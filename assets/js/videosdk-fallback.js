// VideoSDK Fallback Implementation
// This provides basic functionality when VideoSDK CDN fails to load

window.VideoSDK = {
    config: function(token) {
        console.log('VideoSDK Fallback: Config called with token');
        this.token = token;
    },
    
    initMeeting: function(options) {
        console.log('VideoSDK Fallback: initMeeting called with options:', options);
        
        const meeting = {
            meetingId: options.meetingId,
            name: options.name,
            micEnabled: options.micEnabled || true,
            webcamEnabled: options.webcamEnabled || true,
            participants: {},
            events: {},
            
            join: function() {
                console.log('VideoSDK Fallback: Joining meeting...');
                this.triggerEvent('meeting-joined');
            },
            
            leave: function() {
                console.log('VideoSDK Fallback: Leaving meeting...');
                this.triggerEvent('meeting-left');
            },
            
            on: function(event, callback) {
                if (!this.events[event]) {
                    this.events[event] = [];
                }
                this.events[event].push(callback);
            },
            
            triggerEvent: function(event, data) {
                if (this.events[event]) {
                    this.events[event].forEach(callback => {
                        try {
                            callback(data);
                        } catch (error) {
                            console.error('Error in event callback:', error);
                        }
                    });
                }
            },
            
            muteMic: function() {
                console.log('VideoSDK Fallback: Muting microphone');
            },
            
            unmuteMic: function() {
                console.log('VideoSDK Fallback: Unmuting microphone');
            },
            
            enableWebcam: function() {
                console.log('VideoSDK Fallback: Enabling webcam');
            },
            
            disableWebcam: function() {
                console.log('VideoSDK Fallback: Disabling webcam');
            },
            
            startScreenShare: function() {
                console.log('VideoSDK Fallback: Starting screen share');
            },
            
            stopScreenShare: function() {
                console.log('VideoSDK Fallback: Stopping screen share');
            },
            
            getLocalVideoStream: function() {
                return new Promise((resolve, reject) => {
                    // Try to get user media
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ 
                            video: true, 
                            audio: true 
                        }).then(stream => {
                            console.log('VideoSDK Fallback: Got local media stream');
                            resolve(stream);
                        }).catch(error => {
                            console.error('VideoSDK Fallback: Error getting media stream:', error);
                            reject(error);
                        });
                    } else {
                        reject(new Error('getUserMedia not supported'));
                    }
                });
            }
        };
        
        return meeting;
    }
};

console.log('VideoSDK Fallback loaded - using local implementation');
