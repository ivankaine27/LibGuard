<?php
	include 'includes/session.php';
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	
	require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/PHPMailer.php';
	require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/Exception.php';
	require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/SMTP.php';
	
	if(isset($_POST['add'])){
		$student = $_POST['student'];
		
		$sql = "SELECT * FROM students WHERE student_id = '$student'";
		$query = $conn->query($sql);
		if($query->num_rows < 1){
			if(!isset($_SESSION['error'])){
				$_SESSION['error'] = array();
			}
			$_SESSION['error'][] = 'Student not found';
		}
		else{
			$row = $query->fetch_assoc();
			$student_id = $row['id'];
			$student_email = $row['email'];
			$student_name = $row['firstname'];

			    // Send email to the student
					$mail = new PHPMailer(true);
					try {
							//Server settings
							$mail->isSMTP();
							$mail->Host       = 'smtp.gmail.com';  // Specify SMTP server
							$mail->SMTPAuth   = true;                  // Enable SMTP authentication
							$mail->Username   = 'ivanbulaun2727@gmail.com';   // SMTP username
							$mail->Password   = 'dmye wbxl behj vkuw';        // SMTP password
							$mail->SMTPSecure = 'tls';                 // Enable TLS encryption
							$mail->Port       = 587;                   // TCP port to connect to
			
							//Recipients
							$mail->setFrom('ivanbulaun2727@gmail.com', 'LibGuard System');
							$mail->addAddress($student_email);        // Add a recipient
			
							// Content
							$mail->isHTML(true);  // Set email format to HTML
							$mail->Subject = 'Book Returned Successfully';
							$mail->Body    = 'Dear Student,<br>The book has been returned successfully. <br> Thank you for using LibGuard!';
			
							$mail->send();
							echo 'Message has been sent';
					} catch (Exception $e) {
							echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
					}
			} 


			$return = 0;
			foreach($_POST['isbn'] as $isbn){
				if(!empty($isbn)){
					$sql = "SELECT * FROM books WHERE isbn = '$isbn'";
					$query = $conn->query($sql);
					if($query->num_rows > 0){
						$brow = $query->fetch_assoc();
						$bid = $brow['id'];

						$sql = "SELECT * FROM borrow WHERE student_id = '$student_id' AND book_id = '$bid' AND status = 0";
						$query = $conn->query($sql);
						if($query->num_rows > 0){
							$borrow = $query->fetch_assoc();
							$borrow_id = $borrow['id'];
							$sql = "INSERT INTO returns (student_id, book_id, date_return) VALUES ('$student_id', '$bid', NOW())";
							if($conn->query($sql)){
								$return++;
								$sql = "UPDATE books SET status = 0 WHERE id = '$bid'";
								$conn->query($sql);
								$sql = "UPDATE borrow SET status = 1 WHERE id = '$borrow_id'";
								$conn->query($sql);
							}
							else{
								if(!isset($_SESSION['error'])){
									$_SESSION['error'] = array();
								}
								$_SESSION['error'][] = $conn->error;
							}
						}
						else{
							if(!isset($_SESSION['error'])){
								$_SESSION['error'] = array();
							}
							$_SESSION['error'][] = 'Borrow details not found: ISBN - '.$isbn.', Student ID: '.$student;
						}

						

					}
					else{
						if(!isset($_SESSION['error'])){
							$_SESSION['error'] = array();
						}
						$_SESSION['error'][] = 'Book not found: ISBN - '.$isbn;
					}
		
				}
			}

			if($return > 0){
				$book = ($return == 1) ? 'Book' : 'Books';
				$_SESSION['success'] = $return.' '.$book.' successfully returned';
			}

		}
		
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: return.php');

?>