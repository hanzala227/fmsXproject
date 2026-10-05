@extends('super-admin.layout')

@section('content')
<div style="padding: 16px; max-width: 900px; margin: 0 auto;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 600; color: #1e293b; margin: 0 0 4px 0;">
                <i class="fa fa-microchip" style="color: #405189; margin-right: 8px;"></i> Truly Universal AI Engine
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;">Configure any AI provider (OpenAI, Grok, Groq, OpenRouter, Gemini) instantly.</p>
        </div>
    </div>

    @if(session('success'))
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 13px; display: flex; align-items: center;">
            <i class="fa fa-check-circle" style="margin-right: 8px; font-size: 16px;"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; font-size: 13px; display: flex; align-items: center;">
            <i class="fa fa-exclamation-circle" style="margin-right: 8px; font-size: 16px;"></i> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; font-size: 13px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <form method="POST" action="{{ route('super-admin.ai-settings.update') }}">
            @csrf
            
            <div style="padding: 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <label style="display: block; font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 12px;">API Endpoint Format</label>
                <div style="display: flex; gap: 16px;">
                    
                    <label style="flex: 1; border: 2px solid {{ $settings->api_type == 'openai_compatible' ? '#405189' : '#e2e8f0' }}; border-radius: 8px; padding: 16px; cursor: pointer; transition: all 0.2s; background: {{ $settings->api_type == 'openai_compatible' ? '#f0f4ff' : '#fff' }}; position: relative;" onclick="document.getElementById('type-openai').checked = true; highlightProvider('openai_compatible'); toggleFields('openai_compatible');">
                        <input type="radio" name="api_type" id="type-openai" value="openai_compatible" {{ $settings->api_type == 'openai_compatible' ? 'checked' : '' }} style="position: absolute; opacity: 0;">
                        <div style="display: flex; align-items: center;">
                            <div style="width: 40px; height: 40px; background: #0f172a; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 12px;">
                                <i class="fa fa-code"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 600; color: #1e293b;">Standard / OpenAI Compatible</h3>
                                <p style="margin: 0; font-size: 12px; color: #64748b;">Supports Grok, OpenAI, Groq, OpenRouter etc.</p>
                            </div>
                            @if($settings->api_type == 'openai_compatible')
                                <i class="fa fa-check-circle check-icon" style="color: #405189; position: absolute; top: 16px; right: 16px; font-size: 18px;"></i>
                            @endif
                        </div>
                    </label>

                    <label style="flex: 1; border: 2px solid {{ $settings->api_type == 'gemini' ? '#405189' : '#e2e8f0' }}; border-radius: 8px; padding: 16px; cursor: pointer; transition: all 0.2s; background: {{ $settings->api_type == 'gemini' ? '#f0f4ff' : '#fff' }}; position: relative;" onclick="document.getElementById('type-gemini').checked = true; highlightProvider('gemini'); toggleFields('gemini');">
                        <input type="radio" name="api_type" id="type-gemini" value="gemini" {{ $settings->api_type == 'gemini' ? 'checked' : '' }} style="position: absolute; opacity: 0;">
                        <div style="display: flex; align-items: center;">
                            <div style="width: 40px; height: 40px; background: #1a73e8; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 12px;">
                                G
                            </div>
                            <div>
                                <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 600; color: #1e293b;">Google Gemini</h3>
                                <p style="margin: 0; font-size: 12px; color: #64748b;">Native Google API format.</p>
                            </div>
                            @if($settings->api_type == 'gemini')
                                <i class="fa fa-check-circle check-icon" style="color: #405189; position: absolute; top: 16px; right: 16px; font-size: 18px;"></i>
                            @endif
                        </div>
                    </label>

                </div>
            </div>

            <div style="padding: 24px;">
                <h3 style="margin: 0 0 16px 0; font-size: 15px; font-weight: 600; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Connection Details</h3>
                
                <div id="base_url_container" style="margin-bottom: 16px; {{ $settings->api_type == 'gemini' ? 'display: none;' : '' }}">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Base URL</label>
                    <input type="url" name="base_url" id="base_url" value="{{ $settings->base_url }}" placeholder="https://api.openai.com/v1" style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: #64748b;">
                        Examples: <br>
                        • <strong>OpenAI:</strong> <code>https://api.openai.com/v1</code> <br>
                        • <strong>Grok (xAI):</strong> <code>https://api.x.ai/v1</code> <br>
                        • <strong>OpenRouter:</strong> <code>https://openrouter.ai/api/v1</code>
                    </p>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">API Key</label>
                    <input type="password" name="api_key" value="{{ $settings->api_key }}" placeholder="Your secret API key..." style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Model Name</label>
                    <input type="text" name="model_name" value="{{ $settings->model_name }}" placeholder="e.g. grok-2-latest, gpt-4o, gemini-1.5-pro" style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: #64748b;">Enter the exact model string provided by your AI vendor.</p>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; align-items: center;">
                <button type="submit" style="height: 38px; padding: 0 20px; font-size: 13px; font-weight: 500; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer;">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>

    <!-- Connection Test Panel (Chatbot) -->
    <div style="margin-top: 24px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);" x-data="aiChatBot()">
        <div style="padding: 16px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: #1e293b;">
                <i class="fa fa-comments" style="color: #405189; margin-right: 8px;"></i> AI Configuration Tester
            </h3>
            <span style="font-size: 12px; color: #64748b; background: #e2e8f0; padding: 2px 8px; border-radius: 12px;">Active: {{ $settings->model_name }}</span>
        </div>
        
        <div style="display: flex; flex-direction: column; height: 350px;">
            <!-- Chat Window -->
            <div id="chat-window" style="flex: 1; padding: 24px; overflow-y: auto; background: #fafafa; display: flex; flex-direction: column; gap: 16px;">
                
                <!-- Welcome Message -->
                <div style="display: flex; gap: 12px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #405189; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                        <i class="fa fa-robot"></i>
                    </div>
                    <div style="background: #fff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 0 12px 12px 12px; font-size: 13px; color: #334155; max-width: 80%; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        Hello! I am connected to <strong>{{ $settings->model_name }}</strong> via your configured API. Send me a message to test if the connection is working properly!
                    </div>
                </div>

                <!-- Chat History Rendered via Alpine -->
                <template x-for="(msg, index) in messages" :key="index">
                    <div style="display: flex; gap: 12px;" :style="msg.role === 'user' ? 'flex-direction: row-reverse;' : ''">
                        <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;"
                             :style="msg.role === 'user' ? 'background: #0f172a;' : (msg.isError ? 'background: #ef4444;' : 'background: #405189;')">
                            <i class="fa" :class="msg.role === 'user' ? 'fa-user' : (msg.isError ? 'fa-exclamation-triangle' : 'fa-robot')"></i>
                        </div>
                        <div style="padding: 12px 16px; font-size: 13px; max-width: 80%; box-shadow: 0 1px 2px rgba(0,0,0,0.05); white-space: pre-wrap; word-break: break-word;"
                             :style="msg.role === 'user' ? 'background: #0f172a; color: white; border-radius: 12px 0 12px 12px;' : (msg.isError ? 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 0 12px 12px 12px;' : 'background: #fff; border: 1px solid #e2e8f0; color: #334155; border-radius: 0 12px 12px 12px;')"
                             x-text="msg.content">
                        </div>
                    </div>
                </template>

                <!-- Loading Indicator -->
                <div x-show="isLoading" style="display: flex; gap: 12px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #405189; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                        <i class="fa fa-robot"></i>
                    </div>
                    <div style="background: #fff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 0 12px 12px 12px; font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-circle-o-notch fa-spin"></i> Thinking...
                    </div>
                </div>

            </div>

            <!-- Input Area -->
            <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #fff; display: flex; gap: 12px;">
                <input type="text" x-model="inputText" @keydown.enter="sendMessage" :disabled="isLoading" placeholder="Type a message to test..." style="flex: 1; height: 40px; padding: 0 16px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 20px; outline: none; transition: border-color 0.2s;">
                <button @click="sendMessage" :disabled="isLoading || inputText.trim() === ''" style="height: 40px; width: 40px; border-radius: 50%; background: #405189; color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: opacity 0.2s;" :style="(isLoading || inputText.trim() === '') ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                    <i class="fa fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// UI Interactions for the Configuration Form
