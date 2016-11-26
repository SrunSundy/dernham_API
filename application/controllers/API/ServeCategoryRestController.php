<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class ServeCategoryRestController extends REST_Controller{
	
	function __construct()
	{
		parent::__construct();
		$this->load->model("ServeCategoryModel");
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	public function listServeCategory_get(){
		
		header('Access-Control-Allow-Origin:*');
		$response_data = $this->ServeCategoryModel->listServeCategory();
		$response["response_code"] = "200";
		$response["response_data"] = $response_data;
		
		$this->response($response);
	}
	
	
}

?>