<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class ServeCategoryRestController extends REST_Controller{
	
	function __construct()
	{
		parent::__construct();
		if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$this->load->model("ServeCategoryModel");
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	public function listServeCategory_get(){
		
		$response_data = $this->ServeCategoryModel->listServeCategory();
		$response["response_code"] = "200";
		$response["response_data"] = $response_data;
		
		$this->response($response, 200);
	}
	
	
}

?>