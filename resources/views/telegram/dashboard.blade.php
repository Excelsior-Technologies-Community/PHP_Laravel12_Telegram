<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        h1 { font-size: 28px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; }
        .subtitle { color: #666; font-size: 14px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card h2 { font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
        .stat { font-size: 32px; font-weight: 600; color: #4a90e2; margin-bottom: 5px; }
        .stat-label { color: #666; font-size: 14px; }
        .button { display: inline-block; padding: 10px 20px; background: #4a90e2; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500; border: none; cursor: pointer; }
        .button:hover { background: #357abd; }
        .button-secondary { background: #666; }
        .info-list { list-style: none; }
        .info-list li { padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .button-group { display: flex; gap: 10px; margin-top: 15px; }
        .status { padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: 500; }
        .status.active { background: #d4edda; color: #155724; }
        .status.inactive { background: #f8d7da; color: #721c24; }
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
                    <div class="stat-label">Bot Details</div>
                    <div id="botName" style="margin-top: 5px;">-</div>
                </div>
            </div>
            
            <div class="card">
                <h2>Analytics</h2>
                <div class="stat" id="sentToday">0</div>
                <div class="stat-label">Messages Sent Today</div>
                <div style="margin-top: 15px;">
                    <div class="stat" id="totalSubscribers">0</div>
                    <div class="stat-label">Total Active Subscribers</div>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <div class="card">
                <h2>Message History</h2>
                <ul class="info-list" id="statsList">
                    <li>Loading...</li>
                </ul>
            </div>
            
            <div class="card">
                <h2>Subscriber Breakdown</h2>
                <ul class="info-list" id="subscriberBreakdown">
                    <li>Loading...</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        async function loadDashboardData() {
            try {
                const [infoRes, statsRes] = await Promise.all([
                    fetch('/telegram/bot-info'),
                    fetch('/telegram/analytics')
                ]);
                
                const info = await infoRes.json();
                const stats = await statsRes.json();

                if (info.success) {
                    document.getElementById('botStatus').innerHTML = '<span class="status active">Active</span>';
                    document.getElementById('botName').innerText = info.bot_info.first_name + ' (@' + info.bot_info.username + ')';
                }

                if (stats.success) {
                    const data = stats.analytics;
                    document.getElementById('sentToday').innerText = data.sent_today;
                    document.getElementById('totalSubscribers').innerText = data.total_subscribers;
                    
                    document.getElementById('statsList').innerHTML = `
                        <li>Total Sent: ${data.total_sent}</li>
                        <li>Total Received: ${data.total_received}</li>
                        <li>Failed Messages: ${data.failed}</li>
                        <li>Scheduled Messages: ${data.scheduled}</li>
                    `;

                    document.getElementById('subscriberBreakdown').innerHTML = `
                        <li>Private: ${data.private_subscribers}</li>
                        <li>Groups: ${data.group_subscribers}</li>
                        <li>Channels: ${data.channel_subscribers}</li>
                    `;
                }
            } catch (error) {
                console.error(error);
            }
        }
        
        loadDashboardData();
        setInterval(loadDashboardData, 30000);
    </script>
</body>
</html>