# Email Notification Setup - VitalConnect

## ✅ Configuration Complete

The email notification system has been successfully configured with the following settings:

### SMTP Configuration
- **Host:** ssl://smtp.gmail.com
- **Port:** 465
- **Email:** 24046@supnum.mr
- **App Password:** lehe vufv esqb ldvn

### Files Modified
1. **api/config.php** - Updated with Gmail SMTP credentials
2. **api/mailer.php** - Enhanced with SSL support and better error handling

### How It Works

When a blood request is created:
1. The requester submits a request through the frontend
2. The system creates a record in `donation_requests` table
3. A notification is stored in the `notifications` table
4. **An email is immediately sent to the donor** with:
   - Requester's name
   - Blood type needed
   - Link to login and respond

## 🧪 Testing Instructions

### Option 1: Simple Email Test
1. Open your browser
2. Navigate to: `http://localhost/vital-connect/api/test_mail.php`
3. This will send a test email to 24046@supnum.mr
4. Check the inbox (and spam folder)

### Option 2: Full Request Flow Test
1. Open your browser
2. Navigate to: `http://localhost/vital-connect/api/test_request_flow.php`
3. This will:
   - Find test users in your database
   - Create a real blood request
   - Send an email notification
   - Show detailed results

### Option 3: Real-World Test
1. Login as a requester in the application
2. Search for a donor
3. Send a blood request
4. The donor should receive an email immediately
5. Check the donor's email inbox

## 📧 Email Content

The donor will receive an email with this format:

```
Subject: VitalConnect Hero: You have a new blood request

Hello,

You have a new blood request – save a life!

Request Details:
----------------
• Requester Name: [Name]
• Blood Type Needed: [Blood Type]

Please login to your dashboard to view full details and respond:
http://localhost/login

Best regards,
VitalConnect Team
```

## 🔍 Troubleshooting

### If emails are not being sent:

1. **Check SMTP credentials:**
   - Verify the app password is correct
   - Ensure the email account has "Less secure app access" enabled (if required)
   - For Gmail, ensure 2-factor authentication is enabled and you're using an App Password

2. **Check PHP error logs:**
   - WAMP error logs: `C:\wamp64\logs\php_error.log`
   - Look for "SMTP" related errors

3. **Test SMTP connection:**
   - Use the test pages mentioned above
   - Check the detailed output for error messages

4. **Firewall/Antivirus:**
   - Ensure port 465 is not blocked
   - Temporarily disable firewall to test

### Common Issues:

- **"SMTP Auth Failed"**: Wrong username or password
- **"Connection timeout"**: Port 465 might be blocked
- **"SSL connection failed"**: SSL context issue (already handled in the code)

## 🎯 Production Checklist

Before going live:
- [ ] Test email sending with test_mail.php
- [ ] Test full request flow with test_request_flow.php
- [ ] Create a real request through the UI and verify email delivery
- [ ] Check spam folder if emails don't appear in inbox
- [ ] Verify email content is correct
- [ ] Test with different email providers (Gmail, Outlook, etc.)

## 📝 Notes

- Emails are sent **asynchronously** after the request is created
- If email sending fails, the request is still created (email failure doesn't block the request)
- All SMTP responses are logged to PHP error logs for debugging
- The system uses native PHP sockets (no external libraries required)

## 🔗 Related Files

- `api/config.php` - SMTP configuration
- `api/mailer.php` - Email sending logic
- `api/requests.php` - Request creation (lines 92-96 handle email sending)
- `api/test_mail.php` - Simple email test
- `api/test_request_flow.php` - Complete flow test
