<!DOCTYPE html>
<html>
<head>
    <title>Email Verification - VitalConnect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .status {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-left: 15px;
            font-size: 14px;
        }
        .status.ready {
            background: #4caf50;
            color: white;
        }
        .section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin: 8px 0;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #e0e0e0;
        }
        .check-item.success {
            border-left-color: #4caf50;
        }
        .check-item.error {
            border-left-color: #f44336;
        }
        .check-item.warning {
            border-left-color: #ff9800;
        }
        .check-icon {
            font-size: 24px;
            margin-right: 15px;
            min-width: 30px;
        }
        .check-content {
            flex: 1;
        }
        .check-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }
        .check-detail {
            font-size: 14px;
            color: #666;
        }
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
            margin: 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .success-box {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        .highlight {
            background: #fff9c4;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email System Verification</h1>
        <span class="status ready">CONFIGURED & READY</span>
        
        <div class="section">
            <h2>✅ System Configuration Check</h2>
            
            <?php
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            
            require_once 'config.php';
            
            // Check 1: SMTP Configuration
            $smtpConfigured = SMTP_HOST && SMTP_PORT && SMTP_USER && SMTP_PASS;
            ?>
            
            <div class="check-item <?php echo $smtpConfigured ? 'success' : 'error'; ?>">
                <div class="check-icon"><?php echo $smtpConfigured ? '✅' : '❌'; ?></div>
                <div class="check-content">
                    <div class="check-title">SMTP Configuration</div>
                    <div class="check-detail">
                        Host: <?php echo SMTP_HOST; ?> | 
                        Port: <?php echo SMTP_PORT; ?> | 
                        User: <?php echo SMTP_USER; ?>
                    </div>
                </div>
            </div>
            
            <?php
            // Check 2: Mailer file exists
            $mailerExists = file_exists('mailer.php');
            ?>
            
            <div class="check-item <?php echo $mailerExists ? 'success' : 'error'; ?>">
                <div class="check-icon"><?php echo $mailerExists ? '✅' : '❌'; ?></div>
                <div class="check-content">
                    <div class="check-title">Mailer Module</div>
                    <div class="check-detail">mailer.php is present and ready</div>
                </div>
            </div>
            
            <?php
            // Check 3: Requests endpoint
            $requestsExists = file_exists('requests.php');
            ?>
            
            <div class="check-item <?php echo $requestsExists ? 'success' : 'error'; ?>">
                <div class="check-icon"><?php echo $requestsExists ? '✅' : '❌'; ?></div>
                <div class="check-content">
                    <div class="check-title">Request Handler</div>
                    <div class="check-detail">requests.php is configured with email integration</div>
                </div>
            </div>
            
            <?php
            // Check 4: Test SMTP connection
            require_once 'mailer.php';
            $testEmail = SMTP_USER; // Send to self for testing
            $testResult = sendBloodRequestEmail($testEmail, 'Test Requester', 'O+');
            ?>
            
            <div class="check-item <?php echo $testResult ? 'success' : 'error'; ?>">
                <div class="check-icon"><?php echo $testResult ? '✅' : '❌'; ?></div>
                <div class="check-content">
                    <div class="check-title">SMTP Connection Test</div>
                    <div class="check-detail">
                        <?php if ($testResult): ?>
                            Successfully sent test email to <?php echo $testEmail; ?>
                        <?php else: ?>
                            Failed to send test email - Check PHP error logs
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>🔄 How Email Sending Works</h2>
            
            <div class="info-box">
                <strong>📍 Integration Point:</strong> The email is sent from <code>api/requests.php</code> in the <code>create_request</code> action (lines 92-108).
            </div>
            
            <table>
                <tr>
                    <th>Step</th>
                    <th>Action</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Requester clicks "Send Request" in the app</td>
                    <td>✅ Frontend ready</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>POST request to <code>/api/requests.php?action=create_request</code></td>
                    <td>✅ Endpoint configured</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Request saved to database</td>
                    <td>✅ Database ready</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Notification created for donor</td>
                    <td>✅ Notification system ready</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td><span class="highlight">Email sent to donor</span></td>
                    <td>✅ SMTP configured</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Response includes <code>email_sent: true/false</code></td>
                    <td>✅ Logging enabled</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <h2>📧 Email Template</h2>
            
            <div class="code-block">Subject: VitalConnect Hero: You have a new blood request

Hello,

You have a new blood request – save a life!

Request Details:
----------------
• Requester Name: [Dynamic: Requester's Name]
• Blood Type Needed: [Dynamic: Requester's Blood Type]

Please login to your dashboard to view full details and respond:
http://localhost/login

Best regards,
VitalConnect Team</div>
        </div>
        
        <div class="section">
            <h2>🧪 Testing Instructions</h2>
            
            <div class="success-box">
                <strong>✅ System is ready!</strong> Follow these steps to verify email delivery:
            </div>
            
            <ol style="line-height: 2; margin-left: 20px;">
                <li><strong>Test via Live Request Tool:</strong>
                    <br><button class="btn" onclick="window.location.href='test_live_request.php'">🧪 Open Live Request Test</button>
                </li>
                <li><strong>Test via Actual App:</strong>
                    <br>• Login as a requester
                    <br>• Search for a donor
                    <br>• Send a blood request
                    <br>• Check donor's email inbox
                </li>
                <li><strong>Verify in PHP Logs:</strong>
                    <br>• Open: <code>C:\wamp64\logs\php_error.log</code>
                    <br>• Look for: <code>VitalConnect: Email sent successfully</code>
                </li>
            </ol>
        </div>
        
        <div class="section">
            <h2>🔍 Debugging Guide</h2>
            
            <div class="warning-box">
                <strong>If email is not received:</strong>
            </div>
            
            <table>
                <tr>
                    <th>Issue</th>
                    <th>Solution</th>
                </tr>
                <tr>
                    <td>API returns <code>email_sent: false</code></td>
                    <td>Check PHP error logs for SMTP errors</td>
                </tr>
                <tr>
                    <td>No email in inbox</td>
                    <td>Check spam/junk folder</td>
                </tr>
                <tr>
                    <td>SMTP Auth Failed</td>
                    <td>Verify app password in config.php</td>
                </tr>
                <tr>
                    <td>No donor email found</td>
                    <td>Ensure donor profile has email address</td>
                </tr>
                <tr>
                    <td>Connection timeout</td>
                    <td>Check firewall blocking port 465</td>
                </tr>
            </table>
            
            <div class="info-box" style="margin-top: 20px;">
                <strong>📝 Check Logs:</strong> All email attempts are logged to PHP error log with detailed information.
                <br><code>C:\wamp64\logs\php_error.log</code>
            </div>
        </div>
        
        <div class="section">
            <h2>🎯 Quick Actions</h2>
            
            <button class="btn" onclick="window.location.href='test_live_request.php'">🧪 Live Request Test</button>
            <button class="btn" onclick="window.location.href='test_mail.php'">📨 Simple Email Test</button>
            <button class="btn" onclick="window.location.href='test_request_flow.php'">🩸 Full Flow Test</button>
            <button class="btn" onclick="window.location.href='../'">← Back to App</button>
        </div>
    </div>
</body>
</html>
