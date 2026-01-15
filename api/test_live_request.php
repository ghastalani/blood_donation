<!DOCTYPE html>
<html>
<head>
    <title>Live Request Test - VitalConnect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
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
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .user-select {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .user-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .user-card.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .user-card h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .user-card p {
            color: #666;
            font-size: 14px;
            margin: 4px 0;
        }
        .message-box {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
            width: 100%;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .result {
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
            display: none;
        }
        .result.success {
            background: #e8f5e9;
            border: 2px solid #4caf50;
            color: #2e7d32;
        }
        .result.error {
            background: #ffebee;
            border: 2px solid #f44336;
            color: #c62828;
        }
        .result.info {
            background: #e3f2fd;
            border: 2px solid #2196f3;
            color: #1565c0;
        }
        .result pre {
            background: rgba(0,0,0,0.05);
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            margin-top: 10px;
            font-size: 12px;
        }
        .loading {
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 8px;
        }
        .badge.donor {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .badge.requester {
            background: #e3f2fd;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Live Blood Request Test</h1>
        <p class="subtitle">Test the complete request flow with real database users and email sending</p>

        <div class="section">
            <h2>Step 1: Select Requester</h2>
            <div class="user-select" id="requesterList">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading requesters...</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Step 2: Select Donor</h2>
            <div class="user-select" id="donorList">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading donors...</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Step 3: Enter Message (Optional)</h2>
            <textarea class="message-box" id="messageBox" placeholder="Enter a message for the donor (optional)...">Urgent blood request - Please help if you can!</textarea>
        </div>

        <button class="btn" id="sendBtn" disabled>Send Blood Request & Email</button>

        <div class="result" id="result"></div>
    </div>

    <script>
        let selectedRequester = null;
        let selectedDonor = null;

        // Load users
        async function loadUsers() {
            try {
                // Load requesters
                const reqResponse = await fetch('../api/profiles.php?role=requester');
                const requesters = await reqResponse.json();
                
                const requesterList = document.getElementById('requesterList');
                requesterList.innerHTML = '';
                
                requesters.forEach(user => {
                    const card = document.createElement('div');
                    card.className = 'user-card';
                    card.innerHTML = `
                        <h3>${user.name} <span class="badge requester">REQUESTER</span></h3>
                        <p>📧 ${user.email}</p>
                        <p>🩸 Blood Type: ${user.blood_type}</p>
                    `;
                    card.onclick = () => selectRequester(user, card);
                    requesterList.appendChild(card);
                });

                // Load donors
                const donorResponse = await fetch('../api/profiles.php?role=donor');
                const donors = await donorResponse.json();
                
                const donorList = document.getElementById('donorList');
                donorList.innerHTML = '';
                
                donors.forEach(user => {
                    const card = document.createElement('div');
                    card.className = 'user-card';
                    card.innerHTML = `
                        <h3>${user.name} <span class="badge donor">DONOR</span></h3>
                        <p>📧 ${user.email}</p>
                        <p>🩸 Blood Type: ${user.blood_type}</p>
                        <p>📍 Available: ${user.is_available ? 'Yes' : 'No (Cooldown)'}</p>
                    `;
                    card.onclick = () => selectDonor(user, card);
                    donorList.appendChild(card);
                });

            } catch (error) {
                console.error('Error loading users:', error);
                showResult('error', 'Failed to load users: ' + error.message);
            }
        }

        function selectRequester(user, card) {
            document.querySelectorAll('#requesterList .user-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            selectedRequester = user;
            checkCanSend();
        }

        function selectDonor(user, card) {
            document.querySelectorAll('#donorList .user-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            selectedDonor = user;
            checkCanSend();
        }

        function checkCanSend() {
            const btn = document.getElementById('sendBtn');
            btn.disabled = !(selectedRequester && selectedDonor);
        }

        async function sendRequest() {
            const btn = document.getElementById('sendBtn');
            btn.disabled = true;
            btn.textContent = 'Sending...';

            const message = document.getElementById('messageBox').value;

            showResult('info', 'Creating request and sending email...');

            try {
                const response = await fetch('../api/requests.php?action=create_request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        requester_id: selectedRequester.id,
                        donor_id: selectedDonor.id,
                        message: message
                    })
                });

                const result = await response.json();
                
                if (result.status === 'success') {
                    let message = `
                        <strong>✅ SUCCESS!</strong><br><br>
                        <strong>Request Created:</strong> ${result.id}<br>
                        <strong>Email Sent:</strong> ${result.email_sent ? '✅ Yes' : '❌ No'}<br><br>
                        <strong>Details:</strong><br>
                        • Requester: ${selectedRequester.name} (${selectedRequester.email})<br>
                        • Donor: ${selectedDonor.name} (${selectedDonor.email})<br>
                        • Blood Type: ${selectedRequester.blood_type}<br><br>
                    `;
                    
                    if (result.email_sent) {
                        message += `<strong>📧 Email has been sent to ${selectedDonor.email}</strong><br>`;
                        message += `Please check the inbox (and spam folder) for the notification email.`;
                    } else {
                        message += `<strong>⚠️ Email failed to send!</strong><br>`;
                        message += `Check the PHP error logs for details.`;
                    }
                    
                    showResult('success', message);
                } else {
                    showResult('error', `Failed: ${result.message}<pre>${JSON.stringify(result, null, 2)}</pre>`);
                }

            } catch (error) {
                showResult('error', `Error: ${error.message}`);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Send Blood Request & Email';
            }
        }

        function showResult(type, message) {
            const result = document.getElementById('result');
            result.className = `result ${type}`;
            result.innerHTML = message;
            result.style.display = 'block';
        }

        document.getElementById('sendBtn').onclick = sendRequest;

        // Load users on page load
        loadUsers();
    </script>
</body>
</html>
