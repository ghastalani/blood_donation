<?php
/**
 * api/config.php
 * Configuration settings for the application
 */

define('SMTP_HOST', 'ssl://smtp.gmail.com'); // خادم SMTP Gmail
define('SMTP_PORT', 465);                     // المنفذ SSL
define('SMTP_USER', 'elghastalanibebanemedsaghir@gmail.com'); // إيميلك الشخصي الفعلي لإرسال الرسائل
define('SMTP_PASS', 'lehevufvesqbldvn');   // App password لإيميلك الشخصي (NO SPACES!)
define('SMTP_FROM_EMAIL', 'no-reply@vitalconnect.com'); // إيميل ظاهر للمتبرع (رسمي)
define('SMTP_FROM_NAME', 'VitalConnect Blood Bank');    // الاسم الظاهر للمتبرع


// عرض الأخطاء للـ log فقط (آمن للاستخدام في localhost)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/wamp64/logs/php_error.log');