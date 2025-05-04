<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendResetRequestEmail($recipientEmail, $subject, $body, $isHTML = true) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'neukai.organization@gmail.com';
        $mail->Password   = 'qrohlmqphppzqhmh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('neukai.organization@gmail.com', 'Neukai Team');
        $mail->addAddress($recipientEmail); 

        $mail->isHTML($isHTML);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        if (!$isHTML) {
            $mail->AltBody = strip_tags($body);
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function prepareUserResetEmail($email, $role, $ticketId) {
    $subject = "Password Reset Request Received";
    $body = "
        <h2>Password Reset Request</h2>
        <p>We've received your password reset request for your <strong>{$role}</strong> account (<strong>{$email}</strong>).</p>
        <p>Your request (Ticket ID: <strong>{$ticketId}</strong>) is now pending admin approval.</p>
        <p>You'll receive another email once your request has been processed.</p>
        <br>
        <p>Thank you for your patience,</p>
        <p><strong>NEUKAI Team</strong></p>
    ";

    return [
        'subject' => $subject,
        'body' => $body
    ];
}
?>