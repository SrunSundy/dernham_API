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
	
	function sendEmail( $request ){
		$to = $request["email"];
		$subject = "DerNham";		
		$message = "
				<html>
					<head>
						<title>Dernham sends you the verification code to access to your account</title>
					</head>
					<body>
						<p>Your verification code: <strong>".$request["verification_code"]."</strong></p>						
					</body>
				</html>
				";
		
		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		
		// More headers
		$headers .= 'From: DerNham <webmaster@example.com>' . "\r\n";
		$headers .= 'Cc: myboss@example.com' . "\r\n";
		
		return mail($to,$subject,$message,$headers);
	}
	
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