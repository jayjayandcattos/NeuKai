<?php
/**
 * Donor-facing mail.
 */

use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/mailer.php';

if (!function_exists('sendDonorRegistrationEmail')) {
    function sendDonorRegistrationEmail($recipientEmail, $donorFullName)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($recipientEmail, $donorFullName);
            $mail->isHTML(true);
            $mail->Subject = 'Donor Registration Successful';
            $mail->Body = '
                Hi <strong>' . e($donorFullName) . '</strong>,<br><br>
                Thank you for registering as a donor with Neukai.<br>
                Your account is currently <strong>pending approval</strong>.<br><br>
                We appreciate your willingness to help.<br><br>
                Best regards,<br>
                <strong>Neukai Team</strong>
            ';
            $mail->send();
        } catch (Exception $e) {
            error_log('Donor registration mail error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('sendDonationEmail')) {
    function sendDonationEmail($recipientEmail, $recipientName, $subject, $body, $isHTML = true)
    {
        try {
            $mail = neukai_mailer();
            $mail->addAddress($recipientEmail, $recipientName);
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body;

            if (!$isHTML) {
                $mail->AltBody = strip_tags($body);
            }

            $mail->send();

            return true;
        } catch (Exception $e) {
            error_log('Donation mail error: ' . $e->getMessage());

            return false;
        }
    }
}

if (!function_exists('prepareDonationEmail')) {
    function prepareDonationEmail($conn, $donator_id, $charity_id, $donation_id)
    {
        $donor_stmt = $conn->prepare('SELECT email, first_name, last_name FROM tbl_donor WHERE donator_id = ?');
        $donor_stmt->bind_param('i', $donator_id);
        $donor_stmt->execute();
        $donor = $donor_stmt->get_result()->fetch_assoc();
        $donor_stmt->close();

        $charity_stmt = $conn->prepare('SELECT charity_name, email FROM tbl_charity WHERE charity_id = ?');
        $charity_stmt->bind_param('i', $charity_id);
        $charity_stmt->execute();
        $charity = $charity_stmt->get_result()->fetch_assoc();
        $charity_stmt->close();

        $donation_stmt = $conn->prepare('SELECT donation_name, donation_date FROM tbl_donations WHERE donation_id = ?');
        $donation_stmt->bind_param('i', $donation_id);
        $donation_stmt->execute();
        $donation = $donation_stmt->get_result()->fetch_assoc() ?: [];
        $donation_stmt->close();

        if (!$donor || !$charity) {
            return null;
        }

        $donorName = trim(($donor['first_name'] ?? '') . ' ' . ($donor['last_name'] ?? ''));
        $donationName = $donation['donation_name'] ?? 'your donation';
        $donationDate = $donation['donation_date'] ?? date('F j, Y');

        $donor_subject = 'Your Donation to ' . $charity['charity_name'] . ' Has Been Submitted';
        $donor_body = '
            <h2>Thank You for Your Donation, ' . e($donor['first_name']) . '!</h2>
            <p>Your donation <strong>' . e($donationName) . '</strong> to <strong>' . e($charity['charity_name']) . '</strong> has been successfully submitted on ' . e($donationDate) . '.</p>
            <p>The charity will review your donation and contact you for further details if needed.</p>
            <p>You can track the status of your donation through your NEUKAI donor account.</p>
            <br>
            <p>With gratitude,</p>
            <strong>Neukai Team</strong>
        ';

        $charity_subject = 'New Donation Received from ' . $donorName;
        $charity_body = '
            <h2>New Donation Received!</h2>
            <p>You have received a new donation from <strong>' . e($donorName) . '</strong>.</p>
            <p>Please log in to your NEUKAI charity account to review and process this donation.</p>
            <br>
            <p>Sincerely,</p>
            <strong>Neukai Team</strong>
        ';

        return [
            'donor_email' => $donor['email'],
            'donor_name' => $donorName,
            'donor_subject' => $donor_subject,
            'donor_body' => $donor_body,
            'charity_email' => $charity['email'],
            'charity_name' => $charity['charity_name'],
            'charity_subject' => $charity_subject,
            'charity_body' => $charity_body,
        ];
    }
}
