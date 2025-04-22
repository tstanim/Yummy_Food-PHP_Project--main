<?php
session_start();
include '../include/env.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';  // Make sure to include the autoload file for PHPMailer

if (isset($_POST['submit'])) {
    //* Assigning request
    $message_name = $_POST['message_name'];
    $message_email = $_POST['message_email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $errors = [];

    //* Validation
    if (empty($message_name)) {
        $errors['message_name_error'] = "Please Enter Your Name";
    }
    if (empty($message_email)) {
        $errors['message_email_error'] = "Please Enter Your Email";
    }
    if (empty($subject)) {
        $errors['subject_error'] = "Please Enter Your Subject";
    }
    if (empty($message)) {
        $errors['message_error'] = "Please Enter Message";
    }

    if (count($errors) > 0) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_data'] = $_POST;
        header("Location: ../index.php#send-message");
    } else {
        // Insert message into the database
        $insert = "INSERT INTO message (name, email, subject, message) VALUES ('$message_name', '$message_email', '$subject', '$message')";
        $data = mysqli_query($conn, $insert);

        if ($data) {
            $_SESSION['success'] = "Your message has been sent. Thank you!";

            // Send email to admin using PHPMailer
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();                                           // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                        // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                    // Enable SMTP authentication
                $mail->Username   = 'siddiquetanim@gmail.com';                  // SMTP username
                $mail->Password   = 'ybpr ombs kwmy aadt';                   // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          // Enable TLS encryption
                $mail->Port       = 587;                                     // TCP port to connect to

                // Recipients
                $mail->setFrom('your-email@gmail.com', 'Your Name');
                $mail->addAddress('siddiquetanim@gmail.com', 'Admin');      // Admin's email

                // Content
                $mail->isHTML(true);                                          // Set email format to HTML
                $mail->Subject = "New Message From: $message_name";
                $mail->Body    = "You have received a new message from $message_name ($message_email):<br><br>
                                  <strong>Subject:</strong> $subject<br><br>
                                  <strong>Message:</strong><br>$message";

                $mail->send();
                $_SESSION['email_success'] = "An email notification has been sent to the admin!";
            } catch (Exception $e) {
                $_SESSION['email_error'] = "There was an error sending the email: {$mail->ErrorInfo}";
            }

            // Redirect back to the page with success message
            header("Location: ../index.php#send-message");
        } else {
            // Handle database insert error
            $_SESSION['errors']['db_error'] = "There was an error inserting your message into the database.";
            header("Location: ../index.php#send-message");
        }
    }
} else {
    echo 'Enter All Details and Submit Please';
}
