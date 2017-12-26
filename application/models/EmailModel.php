<?php
class EmailModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
	}
	
	/* function send_mail() {
		$from_email = "jamesundy001@gmail.com";
		$to_email = $this->input->post('jame001sundy@gmail.com');
		 
		//Load email library
		$this->load->library('email');
		 
		$this->email->from($from_email, 'Your Name');
		$this->email->to($to_email);
		$this->email->subject('Email Test');
		$this->email->message('Testing the email class.');
		
		return $this->email->send();
		 
		 //Send mail
		if($this->email->send()){
			return true;
		}			
		else{
			return false;
		} 
			//$this->session->set_flashdata("email_sent","Error in sending Email.");
		//$this->load->view('email_form');
	} */
	
	function sentEmail($recipient){
	    
	
	    
	    $header= array('Content-Type: application/json', 'X-API-KEY:123456');
	    $urlapi="dev.dernham.com/service/API/EmailRestController/senddeliveryemail";
	    
	    $item["recipient_email"] = $recipient;
	    $request_data["request_data"] = $item;
	    
	   
	    
	    $ch = curl_init($urlapi);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data) );
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    
	    $result = json_decode(curl_exec($ch));
	    return $result;
	}
	
	function sendEmailForgetPassword( $request ){
		$to = $request["user_email"];
		$subject = "DerNham";	
		$head = "Dernham sent you your password.";
		$body = "Your password: <strong>".$request["user_password"]."</strong>.";
		
		$message = "
			<html>
				<head>
					<title>".$head."</title>
				</head>
				<body>
					<p>".$body."</p>						
				</body>
			</html>";
		
		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		
		// More headers
		$headers .= 'From: DerNham <noreply@dernham.com>' . "\r\n";
		//$headers .= 'Cc: myboss@example.com' . "\r\n";
		
		return mail($to,$subject,$message,$headers);
	}
	
	/*function sendEmail( $request ){
		$to = $request["email"];
		$subject = "DerNham";	
		$head = "Dernham sent you the verification code to activate to your account.";	
		$body = "Your verification code: <strong>".$request["verification_code"]."</strong>.";
		$message = "
			<html>
				<head>
					<title>".$head."</title>
				</head>
				<body>
					<p>".$body."</p>						
				</body>
			</html>	";
		
		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		
		// More headers
		$headers .= 'From: DerNham <noreply@dernham.com>' . "\r\n";
		//$headers .= 'Cc: myboss@example.com' . "\r\n";
		
		return mail($to,$subject,$message,$headers);
	}*/
	
	/* function sendtest(){
		$config = Array(
				'protocol' => 'smtp',
				'smtp_host' => 'ssl://smtp.googlemail.com',
				'smtp_port' => 465,
				'smtp_user' => 'jamekaka001@gmail.com', // change it to yours
				'smtp_pass' => 'tomato123', // change it to yours
				'mailtype' => 'html',
				'charset' => 'iso-8859-1',
				'wordwrap' => TRUE
		);
		
		$message = 'this is me';
		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");
		$this->email->from('jamekaka001@gmail.com'); // change it to yours
		$this->email->to('jame001sundy@gmail.com');// change it to yours
		$this->email->subject('Hello');
		$this->email->message($message);
		if($this->email->send())
		 {
		return 'Email sent.';
		}
		else
		{
		show_error($this->email->print_debugger());
		}   */
	//	return $this->email->send();
	
	
	//}
	
	
	
}
?>