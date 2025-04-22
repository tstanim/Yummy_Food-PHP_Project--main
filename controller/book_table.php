<?php
session_start();
include '../include/env.php';
require '../vendor/autoload.php';

$selects = "SELECT close_day FROM contact_section";
$datas = mysqli_query($conn, $selects);
$result = mysqli_fetch_assoc($datas);

// fetch other sec
$select_other = "SELECT * FROM other_section";
$datass = mysqli_query($conn, $select_other);
$other = mysqli_fetch_assoc($datass);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if (isset($_POST['submit'])) {
    //*assigning request
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $total_people = $_POST['total_people'];

    $errors = [];

    //*error
    $day = date('l', strtotime($date));

    if ($day == $result['close_day']) {
        $errors['date_error'] = "The restaurant will be closed on the date you selected. Please select another date. Thank you!";
    }
    if (empty($name)) {
        $errors['name_error'] = "Please Enter Your Name";
    }
    if (empty($email)) {
        $errors['email_error'] = "Please Enter Your Email";
    }
    if (empty($phone)) {
        $errors['phone_error'] = "Please Enter Your Phone";
    }
    if (empty($date)) {
        $errors['date_error'] = "Please Enter Date";
    }
    if (empty($time)) {
        $errors['time_error'] = "Please Enter Time";
    }
    if (empty($total_people)) {
        $errors['total_people_error'] = "Please Enter Total People";
    }

    if (strtotime($_POST['date']) < strtotime(date("Y-m-d"))) {
        $errors['date_error'] = "You cannot book a table for a past date.";
    }

    if (count($errors) > 0) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_data'] = $_POST;
        header("Location: ../index.php#book-a-table");
    } else {
        // Check if the selected date and time are already booked
        $select = "SELECT * FROM table_book_orders";
        $data = mysqli_query($conn, $select);
        $results = mysqli_fetch_all($data, 1);

        if (in_array($date, array_column($results, 'date')) and in_array($time, array_column($results, 'time'))) {
            $errors['date_error'] = "Please select another date or time. Thank You!";
            $errors['time_error'] = "This time the table has been booked. Please select another time. Thank You!";
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $_POST;
            header("Location: ../index.php#book-a-table");
        } else {
            $insert = "INSERT INTO table_book_orders (name, email, phone, date, time, total_people) VALUES ('$name','$email','$phone','$date','$time','$total_people')";
            $data = mysqli_query($conn, $insert);
            $_SESSION['success'] = "Your Table is Booked Successfully. Please Check Your Email. Thank You!";

            if ($data) {
                // Send confirmation email
                $mail = new PHPMailer(true);

                try {
                    // SMTP Settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'siddiquetanim@gmail.com'; 
                    $mail->Password = 'ybpr ombs kwmy aadt'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Email details
                    $mail->setFrom('your_email@gmail.com', 'Syntax Squad');
                    $mail->addAddress($email, $name);

                    $mail->isHTML(true);
                    $mail->Subject = "Your Table Has Been Booked!";
                    $mail->Body = "
                        <h2>Thank You, $name!</h2>
                        <p>Your table has been successfully booked.</p>
                        <ul>
                            <li><strong>Date:</strong> $date</li>
                            <li><strong>Time:</strong> $time</li>
                            <li><strong>People:</strong> $total_people</li>
                        </ul>
                        <p>We look forward to serving you at Suntax Squad's Yummy Food!</p>
                    ";

                    $mail->send();
                    header("Location: ../index.php#book-a-table");
                } catch (Exception $e) {
                    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
        }
    }
} else {
    //*error
    echo 'Enter All Details and Submit Please';
}
