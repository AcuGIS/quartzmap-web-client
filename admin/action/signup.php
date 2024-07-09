<?php
	session_start(['read_and_close' => true]);
	
	require('../incl/const.php');
	require('../class/database.php');
	require('../class/user.php');
	require('../class/signup.php');
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	require '../class/PHPMailer/Exception.php';
	require '../class/PHPMailer/PHPMailer.php';
	require '../class/PHPMailer/SMTP.php';

	function send_verify_email_smtp($name, $email, $url){
		try {
			//Create an instance; passing `true` enables exceptions
			$mail = new PHPMailer(true);

	    //TODO: move this to setup.php
			$mail->SMTPDebug = SMTP::DEBUG_OFF;                 //Enable verbose debug output
	    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;            //Enable verbose debug output
	    $mail->isSMTP();                                    //Send using SMTP
	    $mail->Host       = SMTP_HOST;                    	//Set the SMTP server to send through
	    $mail->SMTPAuth   = true;                           //Enable SMTP authentication
	    $mail->Username   = SMTP_USER;                   		//SMTP username
	    $mail->Password   = SMTP_PASS;                      //SMTP password
	    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //Enable implicit TLS encryption
	    $mail->Port       = SMTP_PORT;                      //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

	    //Recipients
	    $mail->setFrom(SMTP_USER, 'QuartzMap');
	    $mail->addAddress($email, $name);     //Add a recipient
	    //$mail->addAddress('ellen@example.com');               //Name is optional
	    $mail->addReplyTo(SMTP_USER, 'Information');
	    //$mail->addCC('cc@example.com');
	    //$mail->addBCC('bcc@example.com');

	    //Attachments
	    //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
	    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

	    //Content
	    $mail->isHTML(true);                                  //Set email format to HTML
	    $mail->Subject = 'QuartzMap Verification Email ';
			
			$email_html  = file_get_contents('../snippets/verify_email.html');
			$email_html = str_replace('USER_NAME', $name, $email_html);
			$email_html = str_replace('VERIFY_URL', $url, $email_html);
			$mail->Body    = $email_html;
	    $mail->AltBody = 'Click to verify your email '.$url;

	    $mail->send();
	    return true;
			
		} catch (Exception $e) {
		   //die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
			 return false;
		}
	}
	
	function send_verify_email($name, $email, $url){
		$to      = $email;
		$subject = 'QuartzMap Verification Email';
		$message = 'Hello '.$name.' here is your verification link for PostGIS Sync <a href="'.$url.'">'.$url.'</a>.';
		$headers = 'From: noreply@'.$_SERVER['SERVER_NAME'] . "\r\n" .
    'Reply-To: noreply@'.$_SERVER['SERVER_NAME'] . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

		mail($to, $subject, $message, $headers);
	}

	if(isset($_SESSION[SESS_USR_KEY])) {
    header("Location: ../../index.php");
  }
	
	$loc = '../../signup.php?err='.urlencode('Error: Bad request!');
	
  if(!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password']) ){
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
  	$obj = new signup_Class($database->getConn());
		$uobj = new user_Class($database->getConn(), SUPER_ADMIN_ID);
		
		$urow = $uobj->getByEmail($_POST['email']);
		if($urow){
			$loc = '../../signup.php?err='.urlencode('Error: Email is already registered!');
		}else{
		
			$_POST['verify'] = hash('sha256', $_POST['name'].$_POST['email'].$_POST['password'].date("D M j G:i:s T Y"));
			$_POST['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
			
			$newId = $obj->create($_POST);
			if($newId > 0){
				
				$verify_url = 'https://'.$_SERVER['SERVER_NAME'].str_replace('signup.php', 'verify.php', $_SERVER['REQUEST_URI'])
										. '?id='.$newId.'&verify='.urlencode($_POST['verify']);
				
				if(!send_verify_email_smtp($_POST['name'], $_POST['email'], $verify_url)){
					$obj->delete($newId);
					$loc = '../../signup.php?err='.urlencode('Error: Failed to signup!');
				}else{
					$loc = '../../login.php?msg='.urlencode('Your verification email has been sent!');
				}
			}else{	// error
				$loc = '../../signup.php?err='.urlencode('Error: Failed to signup!');
			}
		}
	}
	
	header('Location: '.$loc);
?>
