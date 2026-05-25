<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Send Telegram Notification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 550px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #4a90e2;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .help-text {
            font-size: 12px;
            color: #888;
            margin-top: 4px;
        }

        .message-types {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 5px;
        }

        .type-option {
            padding: 8px;
            text-align: center;
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }

        .type-option:hover {
            background: #e8e8e8;
        }

        .type-option.active {
            background: #4a90e2;
            border-color: #4a90e2;
            color: white;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #357abd;
        }

        button:active {
            transform: scale(0.98);
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #e0e0e0;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 3px solid #4a90e2;
            padding: 12px;
            margin-top: 20px;
            font-size: 13px;
            color: #555;
        }

        .info-box p {
            margin: 5px 0;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            
            .row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .message-types {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Send Telegram Notification</h1>
        <div class="subtitle">Send messages to your Telegram users</div>
        
        <div id="alert" class="alert"></div>
        
        <form id="telegramForm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            
            <div class="form-group">
                <label>Send To</label>
                <select name="recipient_type" id="recipientType">
                    <option value="single">Single User</option>
                    <option value="multiple">Multiple Users</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Chat ID(s)</label>
                <input type="text" name="chat_ids" id="chatIds" 
                       placeholder="Enter chat ID (e.g., 123456789)" required>
                <div class="help-text">For multiple users, separate IDs with commas</div>
            </div>
            
            <div class="form-group">
                <label>Message Title (Optional)</label>
                <input type="text" name="title" placeholder="Enter message title">
            </div>
            
            <div class="form-group">
                <label>Message Content</label>
                <textarea name="content" placeholder="Enter your message here..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Message Type</label>
                <div class="message-types">
                    <div class="type-option" data-type="info">Info</div>
                    <div class="type-option" data-type="success">Success</div>
                    <div class="type-option" data-type="warning">Warning</div>
                    <div class="type-option" data-type="error">Error</div>
                </div>
                <input type="hidden" name="message_type" id="messageType" value="info">
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label>Button Text</label>
                    <input type="text" name="button_text" placeholder="e.g., Visit Website">
                </div>
                
                <div class="form-group">
                    <label>Button URL</label>
                    <input type="url" name="button_url" placeholder="https://example.com">
                </div>
            </div>
            
            <div class="form-group">
                <label>Schedule Send (Optional)</label>
                <input type="datetime-local" name="schedule_time">
                <div class="help-text">Leave empty to send immediately</div>
            </div>
            
            <button type="submit">Send Notification</button>
            
            <hr>
            
            <div class="info-box">
                <p><strong>Note:</strong></p>
                <p>• Make sure your bot token is correctly set in .env file</p>
                <p>• Chat IDs must be valid Telegram user or group IDs</p>
                <p>• Scheduled messages require queue worker to be running</p>
                <p>• Run <strong>php artisan queue:work</strong> for scheduled messages</p>
            </div>
        </form>
    </div>
    
    <script>
        // Handle recipient type change
        const recipientType = document.getElementById('recipientType');
        const chatIdsInput = document.getElementById('chatIds');
        
        recipientType.addEventListener('change', function() {
            if (this.value === 'single') {
                chatIdsInput.placeholder = 'Enter chat ID (e.g., 123456789)';
                chatIdsInput.required = true;
            } else {
                chatIdsInput.placeholder = 'Enter chat IDs (e.g., 123456789,987654321)';
                chatIdsInput.required = true;
            }
        });
        
        // Handle message type selection
        const typeOptions = document.querySelectorAll('.type-option');
        const messageTypeInput = document.getElementById('messageType');
        
        typeOptions.forEach(option => {
            option.addEventListener('click', function() {
                typeOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                messageTypeInput.value = this.dataset.type;
            });
        });
        
        // Set default active type
        typeOptions[0].classList.add('active');
        
        // Handle form submission
        const form = document.getElementById('telegramForm');
        const alertDiv = document.getElementById('alert');
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            // Add recipient type to form data
            formData.append('chat_ids', chatIdsInput.value);
            
            // Disable submit button
            const submitBtn = form.querySelector('button');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            try {
                const response = await fetch('/telegram/send-advanced', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                alertDiv.style.display = 'block';
                
                if (result.success) {
                    alertDiv.className = 'alert success';
                    alertDiv.textContent = 'Success: ' + result.message;
                    form.reset();
                    // Reset to default values
                    recipientType.value = 'single';
                    chatIdsInput.placeholder = 'Enter chat ID (e.g., 123456789)';
                    typeOptions.forEach(opt => opt.classList.remove('active'));
                    typeOptions[0].classList.add('active');
                    messageTypeInput.value = 'info';
                } else {
                    alertDiv.className = 'alert error';
                    alertDiv.textContent = 'Error: ' + result.message;
                }
                
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
                
            } catch (error) {
                alertDiv.style.display = 'block';
                alertDiv.className = 'alert error';
                alertDiv.textContent = 'Error: Failed to send notification. Please try again.';
                
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Notification';
            }
        });
    </script>
</body>
</html>