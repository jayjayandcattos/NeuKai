<?php
/**
 * Charity-facing mail.
 */

use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/mailer.php';

if (!function_exists('sendDonationStatusEmail')) {
    function sendDonationStatusEmail($recipientEmail, $donorName, $status, $charityName)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($recipientEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Donation Request ' . ucfirst($status);

            $statusMessage = $status === 'approved'
                ? 'We are happy to let you know that your donation request has been approved by ' . e($charityName) . '!'
                : 'Unfortunately, your donation request was not accepted by ' . e($charityName) . '.';

            $mail->Body = '
                Hi <strong>' . e($donorName) . '</strong>,<br><br>
                ' . $statusMessage . '<br><br>
                Thank you for supporting our cause.<br><br>
                Best regards,<br>
                <strong>NEUKAI Team</strong>
            ';

            $mail->send();
        } catch (Exception $e) {
            error_log('Donation status mail error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('charityRegistrationEmail')) {
    function charityRegistrationEmail($recipientEmail, $charityName)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($recipientEmail, $charityName);
            $mail->isHTML(true);
            $mail->Subject = 'Charity Registration Successful';
            $mail->Body = '
                Hi <strong>' . e($charityName) . '</strong>,<br><br>
                Thank you for registering your charity with us.<br>
                Your account is now pending verification by our team.<br><br>
                We will notify you once it is approved.<br><br>
                Best regards,<br>
                <strong>NEUKAI Team</strong>
            ';
            $mail->send();
        } catch (Exception $e) {
            error_log('Charity registration mail error: ' . $e->getMessage());
        }
    }
}
