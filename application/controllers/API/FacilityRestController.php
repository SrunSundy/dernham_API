<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class FacilityRestController extends REST_Controller{
	
	function __construct()
	{
		parent::__construct();
		if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$this->load->model("FacilityModel");
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	public function listfacility_get(){
		
		//row=20&
		//page=2
		//srch_key=abc
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		$request["srch_key"] = $this->input->get('srch_key');
		
		$response_data = $this->FacilityModel->listFacility($request);
		$response = $response_data;
		$response["response_code"] = "200";
		
		$this->response($response, 200);
	}
	
	
}

?>