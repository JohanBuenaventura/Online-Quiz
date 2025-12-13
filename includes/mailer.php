<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../assets/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../assets/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../assets/phpmailer/src/Exception.php';

/**
 * Send an email using PHPMailer via Hostinger SMTP
 * Reads SMTP settings from includes/config.php
 * or falls back to Hostinger defaults.
 */
function sendMail($toEmail, $subject, $htmlMessage) {
    $mail = new PHPMailer(true);

    // Load config if available
    $cfg = [];
    if (file_exists(__DIR__ . '/config.php')) {
        $cfg = require __DIR__ . '/config.php';
    }

    // Use Hostinger email config if not set in config.php
    $host = $cfg['smtp_host'] ?? 'smtp.hostinger.com';
    $user = $cfg['smtp_user'] ?? 'oquizsystem@onlinequizsystem.online';
    $pass = $cfg['smtp_pass'] ?? '-Johan09957832872'; // replace with your actual password
    $port = $cfg['smtp_port'] ?? 465;
    $secure = $cfg['smtp_secure'] ?? 'ssl';
    $from = $cfg['smtp_from'] ?? 'oquizsystem@onlinequizsystem.online';
    $fromName = $cfg['smtp_from_name'] ?? 'Online Quiz System';

    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $user;
        $mail->Password = $pass;

        // Set encryption
        if (strtolower($secure) === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port = $port;

        // Sender & recipient
        $mail->setFrom($from, $fromName);
        $mail->addAddress($toEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;

        return $mail->send();

    } catch (Exception $e) {
        error_log('Mail error: ' . ($mail->ErrorInfo ?? '') . ' | Exception: ' . $e->getMessage());
        return false;
    }
}
