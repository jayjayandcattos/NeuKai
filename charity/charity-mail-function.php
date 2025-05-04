<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

function sendDonationStatusEmail($recipientEmail, $donorName, $status, $charityName) {
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
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Donation Request ' . ucfirst($status);

        $statusMessage = $status === 'approved'
            ? "We’re happy to let you know that your donation request has been approved by $charityName!"
            : "Unfortunately, your donation request was not accepted by $charityName.";

        $mail->Body = "
            Hi <strong>" . htmlspecialchars($donorName) . "</strong>,<br><br>
            $statusMessage<br><br>
            Thank you for supporting our cause.<br><br>
            Best regards,<br>
            <strong>NEUKAI Team</strong>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Donation Email Error: " . $mail->ErrorInfo);
    }
}

function charityRegistrationEmail($recipientEmail, $charityName) {
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
        $mail->addAddress($recipientEmail, $charityName);

        $mail->isHTML(true);
        $mail->Subject = 'Charity Registration Successful';

        $mail->Body = "
            Hi <strong>" . htmlspecialchars($charityName) . "</strong>,<br><br>
            Thank you for registering your charity with us.<br>
            Your account is now pending verification by our team.<br><br>
            We'll notify you once it's approved.<br><br>
            Best regards,<br>
            <strong>NEUKAI Team</strong>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Charity Registration Email Error: " . $mail->ErrorInfo);
    }
}