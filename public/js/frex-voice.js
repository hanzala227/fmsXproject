class FrexVoiceAssistant {
    constructor() {
        this.recognition = null;
        this.isListening = false;
        this.isProcessing = false;
        this.mode = 'idle'; // 'idle', 'wakeword', 'active'
        this.userName = (window.FrexConfig && window.FrexConfig.userName) ? window.FrexConfig.userName.split(' ')[0] : 'Boss';
        
        // Ensure browser compatibility
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            console.warn("Speech Recognition API is not supported in this browser.");
            return;
        }

        this.recognition = new SpeechRecognition();
        this.recognition.lang = 'en-US';
        this.recognition.interimResults = true; // Need interim for fast wakeword detection
        this.recognition.maxAlternatives = 1;

        this.setupUI();
        this.bindEvents();
    }

    setupUI() {
        // Create FAB
        this.fab = document.createElement('button');
        this.fab.id = 'frex-fab';
        this.fab.innerHTML = '<i class="fa fa-microphone"></i>';
        this.fab.title = "Click to activate Frex (or say 'Frexo')";
        
        // Create Overlay / Transcript Box
        this.overlay = document.createElement('div');
        this.overlay.id = 'frex-overlay';
        this.overlay.style.display = 'none';
        this.overlay.innerHTML = `
            <div class="frex-indicator">
                <i class="fa fa-circle-o-notch fa-spin frex-spinner" style="display:none;"></i>
                <div class="frex-waves">
                    <span></span><span></span><span></span>
                </div>
            </div>
            <div class="frex-text">Listening...</div>
        `;

        document.body.appendChild(this.fab);
        document.body.appendChild(this.overlay);

        // Add CSS
        const style = document.createElement('style');
        style.innerHTML = `
            #frex-fab {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                color: white;
                font-size: 24px;
                border: none;
                box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
                cursor: pointer;
                z-index: 9999;
                transition: transform 0.2s, box-shadow 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            #frex-fab:hover {
                transform: scale(1.05);
                box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6);
            }
            #frex-fab.wakeword {
                background: linear-gradient(135deg, #10b981, #059669); /* Green for background listening */
                animation: pulse-green 2s infinite;
            }
            #frex-fab.listening {
                background: linear-gradient(135deg, #ef4444, #dc2626); /* Red for active listening */
                animation: pulse-red 1.5s infinite;
            }
            #frex-overlay {
                position: fixed;
                bottom: 100px;
                right: 30px;
                width: 300px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                padding: 20px;
                z-index: 9998;
                border: 1px solid #e2e8f0;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            .frex-text {
                font-family: 'Inter', sans-serif;
                font-size: 14px;
                color: #334155;
                text-align: center;
                word-wrap: break-word;
            }
            .frex-waves {
                display: flex;
                gap: 4px;
                align-items: center;
                height: 20px;
            }
            .frex-waves span {
                display: block;
                width: 4px;
                height: 4px;
                background: #3b82f6;
                border-radius: 2px;
                animation: frexWave 1s infinite ease-in-out;
            }
            .frex-waves span:nth-child(2) { animation-delay: 0.2s; }
            .frex-waves span:nth-child(3) { animation-delay: 0.4s; }
            @keyframes pulse-red {
                0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
                70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
                100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
            }
            @keyframes pulse-green {
                0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
                70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
                100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
            }
            @keyframes frexWave {
                0%, 100% { height: 4px; }
                50% { height: 20px; }
            }
        `;
        document.head.appendChild(style);
    }

    bindEvents() {
        this.fab.addEventListener('click', () => {
            // Force reset any stuck state if user explicitly clicks
            if (this.isProcessing) {
                this.isProcessing = false;
                this.overlay.style.display = 'none';
                try { window.speechSynthesis.cancel(); } catch(e) {}
            }
            
            if (this.mode === 'active') {
                this.stopListening();
                this.mode = 'idle';
                this.updateFabStyle();
            } else if (this.mode === 'wakeword' || this.mode === 'idle') {
                // Manual click overrides wakeword and starts active listening immediately
                this.startActiveListening();
            }
        });

        // Double click to toggle continuous wakeword mode
        this.fab.addEventListener('dblclick', () => {
            if (this.mode === 'wakeword') {
                this.stopListening();
                this.mode = 'idle';
                this.speak("Wake word disabled.");
            } else {
                this.startWakewordListening();
                this.speak("Wake word activated. Just say Frexo to wake me up.");
            }
        });

        this.recognition.onstart = () => {
            this.isListening = true;
            this.updateFabStyle();
            
            if (this.mode === 'active') {
                this.overlay.style.display = 'flex';
                this.updateText("Listening...");
                this.toggleSpinner(false);
            }
        };

        this.recognition.onresult = (event) => {
            this.hasResult = true;
            let finalTranscript = '';
            let interimTranscript = '';
            
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }

            const currentSpeech = (finalTranscript + interimTranscript).toLowerCase().trim();
            
            if (this.mode === 'wakeword') {
                console.log("Frex Heard:", currentSpeech);
                // Detect wakeword (Extremely broad fuzzy matching for Frexo)
                const matchWords = [
                    'frex', 'freaks', 'frank', 'flex', 'frax', 'frexo', 'fraxo', 'freck', 'frag', 'fresco', 
                    'rexo', 'facto', 'track so', 'frack', 'f렉스', 'hey frex', 'freak so', 'frax oh', 'fragso', 
                    'frank so', 'fresh', 'shrek', 'trex', 'f r e x', 'hey ai', 'hey bot'
                ];
                
                // Fuzzy regex to catch almost anything starting with F/P/B and containing X/K/S sounds
                const fuzzyRegex = /frex|frax|fres|frek|frag|flex|frank|shrek|trex|rex|fr[a-z]x|fr[a-z]k|f[a-z]ex|f[a-z]ck|prax|prex|brex|brax|alex|felix|crack so|press so|fresh so/i;
                
                if (matchWords.some(word => currentSpeech.includes(word)) || fuzzyRegex.test(currentSpeech)) {
                    this.triggerWakeWord();
                }
            } else if (this.mode === 'active') {
                this.updateText(`"${currentSpeech}"`);
                
                // If it's final, process it
                if (finalTranscript) {
                    this.processTranscript(finalTranscript);
                }
            }
        };

        this.recognition.onerror = (event) => {
            this.isListening = false;
            
            // Always show permission errors regardless of mode!
            if (event.error === 'not-allowed') {
                this.mode = 'idle';
                this.updateFabStyle();
                this.updateText("Error: Microphone access is blocked by your browser! Please click the lock icon in the URL bar and allow Microphone access.");
                this.overlay.style.display = 'flex';
                return;
            }

            if (this.mode === 'active') {
                let msg = event.error;
                if (event.error === 'no-speech') {
                    msg = "No voice detected. Please try again.";
                }
                this.updateText(`Error: ${msg}`);
                
                setTimeout(() => { 
                    if (!this.isListening && !this.isProcessing) {
                        this.overlay.style.display = 'none'; 
                    }
                }, 3000);
                
                this.mode = 'idle';
                this.updateFabStyle();
            }
        };

        this.recognition.onend = () => {
            this.isListening = false;
            
            if (this.mode === 'wakeword') {
                // Auto-restart wakeword listening loop if it dropped
                setTimeout(() => {
                    if (this.mode === 'wakeword' && !this.isListening) {
                        try { this.recognition.start(); } catch(e) {}
                    }
                }, 1000);
            } else if (this.mode === 'active' && !this.isProcessing && !this.hasResult) {
                // Stopped listening without results
                this.mode = 'idle';
                this.updateFabStyle();
                this.updateText("No voice detected. Please try again.");
                
                // Let user dismiss it by clicking
                const hideOverlay = (e) => {
                    if (e.target.closest('#frex-fab') || e.target.closest('#frex-overlay')) return;
                    this.overlay.style.display = 'none';
                    document.removeEventListener('click', hideOverlay);
                };
                setTimeout(() => document.addEventListener('click', hideOverlay), 100);
                
                // Revert back to wakeword after a failed active session
                this.startWakewordListening();
            }
        };
    }

    updateFabStyle() {
        this.fab.className = '';
        if (this.mode === 'wakeword') {
            this.fab.classList.add('wakeword');
        } else if (this.mode === 'active') {
            this.fab.classList.add('listening');
        }
    }

    startWakewordListening() {
        this.mode = 'wakeword';
        this.recognition.continuous = true;
        this.recognition.interimResults = true;
        
        if (!this.isListening) {
            try { this.recognition.start(); } catch(e) {}
        } else {
            this.updateFabStyle();
        }

        // Watchdog to ensure it NEVER dies (fixes Chrome tab throttling killing the loop)
        if (!this.watchdog) {
            this.watchdog = setInterval(() => {
                if (this.mode === 'wakeword' && !this.isListening) {
                    try { this.recognition.start(); } catch(e) {}
                }
            }, 3000);
        }
    }

    startActiveListening() {
        this.mode = 'active';
        this.hasResult = false;
        this.recognition.continuous = false;
        this.recognition.interimResults = true; // Show text as they speak
        
        // Forcefully abort instead of stop so it doesn't get stuck waiting for onend
        try { this.recognition.abort(); } catch(e) {}
        
        setTimeout(() => {
            try { 
                this.recognition.start(); 
            } catch(e) {
                console.error("Frex Mic Start Error:", e);
                // If it fails to start because it's already started, make sure the UI matches
                if (e.name === 'InvalidStateError') {
                    this.isListening = true;
                    this.updateFabStyle();
                }
            }
        }, 300);
    }

    stopListening() {
        this.isListening = false;
        try { this.recognition.stop(); } catch(e) {}
    }

    triggerWakeWord() {
        this.stopListening();
        this.mode = 'idle'; // Temporarily idle while speaking
        this.updateFabStyle();
        
        this.overlay.style.display = 'flex';
        this.updateText("Waking up...");
        
        const textToSpeak = `How can I help you, ${this.userName}?`;
        this.speak(textToSpeak, () => {
            // Callback when speech ends: Start actively listening for the command
            this.startActiveListening();
        });
    }

    updateText(text) {
        this.overlay.querySelector('.frex-text').innerText = text;
    }

    toggleSpinner(show) {
        const spinner = this.overlay.querySelector('.frex-spinner');
        const waves = this.overlay.querySelector('.frex-waves');
        if (show) {
            spinner.style.display = 'inline-block';
            waves.style.display = 'none';
        } else {
            spinner.style.display = 'none';
            waves.style.display = 'flex';
        }
    }

    async processTranscript(transcript) {
        this.isProcessing = true;
        this.stopListening();
        this.overlay.style.display = 'flex';
        this.toggleSpinner(true);
        this.updateText("Thinking...");

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const response = await fetch('/frex/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ transcript })
            });

            const data = await response.json();
            
            const handleComplete = () => {
                this.isProcessing = false;
                if (data.success && data.action) {
                    this.executeAction(data.action, data.parameters);
                } else {
                    console.error("Frex Error:", data.error);
                }
                
                // If the action does NOT navigate away, we want to immediately return to background listening
                const navActions = ['navigate_dashboard', 'navigate_module', 'open_create_form', 'search_shipment'];
                if (!navActions.includes(data.action)) {
                    this.startWakewordListening();
                    
                    // Keep overlay open until user clicks anywhere
                    const hideOverlay = (e) => {
                        if (e.target.closest('#frex-fab') || e.target.closest('#frex-overlay')) return;
                        this.overlay.style.display = 'none';
                        document.removeEventListener('click', hideOverlay);
                    };
                    setTimeout(() => document.addEventListener('click', hideOverlay), 100);
                }
            };

            if (data.voice_reply) {
                this.updateText(data.voice_reply);
                // Speak first, then handle the action (so navigation doesn't cut off the voice)
                this.speak(data.voice_reply, handleComplete);
            } else {
                handleComplete();
            }

        } catch (error) {
            console.error("Frex Network Error:", error);
            this.updateText("Network error.");
            this.isProcessing = false;
            
            // Revert to listening and let them click to dismiss the error
            this.startWakewordListening();
            const hideOverlay = (e) => {
                if (e.target.closest('#frex-fab') || e.target.closest('#frex-overlay')) return;
                this.overlay.style.display = 'none';
                document.removeEventListener('click', hideOverlay);
            };
            setTimeout(() => document.addEventListener('click', hideOverlay), 100);
        }
    }

    speak(text, onEndCallback = null) {
        if (!window.speechSynthesis) {
            if (onEndCallback) onEndCallback();
            return;
        }
        
        // Cancel any ongoing speech
        window.speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        utterance.rate = 1.0;
        
        let callbackFired = false;
        
        if (onEndCallback) {
            const fireCallback = () => {
                if (!callbackFired) {
                    callbackFired = true;
                    onEndCallback();
                }
            };
            
            utterance.onend = fireCallback;
            utterance.onerror = () => {
                // If TTS is blocked, wait 4 seconds before hiding so the user can at least read the text!
                setTimeout(fireCallback, 4000);
            };
            
            // Safety net: if onend or onerror never fire at all, force callback after 10s
            setTimeout(fireCallback, 10000);
        }
        
        try {
            window.speechSynthesis.speak(utterance);
        } catch (e) {
            console.error("Frex TTS Error:", e);
            if (onEndCallback && typeof fireCallback === 'function') {
                fireCallback();
            }
        }
    }

    executeAction(action, params) {
        switch (action) {
            case 'navigate_dashboard':
                window.location.href = '/dashboard';
                break;
            case 'navigate_module':
                if (params.module) window.location.href = `/${params.module}/list`;
                break;
            case 'open_create_form':
                if (params.module) window.location.href = `/${params.module}/create`;
                break;
            case 'search_shipment':
                if (params.query) {
                    const searchInput = document.querySelector('input[type="search"]');
                    if (searchInput) {
                        searchInput.value = params.query;
                        searchInput.dispatchEvent(new Event('input'));
                    } else {
                        window.location.href = `/ocean-import/list?search=${encodeURIComponent(params.query)}`;
                    }
                }
                break;
            case 'read_dashboard_summary':
                break;
            case 'conversational_reply':
                // Handled natively by the TTS, no UI action needed.
                break;
            default:
                console.warn("Frex: Unknown action", action);
        }
    }
}

// Initialize Frex when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('meta[name="csrf-token"]')) {
        window.frexAssistant = new FrexVoiceAssistant();
        
        // Auto-start wakeword mode on page load (like Siri)
        setTimeout(() => {
            window.frexAssistant.startWakewordListening();
        }, 1000);
    }
});
