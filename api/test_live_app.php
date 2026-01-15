<!DOCTYPE html>
<html>
<head>
    <title>Live App Test - VitalConnect Email System</title>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .header h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin-left: 15px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .panel {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .panel h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 24px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .user-card {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }
        .user-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
            border-width: 3px;
        }
        .user-card h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 8px;
        }
        .user-card .detail {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
            display: flex;
            align-items: center;
        }
        .user-card .detail::before {
            content: '•';
            margin-right: 8px;
            color: #667eea;
            font-weight: bold;
        }
        .blood-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 16px;
            margin-left: 10px;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 20px;
        }
        .btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .btn.success {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
        }
        .btn.danger {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        }
        .result-box {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: none;
        }
        .result-box.show {
            display: block;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .result-box.success {
            border-left: 5px solid #4caf50;
        }
        .result-box.error {
            border-left: 5px solid #f44336;
        }
        .result-box h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        .result-box.success h3 {
            color: #4caf50;
        }
        .result-box.error h3 {
            color: #f44336;
        }
        .result-detail {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .result-detail strong {
            color: #667eea;
        }
        .loading {
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step::after {
            content: '→';
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            color: #ccc;
            font-size: 24px;
        }
        .step:last-child::after {
            display: none;
        }
        .step.active {
            color: #667eea;
            font-weight: bold;
        }
        .step.completed {
            color: #4caf50;
        }
        .step-number {
            background: #e0e0e0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step.completed .step-number {
            background: #4caf50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Live App Email Test</h1>
            <span class="status-badge">SIMULATING REAL USER FLOW</span>
            <div class="info-box" style="margin-top: 20px;">
                <strong>📍 This test simulates exactly what happens when a requester clicks "Send Request" in the live app.</strong>
                <br>It uses the same API endpoints, same database, and same email system.
            </div>
        </div>

        <div class="step-indicator">
            <div class="step" id="step1">
                <div class="step-number">1</div>
                <div>Select Requester</div>
            </div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div>Select Donor</div>
            </div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <div>Send Request</div>
            </div>
            <div class="step" id="step4">
                <div class="step-number">4</div>
                <div>Email Sent</div>
            </div>
        </div>

        <div class="grid">
            <div class="panel">
                <h2>👤 Select Requester</h2>
                <div id="requesterList">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading requesters from database...</p>
                    </div>
                </div>
            </div>

            <div class="panel">
                <h2>🩸 Select Donor</h2>
                <div id="donorList">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading donors from database...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h2>📧 Send Blood Request</h2>
            <div class="warning-box">
                <strong>⚠️ This will create a REAL request in the database and send a REAL email!</strong>
                <br>Make sure you have selected both a requester and a donor above.
            </div>
            <button class="btn" id="sendBtn" disabled>
                📨 Send Request & Email Notification
            </button>
        </div>

        <div class="result-box" id="resultBox">
            <h3 id="resultTitle"></h3>
            <div id="resultContent"></div>
        </div>
    </div>

    <script>







        
        let selectedRequester = null;
        let selectedDonor = null;
        let currentStep = 1;

        function updateStep(step) {
            currentStep = step;
            for (let i = 1; i <= 4; i++) {
                const stepEl = document.getElementById(`step${i}`);
                stepEl.classList.remove('active', 'completed');
                if (i < step) stepEl.classList.add('completed');
                if (i === step) stepEl.classList.add('active');
            }
        }

        async function loadUsers() {
            try {
                // Load requesters
                const reqResponse = await fetch('/api/profiles.php?role=requester');
                const requesters = await reqResponse.json();
                
                const requesterList = document.getElementById('requesterList');
                requesterList.innerHTML = '';
                
                if (requesters.length === 0) {
                    requesterList.innerHTML = '<p style="text-align:center;color:#999;">No requesters found in database</p>';
                } else {
                    requesters.forEach(user => {
                        const card = document.createElement('div');
                        card.className = 'user-card';
                        card.innerHTML = `
                            <h3>${user.name} <span class="blood-badge">${user.blood_type}</span></h3>
                            <div class="detail">Email: ${user.email}</div>
                            <div class="detail">Phone: ${user.phone}</div>
                        `;
                        card.onclick = () => selectRequester(user, card);
                        requesterList.appendChild(card);
                    });
                }

                // Load donors
                const donorResponse = await fetch('/api/profiles.php?role=donor');
                const donors = await donorResponse.json();
                
                const donorList = document.getElementById('donorList');
                donorList.innerHTML = '';
                
                if (donors.length === 0) {
                    donorList.innerHTML = '<p style="text-align:center;color:#999;">No donors found in database</p>';
                } else {
                    donors.forEach(user => {
                        const card = document.createElement('div');
                        card.className = 'user-card';
                        card.innerHTML = `
                            <h3>${user.name} <span class="blood-badge">${user.blood_type}</span></h3>
                            <div class="detail">Email: ${user.email}</div>
                            <div class="detail">Phone: ${user.phone}</div>
                            <div class="detail">Status: ${user.is_available ? '✅ Available' : '❌ On Cooldown'}</div>
                        `;
                        card.onclick = () => selectDonor(user, card);
                        donorList.appendChild(card);
                    });
                }

            } catch (error) {
                console.error('Error loading users:', error);
                showResult('error', 'Failed to Load Users', `Error: ${error.message}`);
            }
        }

        function selectRequester(user, card) {
            document.querySelectorAll('#requesterList .user-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            selectedRequester = user;
            updateStep(2);
            checkCanSend();
        }

        function selectDonor(user, card) {
            document.querySelectorAll('#donorList .user-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            selectedDonor = user;
            updateStep(3);
            checkCanSend();
        }

        function checkCanSend() {
            const btn = document.getElementById('sendBtn');
            btn.disabled = !(selectedRequester && selectedDonor);
        }

        async function sendRequest() {
            const btn = document.getElementById('sendBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner" style="width:20px;height:20px;border-width:3px;display:inline-block;margin-right:10px;"></div> Sending...';
            
            updateStep(3);
            
            const resultBox = document.getElementById('resultBox');
            resultBox.classList.remove('show', 'success', 'error');

            try {
                console.log('Sending request:', {
                    requester_id: selectedRequester.id,
                    donor_id: selectedDonor.id,
                    message: `Urgent blood request from ${selectedRequester.name}`
                });

                const response = await fetch('/api/requests.php?action=create_request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        requester_id: selectedRequester.id,
                        donor_id: selectedDonor.id,
                        message: `Urgent blood request from ${selectedRequester.name}`
                    })
                });

                const result = await response.json();
                console.log('API Response:', result);

                if (result.status === 'success') {
                    updateStep(4);
                    
                    let content = `
                        <div class="result-detail">
                            <strong>Request ID:</strong> ${result.id}<br>
                            <strong>Email Sent:</strong> ${result.email_sent ? '✅ YES' : '❌ NO'}<br>
                            <strong>Requester:</strong> ${selectedRequester.name} (${selectedRequester.email})<br>
                            <strong>Donor:</strong> ${selectedDonor.name} (${selectedDonor.email})<br>
                            <strong>Blood Type:</strong> ${selectedRequester.blood_type}
                        </div>
                    `;

                    if (result.email_sent) {
                        content += `
                            <div class="info-box">
                                <strong>✅ EMAIL SENT SUCCESSFULLY!</strong><br><br>
                                The donor <strong>${selectedDonor.name}</strong> should receive an email at <strong>${selectedDonor.email}</strong> with:<br>
                                • Requester name: ${selectedRequester.name}<br>
                                • Blood type needed: ${selectedRequester.blood_type}<br>
                                • Link to dashboard: http://localhost/login<br><br>
                                <strong>Please check:</strong><br>
                                1. Donor's email inbox (and spam folder)<br>
                                2. PHP error logs at: C:\\wamp64\\logs\\php_error.log<br>
                                3. Browser console for detailed logs
                            </div>
                        `;
                        showResult('success', '🎉 Success! Request Sent & Email Delivered', content);
                    } else {
                        content += `
                            <div class="warning-box">
                                <strong>⚠️ REQUEST CREATED BUT EMAIL FAILED</strong><br><br>
                                The request was saved to the database, but the email notification failed to send.<br><br>
                                <strong>Check:</strong><br>
                                1. PHP error logs: C:\\wamp64\\logs\\php_error.log<br>
                                2. SMTP configuration in api/config.php<br>
                                3. Gmail app password is correct<br>
                                4. Port 465 is not blocked by firewall
                            </div>
                        `;
                        showResult('error', '⚠️ Request Created, Email Failed', content);
                    }
                } else {
                    showResult('error', '❌ Request Failed', `
                        <div class="result-detail">
                            <strong>Error:</strong> ${result.message || 'Unknown error'}<br>
                            <strong>Status:</strong> ${result.status}
                        </div>
                        <div class="warning-box">
                            Check the browser console and PHP error logs for details.
                        </div>
                    `);
                }

            } catch (error) {
                console.error('Error:', error);
                showResult('error', '❌ Network Error', `
                    <div class="result-detail">
                        <strong>Error:</strong> ${error.message}
                    </div>
                    <div class="warning-box">
                        Failed to communicate with the API. Check that WAMP is running and the API endpoint is accessible.
                    </div>
                `);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '📨 Send Request & Email Notification';
            }
        }

        function showResult(type, title, content) {
            const resultBox = document.getElementById('resultBox');
            const resultTitle = document.getElementById('resultTitle');
            const resultContent = document.getElementById('resultContent');
            
            resultBox.className = `result-box ${type} show`;
            resultTitle.textContent = title;
            resultContent.innerHTML = content;
            
            resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        document.getElementById('sendBtn').onclick = sendRequest;

        // Load users on page load
        loadUsers();
        updateStep(1);
    </script>
</body>
</html>
