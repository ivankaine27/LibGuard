<?php
	include 'includes/session.php';

	if(isset($_GET['return'])){
		$return = $_GET['return'];
	}
	else{
		$return = 'index.php';
	}

	if(isset($_POST['save'])){
		$curr_password = $_POST['curr_password'];
		$username = $_POST['student_id'];
		$password = $_POST['password'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		if(password_verify($curr_password, $student['password'])){
			

			if($password == $student['password']){
				$password = $student['password'];
			}
			else{
				$password = password_hash($password, PASSWORD_DEFAULT);
			}

			$sql = "UPDATE students SET student_id = '$username', password = '$password', firstname = '$firstname', lastname = '$lastname'  WHERE id = '".$student['id']."'";
			if($conn->query($sql)){
				$_SESSION['success'] = 'Student profile updated successfully';
			}
			else{
				if($return == 'borrow.php' OR $return == 'return.php'){
					if(!isset($_SESSION['error'])){
						$_SESSION['error'] = array();
					}
					$_SESSION['error'][] = $conn->error;
				}
				else{
					$_SESSION['error'] = $conn->error;
				}
				
			}
			
		}
		else{
			if($return == 'borrow.php' OR $return == 'return.php'){
				if(!isset($_SESSION['error'])){
					$_SESSION['error'] = array();
				}
				$_SESSION['error'][] = 'Incorrect password';
			}
			else{
				$_SESSION['error'] = 'Incorrect password';
			}

		}
	}
	else{
		if($return == 'borrow.php' OR $return == 'return.php'){
			if(!isset($_SESSION['error'])){
				$_SESSION['error'] = array();
			}
			$_SESSION['error'][] = 'Fill up required details first';
		}
		else{
			$_SESSION['error'] = 'Fill up required details first';
		}
		
	}

	header('location:'.$return);

?>