function highlightProvider(type) {
    const labels = document.querySelectorAll('label[onclick^="document.getElementById"]');
    labels.forEach(l => {
        l.style.borderColor = '#e2e8f0';
        l.style.background = '#fff';
        const icon = l.querySelector('.check-icon');
        if (icon) icon.remove();
    });

    const active = document.querySelector('label[onclick*="'+type+'"]');
    if (active) {
        active.style.borderColor = '#405189';
        active.style.background = '#f0f4ff';
        
        const checkIcon = document.createElement('i');
        checkIcon.className = 'fa fa-check-circle check-icon';
        checkIcon.style.color = '#405189';
        checkIcon.style.position = 'absolute';
        checkIcon.style.top = '16px';
        checkIcon.style.right = '16px';
        checkIcon.style.fontSize = '18px';
        
        active.querySelector('div').appendChild(checkIcon);
    }
}

function toggleFields(type) {
    const baseUrlContainer = document.getElementById('base_url_container');
    const baseUrlInput = document.getElementById('base_url');
    
    if (type === 'gemini') {
        baseUrlContainer.style.display = 'none';
    } else {
        baseUrlContainer.style.display = 'block';
        if (!baseUrlInput.value) {
            baseUrlInput.value = 'https://api.openai.com/v1';
        }
    }
}

// Alpine.js Chatbot Logic
document.addEventListener('alpine:init', () => {
    Alpine.data('aiChatBot', () => ({
        inputText: '',
        isLoading: false,
        messages: [],
        
        scrollToBottom() {
            setTimeout(() => {
                const window = document.getElementById('chat-window');
                if (window) window.scrollTop = window.scrollHeight;
            }, 50);
        },

        async sendMessage() {
            if (this.inputText.trim() === '') return;
            
            const message = this.inputText.trim();
            this.inputText = '';
            
            // Add user message
            this.messages.push({ role: 'user', content: message, isError: false });
            this.isLoading = true;
            this.scrollToBottom();
            
            try {
                const response = await fetch("{{ route('super-admin.ai-settings.test') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: message })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.messages.push({ role: 'ai', content: data.reply, isError: false });
                } else {
                    this.messages.push({ role: 'ai', content: 'Connection Error: ' + (data.error || 'Unknown error occurred'), isError: true });
                }
            } catch (error) {
                this.messages.push({ role: 'ai', content: 'Network Error: Failed to reach the server.', isError: true });
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        }
    }));
});
</script>
@endsection
