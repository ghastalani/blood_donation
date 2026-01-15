# 🩸 VitalConnect - Email Notification Testing Guide

## ✅ What Was Fixed

### Changes Made:
1. **Enhanced `api/requests.php`** - Added detailed logging for email sending
2. **Enhanced `api/mailer.php`** - Added comprehensive logging to track SMTP operations
3. **Updated `api/profiles.php`** - Added endpoint to fetch users by role for testing
4. **Created test pages** - Multiple testing tools to verify email functionality

### Key Improvements:
- ✅ Email sending now logs every step to PHP error logs
- ✅ Response includes `email_sent` status (true/false)
- ✅ Detailed SMTP connection and authentication logging
- ✅ Better error handling and reporting

## 🧪 How to Test

### Method 1: Live Request Test (RECOMMENDED) ⭐

1. **Open the test dashboard:**
   ```
   http://localhost/vital-connect/api/
   ```

2. **Click "Live Request Test"** (🧪 icon)

3. **Follow the interactive steps:**
   - Select a requester from the list
   - Select a donor from the list
   - Optionally edit the message
   - Click "Send Blood Request & Email"

4. **Check the results:**
   - The page will show if the email was sent successfully
   - Check the donor's email inbox (and spam folder)

### Method 2: Test from the Actual Application

1. **Login as a requester** in your VitalConnect app

2. **Search for a donor:**
   - Go to the donor search page
   - Filter by blood type and city
   - Find an available donor

3. **Send a blood request:**
   - Click "Send Request" on a donor
   - Enter your message
   - Submit the request

4. **Verify email was sent:**
   - Check PHP error logs for confirmation
   - Check the donor's email inbox

### Method 3: Check PHP Error Logs

After sending a request, check the logs at:
```
C:\wamp64\logs\php_error.log
```

Look for these log entries:
```
VitalConnect: Attempting to send email to donor: [email]
=== sendBloodRequestEmail called ===
Recipient: [email]
Requester: [name]
Blood Type: [type]
SMTP Response: 220 smtp.gmail.com ESMTP...
SMTP Response: 235 2.7.0 Accepted
VitalConnect: Email sent successfully to [email]
```

## 📧 What the Donor Receives

The donor will receive an email like this:

```
From: VitalConnect Blood Bank <no-reply@vitalconnect.com>
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

### If Email is Not Sent:

1. **Check the API response:**
   - Look for `"email_sent": false` in the response
   - This means the email failed to send

2. **Check PHP error logs:**
   ```
   C:\wamp64\logs\php_error.log
   ```
   Look for:
   - `SMTP Socket Error` - Connection failed
   - `SMTP Auth Failed` - Wrong credentials
   - `No donor email found` - Donor has no email in database

3. **Verify SMTP credentials:**
   - Open `api/config.php`
   - Verify email and app password are correct
   - Ensure using Gmail app password (not regular password)

4. **Test SMTP directly:**
   - Go to `http://localhost/vital-connect/api/test_mail.php`
   - This tests SMTP without database dependencies

### Common Issues:

| Issue | Solution |
|-------|----------|
| `email_sent: false` | Check PHP error logs for details |
| No email in inbox | Check spam folder |
| SMTP Auth Failed | Verify app password in config.php |
| Connection timeout | Check firewall/antivirus blocking port 465 |
| No donor email found | Donor profile missing email address |

## 📊 Expected Behavior

### When a Request is Created:

1. ✅ Request saved to `donation_requests` table
2. ✅ Notification saved to `notifications` table
3. ✅ Email sent to donor immediately
4. ✅ Response includes `email_sent: true/false`
5. ✅ All steps logged to PHP error log

### Success Indicators:

- API returns: `{"status":"success","id":"xxx","email_sent":true}`
- PHP log shows: `VitalConnect: Email sent successfully to [email]`
- Donor receives email within seconds

### Failure Indicators:

- API returns: `{"status":"success","id":"xxx","email_sent":false}`
- PHP log shows: `VitalConnect: Email FAILED to send`
- Check logs for specific SMTP error

## 🎯 Testing Checklist

- [ ] Test 1: Simple email test passes (`test_mail.php`)
- [ ] Test 2: Full request flow test passes (`test_request_flow.php`)
- [ ] Test 3: Live request test sends email (`test_live_request.php`)
- [ ] Test 4: Real app request sends email
- [ ] Test 5: Email appears in donor's inbox
- [ ] Test 6: Email content is correct
- [ ] Test 7: PHP logs show success messages

## 📁 Modified Files

- `api/config.php` - SMTP credentials
- `api/mailer.php` - Enhanced logging
- `api/requests.php` - Email sending with status tracking
- `api/profiles.php` - Added role-based user fetching
- `api/test_live_request.php` - Interactive testing tool
- `api/index.html` - Testing dashboard

## 🚀 Next Steps

1. **Run the Live Request Test** to verify everything works
2. **Check the donor's email** to confirm delivery
3. **Test from the actual app** to ensure end-to-end functionality
4. **Monitor PHP logs** during testing to catch any issues

## 💡 Tips

- Always check **spam folder** if email doesn't appear in inbox
- Use the **Live Request Test** for quick verification
- Check **PHP error logs** for detailed debugging information
- The email is sent **after** the database transaction commits
- Email failure **does not** prevent request creation

---

**Need Help?**
- Check PHP error logs: `C:\wamp64\logs\php_error.log`
- Use test dashboard: `http://localhost/vital-connect/api/`
- Review SMTP config: `api/config.php`
