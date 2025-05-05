<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

function sendDonorRegistrationEmail($recipientEmail, $donorFullName) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'neukai.organization@gmail.com';
        $mail->Password   = 'qrohlmqphppzqhmh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('neukai.organization@gmail.com', 'Neukai Team');
        $mail->addAddress($recipientEmail, $donorFullName);

        $mail->isHTML(true);
        $mail->Subject = 'Donor Registration Successful';
        $mail->Body = "
            Hi <strong>" . htmlspecialchars($donorFullName) . "</strong>,<br><br>
            Thank you for registering as a donor with Neukai.<br>
            Your account is currently <strong>pending approval</strong>.<br><br>
            We appreciate your willingness to help.<br><br>
            Best regards,<br>
            <strong>Neukai Team</strong>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Donor Registration Email Error: " . $mail->ErrorInfo);
    }
}
function sendDonationEmail($recipientEmail, $recipientName, $subject, $body, $isHTML = true) {
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
        $mail->addAddress($recipientEmail, $donorFullName);

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

function prepareDonationEmail($conn, $donator_id, $charity_id, $donation_id) {

    $donor_stmt = $conn->prepare("SELECT email, first_name, last_name FROM tbl_donor WHERE donator_id = ?");
    $donor_stmt->bind_param("i", $donator_id);
    $donor_stmt->execute();
    $donor_result = $donor_stmt->get_result();
    $donor = $donor_result->fetch_assoc();
    $donor_stmt->close();

    $charity_stmt = $conn->prepare("SELECT charity_name, email FROM tbl_charity WHERE charity_id = ?");
    $charity_stmt->bind_param("i", $charity_id);
    $charity_stmt->execute();
    $charity_result = $charity_stmt->get_result();
    $charity = $charity_result->fetch_assoc();
    $charity_stmt->close();


    $donor_subject = "Your Donation to {$charity['charity_name']} Has Been Submitted";
    $donor_body = "
        <h2>Thank You for Your Donation, {$donor['first_name']}!</h2>
        <p>Your donation <strong>{$donation['donation_name']}</strong> to <strong>{$charity['charity_name']}</strong> has been successfully submitted on {$donation['donation_date']}.</p>
        <p>The charity will review your donation and contact you for further details if needed.</p>
        <p>You can track the status of your donation through your NEUKAI donor account.</p>
        <br>
        <p>With gratitude,</p>
        <strong>Neukai Team</strong>
    ";

    $charity_subject = "New Donation Received from {$donor['first_name']} {$donor['last_name']}";
    $charity_body = "
        <h2>New Donation Received!</h2>
        <p>You have received a new donation from <strong>{$donor['first_name']} {$donor['last_name']}</strong>.</p>
        <p>Donation Details:</p>
        <p>Please log in to your NEUKAI charity account to review and process this donation.</p>
        <br>
        <p>Sincerely,</p>
        <strong>Neukai Team</strong>
    ";

    return [
        'donor_email' => $donor['email'],
        'donor_name' => $donor['first_name'] . ' ' . $donor['last_name'],
        'donor_subject' => $donor_subject,
        'donor_body' => $donor_body,
        'charity_email' => $charity['email'],
        'charity_name' => $charity['charity_name'],
        'charity_subject' => $charity_subject,
        'charity_body' => $charity_body
    ];
}