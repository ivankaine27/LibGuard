<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/Exception.php';
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $firstname = $_POST['firstname'];
  $title = $_POST['title'];
  $due_date = $_POST['due_date'];
  $status = $_POST['status'];
  $penaltyy = isset($_POST['penalty']) ? $_POST['penalty'] : '';
  $returned_date = date('M d, Y');
$days_late = (strtotime($returned_date) - strtotime($due_date)) / (60 * 60 * 24); // Calculate days late
if ($days_late <= 0) {
    // If returned_date is earlier than or equal to dueDate, penalty is 0
    $penalty = 0;
} else {
    // Penalty: 10 PHP per day late
    $penalty = 5 * $days_late;
}
$penaltyTotal += $penalty; // Accumulate penalty total


  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ivanbulaun2727@gmail.com';
    $mail->Password   = 'dmye wbxl behj vkuw';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('ivanbulaun2727@gmail.com', 'LibGuard System');
    $mail->addAddress($email);
    $mail->isHTML(true);
    
    if ($status == '<span class="label label-danger">not returned</span>') {
      // Send reminder email
      $mail->Subject = 'Reminder: Return Borrowed Book';
      $mail->Body    = 'Dear '.$firstname.',<br><br>This is a reminder to return the book "'.$title.'" before the due date ('.$due_date.') to avoid penalties.<br><br>Thank you.';
    } elseif ($status == '<span class="label label-danger">OVERDUE!</span>') {
      // Send urgent email
      $mail->Subject = 'Urgent: Return Overdue Book';
      $mail->Body    = 'Dear '.$firstname.',<br><br>This is an urgent reminder to return the book "'.$title.'" immediately. It is past its due date ('.$due_date.') and further penalties may apply. Please return it as soon as possible to avoid additional charges.<br><br>Penalty: '.$penaltyTotal.'<br><br>Thank you.';
    }

    $mail->send();
    echo 'Email sent successfully!';
  } catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }
} else {
  echo "Invalid request!";
}
?>
