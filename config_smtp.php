<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // adjust if needed

function sendMailSMTP($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';        // e.g. smtp.gmail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cmtchotels@gmail.com';   // your SMTP email
        $mail->Password   = 'guwj hmyc czgy qfex';     // app password, not Gmail login
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender
        $mail->setFrom('cmtchotels@gmail.com', 'Yoga Bhawna Missions');
        $mail->addAddress($to);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
