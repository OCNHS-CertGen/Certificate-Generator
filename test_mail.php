<?php
require_once 'includes/mailer.php';

// Use a temporary email or your own for testing
$test_email = "johnwisdomdeguit@gmail.com";
$subject = "OCNHS SMTP Test";
$message = "If you are reading this, your SMTP settings in .env are working correctly!";

echo "Attempting to send test email to $test_email...<br>";

if (sendEmail($test_email, $subject, $message)) {
    echo "<h2 style='color:green;'>SUCCESS!</h2> Email sent. Please check your inbox (and spam folder).";
} else {
    echo "<h2 style='color:red;'>FAILED!</h2> Email could not be sent. <br>";
    echo "Possible reasons:<br>";
    echo "1. Incorrect SMTP_USER or SMTP_PASS in .env<br>";
    echo "2. You need to use a Google 'App Password' instead of your regular password.<br>";
    echo "3. Your firewall or ISP is blocking port 587.";
}
