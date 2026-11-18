<?php
/**
 * Password-reset request mail.
 */

use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/mailer.php';

if (!function_exists('sendResetRequestEmail')) {
    function sendResetRequestEmail($recipientEmail, $subject, $body, $isHTML = true)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($recipientEmail);
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body;

            if (!$isHTML) {
                $mail->AltBody = strip_tags($body);
            }

            $mail->send();

            return true;
        } catch (Exception $e) {
            error_log('Reset request mail error: ' . $e->getMessage());

            return false;
        }
    }
}

if (!function_exists('prepareUserResetEmail')) {
    function prepareUserResetEmail($email, $role, $ticketId)
    {
        $subject = 'Password Reset Request Received';
        $body = '
            <h2>Password Reset Request</h2>
            <p>We have received your password reset request for your <strong>' . e($role) . '</strong> account (<strong>' . e($email) . '</strong>).</p>
            <p>Your request (Ticket ID: <strong>' . e($ticketId) . '</strong>) is now pending admin approval.</p>
            <p>You will receive another email once your request has been processed.</p>
            <br>
            <p>Thank you for your patience,</p>
            <p><strong>NEUKAI Team</strong></p>
        ';

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
}
