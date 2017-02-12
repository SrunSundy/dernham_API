<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class UserRestController extends REST_Controller{
	
	function __construct()
	{
		parent::__construct();
		if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$this->load->model("EmailModel");
	}
	
	function sendtest_post(){
		
		$responsequery = $this->EmailModel->sendtest();
		$this->response($responsequery , 200);
	}
	
}

?>