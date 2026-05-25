<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Dashboard</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .card h2 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .stat {
            font-size: 32px;
            font-weight: 600;
            color: #4a90e2;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }

        .button:hover {
            background: #357abd;
        }

        .button-secondary {
            background: #666;
        }

        .button-secondary:hover {
            background: #555;
        }

        .info-list {
            list-style: none;
        }

        .info-list li {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .info-list li:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: 500;
            color: #333;
            display: inline-block;
            width: 100px;
        }

        .value {
            color: #666;
        }

        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }

        .status.active {
            background: #d4edda;
            color: #155724;
        }

        .status.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .button-group .button {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Telegram Bot Dashboard</h1>
            <div class="subtitle">Manage your Telegram notifications</div>
        </div>
        
        <div class="grid">
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="button-group">
                    <a href="/telegram/form" class="button">Send New Message</a>
                    <a href="/telegram/send" class="button button-secondary">Test Default Message</a>
                </div>
            </div>
            
            <div class="card">
                <h2>Bot Status</h2>
                <div id="botInfo">
                    <div class="stat-label">Status</div>
                    <div class="stat" id="botStatus">-</div>
                    <div class="stat-label">Bot Name</div>
                    <div id="botName" class="value" style="margin-top: 5px;">-</div>
                    <div class="stat-label" style="margin-top: 10px;">Username</div>
                    <div id="botUsername" class="value">-</div>
                </div>
            </div>
            
            <div class="card">
                <h2>Statistics</h2>
                <div class="stat" id="messageCount">0</div>
                <div class="stat-label">Messages Sent Today</div>
                <div style="margin-top: 15px;">
                    <div class="stat" id="totalCount">0</div>
                    <div class="stat-label">Total Messages Sent</div>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <div class="card">
                <h2>Recent Updates</h2>
                <ul class="info-list" id="updatesList">
                    <li>No updates available</li>
                </ul>
                <button onclick="loadUpdates()" class="button" style="margin-top: 15px; width: 100%;">Refresh Updates</button>
            </div>
            
            <div class="card">
                <h2>Quick Tips</h2>
                <ul class="info-list">
                    <li>Use <strong>Info</strong> type for general messages</li>
                    <li>Use <strong>Success</strong> type for confirmations</li>
                    <li>Use <strong>Warning</strong> type for alerts</li>
                    <li>Use <strong>Error</strong> type for failures</li>
                    <li>Add buttons to make messages interactive</li>
                    <li>Schedule messages for future delivery</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        // Load bot information
        async function loadBotInfo() {
            try {
                const response = await fetch('/telegram/bot-info');
                const data = await response.json();
                
                if (data.success && data.bot_info) {
                    const bot = data.bot_info;
                    document.getElementById('botStatus').innerHTML = '<span class="status active">Active</span>';
                    document.getElementById('botName').innerHTML = bot.first_name;
                    document.getElementById('botUsername').innerHTML = '@' + bot.username;
                } else {
                    document.getElementById('botStatus').innerHTML = '<span class="status inactive">Inactive</span>';
                    document.getElementById('botName').innerHTML = 'Not connected';
                    document.getElementById('botUsername').innerHTML = 'Check bot token';
                }
            } catch (error) {
                document.getElementById('botStatus').innerHTML = '<span class="status inactive">Error</span>';
                document.getElementById('botName').innerHTML = 'Connection failed';
                document.getElementById('botUsername').innerHTML = 'Check configuration';
            }
        }
        
        // Load updates
        async function loadUpdates() {
            try {
                const response = await fetch('/telegram/updates');
                const data = await response.json();
                
                const updatesList = document.getElementById('updatesList');
                
                if (data.success && data.updates && data.updates.length > 0) {
                    updatesList.innerHTML = '';
                    const recentUpdates = data.updates.slice(0, 5);
                    
                    recentUpdates.forEach(update => {
                        const li = document.createElement('li');
                        if (update.message) {
                            const from = update.message.from?.first_name || 'Unknown';
                            const text = update.message.text || 'No text';
                            li.innerHTML = `<strong>${from}</strong>: ${text.substring(0, 50)}${text.length > 50 ? '...' : ''}`;
                        } else {
                            li.innerHTML = 'New update received';
                        }
                        updatesList.appendChild(li);
                    });
                } else {
                    updatesList.innerHTML = '<li>No recent updates</li>';
                }
            } catch (error) {
                document.getElementById('updatesList').innerHTML = '<li>Failed to load updates</li>';
            }
        }
        
        // Load statistics
        function loadStats() {
            // Get today's date
            const today = new Date().toDateString();
            const sentToday = localStorage.getItem('telegram_sent_today') || 0;
            const totalSent = localStorage.getItem('telegram_total_sent') || 0;
            
            // Check if we need to reset today's count
            const lastDate = localStorage.getItem('telegram_last_date');
            if (lastDate !== today) {
                localStorage.setItem('telegram_sent_today', 0);
                localStorage.setItem('telegram_last_date', today);
                document.getElementById('messageCount').innerText = '0';
            } else {
                document.getElementById('messageCount').innerText = sentToday;
            }
            
            document.getElementById('totalCount').innerText = totalSent;
        }
        
        // Increment stats when message is sent
        function incrementStats() {
            const today = new Date().toDateString();
            let sentToday = parseInt(localStorage.getItem('telegram_sent_today') || 0);
            let totalSent = parseInt(localStorage.getItem('telegram_total_sent') || 0);
            const lastDate = localStorage.getItem('telegram_last_date');
            
            if (lastDate !== today) {
                sentToday = 0;
                localStorage.setItem('telegram_last_date', today);
            }
            
            sentToday++;
            totalSent++;
            
            localStorage.setItem('telegram_sent_today', sentToday);
            localStorage.setItem('telegram_total_sent', totalSent);
            
            document.getElementById('messageCount').innerText = sentToday;
            document.getElementById('totalCount').innerText = totalSent;
        }
        
        // Initialize dashboard
        loadBotInfo();
        loadUpdates();
        loadStats();
        
        // Refresh updates every 30 seconds
        setInterval(loadUpdates, 30000);
    </script>
</body>
</html>