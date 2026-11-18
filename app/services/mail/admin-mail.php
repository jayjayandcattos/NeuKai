<?php
/**
 * Admin-facing mail: charity approval, donor status, delivery confirmation,
 * password-reset approval.
 */

use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/mailer.php';

if (!function_exists('sendApprovalEmail')) {
    function sendApprovalEmail($email, $charityname, $charityId)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($email, $charityname);
            $mail->isHTML(true);
            $mail->Subject = 'Charity Registration Approved - Welcome to Neukai!';
            $mail->Body = '
                Dear ' . e($charityname) . ',<br><br>
                Good day!<br><br>
                We are pleased to inform you that your charity registration request (ID: ' . e($charityId) . ') has been <b>approved</b> by the Neukai Team.<br><br>
                You may now log in and start using your charity account in the system.<br><br>
                Thank you for supporting our cause. We look forward to working with you!<br><br>
                Best regards,<br>
                <b>NEUKAI Team</b>
            ';
            $mail->send();
        } catch (Exception $e) {
            error_log('Approval mail error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('sendDeclineEmail')) {
    function sendDeclineEmail($email, $charityname, $charityId)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($email, $charityname);
            $mail->isHTML(true);
            $mail->Subject = 'Charity Registration Declined - Neukai Team';
            $mail->Body = '
                Dear ' . e($charityname) . ',<br><br>
                Good day!<br><br>
                We regret to inform you that your charity registration request (ID: ' . e($charityId) . ') has been <b>declined</b> after careful review.<br><br>
                If you have any questions or would like to clarify your application, feel free to contact us.<br><br>
                Thank you for your interest and understanding.<br><br>
                Best regards,<br>
                <b>NEUKAI Team</b>
            ';
            $mail->send();
        } catch (Exception $e) {
            error_log('Decline mail error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('addCharityMailer')) {
    function addCharityMailer($email, $charityname, $temporaryPassword)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($email, $charityname);
            $mail->isHTML(true);
            $mail->Subject = 'Charity Account Created - Welcome to Neukai!';
            $mail->Body = '
                Dear ' . e($charityname) . ',<br><br>
                Good day!<br><br>
                Your charity account has been successfully <b>registered</b> in the Neukai system by our admin team.<br><br>
                You can now log in using the following temporary credentials:<br><br>
                <b>Email:</b> ' . e($email) . '<br>
                <b>Temporary Password:</b> ' . e($temporaryPassword) . '<br><br>
                For security purposes, please make sure to <b>change your password immediately</b> after logging in.<br><br>
                Thank you for being part of our mission to support meaningful causes.<br><br>
                Best regards,<br>
                <b>NEUKAI Team</b>
            ';
            $mail->send();
        } catch (Exception $e) {
            error_log('Registration mail error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('sendDonorStatusEmail')) {
    function sendDonorStatusEmail($email, $name, $status)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Donor Account Status Update';

            if ($status === 'approved') {
                $mail->Body = '
                    Dear ' . e($name) . ',<br><br>
                    We are happy to inform you that your donor account has been <strong>approved</strong> by the Neukai Team.<br><br>
                    Thank you for your generosity and willingness to support charitable initiatives. You may now log in and begin contributing.<br><br>
                    Best regards,<br>
                    <b>NEUKAI Team</b>
                ';
            } elseif ($status === 'declined') {
                $mail->Body = '
                    Dear ' . e($name) . ',<br><br>
                    We appreciate your interest in joining our donor community. However, after careful review, your donor account request has been <strong>declined</strong>.<br><br>
                    If you believe this was a mistake or would like clarification, feel free to reach out to us.<br><br>
                    Sincerely,<br>
                    <b>NEUKAI Team</b>
                ';
            } else {
                return;
            }

            $mail->send();
        } catch (Exception $e) {
            error_log('Donor status mail error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('addDonorMailer')) {
    function addDonorMailer($email, $first_name, $temporaryPassword)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($email, $first_name);
            $mail->isHTML(true);
            $mail->Subject = 'Donor Account Created - Welcome to Neukai!';
            $mail->Body = '
                Dear ' . e($first_name) . ',<br><br>
                Your donor account has been successfully <strong>registered</strong> by our admin team.<br><br>
                You can log in to the system using the following temporary credentials:<br>
                <b>Email:</b> ' . e($email) . '<br>
                <b>Temporary Password:</b> ' . e($temporaryPassword) . '<br><br>
                Please <strong>change your password immediately</strong> after logging in to protect your account.<br><br>
                We are excited to have you onboard as a donor. Thank you for choosing to support meaningful causes with us.<br><br>
                Best regards,<br>
                <strong>NEUKAI Team</strong>
            ';
            $mail->send();
        } catch (Exception $e) {
            error_log('Registration mail error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('sendDeliveryConfirmationEmail')) {
    function sendDeliveryConfirmationEmail($toEmail, $toName, $donationName)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = 'Donation Successfully Delivered';
            $mail->Body = '
                Dear ' . e($toName) . ',<br><br>
                We are happy to inform you that your donation <strong>"' . e($donationName) . '"</strong> has been successfully <strong>delivered</strong> to the receiving charity.<br><br>
                Your kindness and generosity truly make a difference. Thank you for trusting Neukai to help you make an impact.<br><br>
                With gratitude,<br>
                <strong>NEUKAI Team</strong>
            ';
            $mail->send();
        } catch (Exception $e) {
            error_log('Delivery confirmation mail error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('sendResetApprovedEmail')) {
    function sendResetApprovedEmail($recipientEmail, $role, $temporaryPassword)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($recipientEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Approved';
            $mail->Body = '
                Hello,<br><br>
                Your password reset request for your <strong>' . e($role) . '</strong> account has been approved.<br><br>
                Your new temporary password is: <strong>' . e($temporaryPassword) . '</strong><br>
                Please log in and change your password immediately for security purposes.<br><br>
                Regards,<br>
                <strong>NEUKAI Team</strong>
            ';
            $mail->send();
        } catch (Exception $e) {
            error_log('Reset mail error: ' . $e->getMessage());
        }
    }
}
