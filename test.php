<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:/xampp/htdocs/bidmo/PHPMailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/bidmo/PHPMailer/src/SMTP.php';
require 'C:/xampp/htdocs/bidmo/PHPMailer/src/Exception.php';

$mail = new PHPMailer();

// Enable verbose debugging
$mail->SMTPDebug = 2;

// Set Gmail as the SMTP server
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;

// Gmail SMTP username and password
$mail->Username = 'remofalcone252@gmail.com';
$mail->Password = 'annucqcfeedghhab';

// Enable TLS encryption
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

// Sender and recipient details
$mail->setFrom('remofalcone252@gmail.com', 'Your Name');
$mail->addAddress('kazeynaval292003@gmail.com', 'Recipient Name');

// Email subject and body
$mail->Subject = 'Test Email via Gmail SMTP';
$mail->Body = 'This is a test email sent via Gmail SMTP using PHPMailer.';

// Send the email
if ($mail->send()) {
    echo 'Email sent successfully!';
} else {
    echo 'Failed to send email.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}
?>