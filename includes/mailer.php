<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/environment.php';

/**
 * Send an email using PHPMailer and SMTP settings from .env
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body (plain text or HTML)
 * @param bool $isHtml Whether the message is HTML
 * @return bool True on success, False on failure
 */
function sendEmail($to, $subject, $message, $isHtml = false) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

        // Recipients
        $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? ($_ENV['SMTP_USER'] ?? 'no-reply@ocnhs.edu.ph');
        $fromName  = $_ENV['SMTP_FROM_NAME'] ?? 'OCNHS EMIS Office';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        // Content
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
