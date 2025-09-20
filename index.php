<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zyra - Video Conferencing</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🎥%3C/text%3E%3C/svg%3E">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Error handling for VideoSDK loading - must be defined before VideoSDK script
        function handleVideoSDKError() {
            console.error('Failed to load VideoSDK from CDN, trying alternative...');
            // Try alternative CDN
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/@videosdk.live/js-sdk@0.3.3/dist/videosdk.js';
            script.onerror = function() {
                console.error('VideoSDK failed to load from alternative CDN');
                showVideoSDKError();
            };
            script.onload = function() {
                console.log('VideoSDK loaded from alternative CDN');
                // Reinitialize the app
                if (window.zyraApp) {
                    window.zyraApp.init();
                }
            };
            document.head.appendChild(script);
        }
        
        function showVideoSDKError() {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg z-50';
            errorDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <div>
                        <strong>VideoSDK Error</strong><br>
                        <small>Please refresh the page or check your internet connection</small>
                    </div>
                </div>
            `;
            document.body.appendChild(errorDiv);
            
            // Auto remove after 10 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 10000);
        }
    </script>
    <script src="https://sdk.videosdk.live/js-sdk/0.3.3/videosdk.js" onerror="handleVideoSDKError()"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Animated Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-4xl mx-auto px-4">
        <div class="text-center mb-12 fade-in">
            <h1 class="text-6xl font-bold text-white mb-4">
                <i class="fas fa-video text-yellow-400 mr-4"></i>Zyra
            </h1>
            <p class="text-xl text-gray-200 mb-8">Professional Video Conferencing Made Simple</p>
        </div>
        
        <div class="grid md:grid-cols-2 gap-8 mb-12">
            <!-- Create Meeting Card -->
            <div class="glass-effect rounded-2xl p-8 text-center hover:scale-105 transition-all duration-300 fade-in">
                <div class="text-6xl mb-6">
                    <i class="fas fa-plus-circle text-green-400"></i>
                </div>
                <h2 class="text-3xl font-bold text-white mb-4">Create Meeting</h2>
                <p class="text-gray-300 mb-6">Start a new video conference and invite participants</p>
                <button onclick="createMeeting()" class="btn-primary text-white px-8 py-4 rounded-full text-lg font-semibold w-full">
                    <i class="fas fa-video mr-2"></i>Start Meeting
                </button>
            </div>
            
            <!-- Join Meeting Card -->
            <div class="glass-effect rounded-2xl p-8 text-center hover:scale-105 transition-all duration-300 fade-in">
                <div class="text-6xl mb-6">
                    <i class="fas fa-sign-in-alt text-red-400"></i>
                </div>
                <h2 class="text-3xl font-bold text-white mb-4">Join Meeting</h2>
                <p class="text-gray-300 mb-6">Enter a meeting ID to join an existing conference</p>
                <div class="mb-4">
                    <input type="text" id="meetingId" placeholder="Enter Meeting ID" 
                           class="w-full px-4 py-3 rounded-lg bg-white bg-opacity-20 text-white placeholder-gray-300 border border-white border-opacity-30 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
                <button onclick="joinMeeting()" class="btn-secondary text-white px-8 py-4 rounded-full text-lg font-semibold w-full">
                    <i class="fas fa-sign-in-alt mr-2"></i>Join Meeting
                </button>
            </div>
        </div>
        
        <!-- Features Section -->
        <div class="glass-effect rounded-2xl p-8 text-center fade-in">
            <h3 class="text-2xl font-bold text-white mb-6">Why Choose Zyra?</h3>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="text-center">
                    <i class="fas fa-shield-alt text-4xl text-yellow-400 mb-4"></i>
                    <h4 class="text-lg font-semibold text-white mb-2">Secure</h4>
                    <p class="text-gray-300 text-sm">End-to-end encryption for your privacy</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-bolt text-4xl text-green-400 mb-4"></i>
                    <h4 class="text-lg font-semibold text-white mb-2">Fast</h4>
                    <p class="text-gray-300 text-sm">Low latency, high-quality video calls</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-users text-4xl text-red-400 mb-4"></i>
                    <h4 class="text-lg font-semibold text-white mb-2">Collaborative</h4>
                    <p class="text-gray-300 text-sm">Screen sharing and real-time collaboration</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Meeting Modal -->
    <div id="meetingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Meeting Room</h2>
                <button onclick="closeMeeting()" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6 mb-6">
                <div class="md:col-span-2">
                    <div id="videoContainer" class="bg-gray-900 rounded-lg h-96 mb-4 relative">
                        <video id="localVideo" autoplay muted class="w-full h-full object-cover rounded-lg"></video>
                        <div id="remoteVideoContainer" class="absolute top-4 right-4 w-48 h-36 bg-gray-800 rounded-lg overflow-hidden">
                            <video id="remoteVideo" autoplay class="w-full h-full object-cover"></video>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-gray-100 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Meeting Info</h3>
                        <p class="text-sm text-gray-600 mb-1">Meeting ID: <span id="displayMeetingId" class="font-mono"></span></p>
                        <p class="text-sm text-gray-600">Participants: <span id="participantCount">1</span></p>
                    </div>
                    
                    <div class="bg-gray-100 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Controls</h3>
                        <div class="space-y-2">
                            <button id="muteBtn" onclick="toggleMute()" class="w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
                                <i class="fas fa-microphone-slash mr-2"></i>Mute
                            </button>
                            <button id="videoBtn" onclick="toggleVideo()" class="w-full bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                                <i class="fas fa-video-slash mr-2"></i>Video Off
                            </button>
                            <button id="screenShareBtn" onclick="toggleScreenShare()" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                                <i class="fas fa-desktop mr-2"></i>Share Screen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-center space-x-4">
                <button onclick="leaveMeeting()" class="bg-red-500 text-white px-8 py-3 rounded-lg hover:bg-red-600 transition font-semibold">
                    <i class="fas fa-phone-slash mr-2"></i>Leave Meeting
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
