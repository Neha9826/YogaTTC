<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Get form fields safely
$name    = htmlspecialchars($_POST['name'] ?? '');
$phone   = htmlspecialchars($_POST['phone'] ?? '');
$email   = htmlspecialchars($_POST['email'] ?? '');
$service = htmlspecialchars($_POST['service'] ?? '');
$date    = htmlspecialchars($_POST['date'] ?? '');

if (empty($name) || empty($phone) || empty($email)) {
    die("Please fill all fields.");
}

$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';     // Replace if using another SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'cmtchotels@gmail.com'; // 🔹 Your Gmail address
    $mail->Password   = 'guwj hmyc czgy qfex';    // 🔹 Gmail App Password (not your real password)
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Sender and receiver
    $mail->setFrom('cmtchotels@gmail.com', 'Shivoham Retreat Website');
    $mail->addAddress('cmtchotels@gmail.com', 'Admin'); // You receive it here
    $mail->addReplyTo($email, $name); // So you can directly reply to user

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Quick Query Submission';
    $mail->Body    = "
        <h3>New Quick Query Submission</h3>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Phone:</strong> {$phone}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Service Type:</strong> {$service}</p>
        <p><strong>Preferred Date:</strong> {$date}</p>
    ";
    $mail->AltBody = "Name: $name\nPhone: $phone\nEmail: $email\nService: $service\nDate: $date";

    $mail->send();

    echo "<script>
            alert('Thank you! Your query has been submitted successfully.');
            window.location.href = 'index.html';
          </script>";

} catch (Exception $e) {
    echo "Message could not be sent. Error: {$mail->ErrorInfo}";
}
?>
