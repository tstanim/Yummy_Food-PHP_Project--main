<?php
session_start();
require '../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../include/env.php';

$id = $_GET['id'];

// Fetch email
$select_email = "SELECT email FROM table_book_orders WHERE id = $id";
$email_result = mysqli_query($conn, $select_email);
$email_data = mysqli_fetch_assoc($email_result);

// Fetch other and contact
$select_other = "SELECT * FROM other_section";
$other_result = mysqli_query($conn, $select_other);
$other = mysqli_fetch_assoc($other_result);

// Fetch message info
$subject = $_POST['subject'] ?? 'Booking Canceled';
$body = $_POST['body'] ?? 'Your booking has been canceled.';
$recipient = $_POST['email'] ?? $email_data['email'];

// Delete the booking
$delete = "DELETE FROM table_book_orders WHERE id = $id";
$deleted = mysqli_query($conn, $delete);

if ($deleted && isset($_POST['submit'])) {
    $mail = new PHPMailer(true);
    try {
        // SMTP config for Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'siddiquetanim@gmail.com';  // Your Gmail
        $mail->Password   = 'ybpr ombs kwmy aadt';        // App password from Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom('siddiquetanim@gmail.com', $other['r_name']);
        $mail->addAddress($recipient); // Recipient email

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        $_SESSION['success'] = "Table order canceled and email sent.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Email could not be sent. Error: {$mail->ErrorInfo}";
    }

    header("Location: ../backend_files/table_book_order.php");
    exit();
} else {
    $_SESSION['error'] = "Failed to cancel table booking.";
    header("Location: ../backend_files/table_book_order.php");
    exit();
}
