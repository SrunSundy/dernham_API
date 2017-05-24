<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class LocationRestController extends REST_Controller{

	public function __construct() {
	
		parent::__construct();
	
		if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$this->load->model('LocationModel');
	
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	public function listcountry_get(){
		
		//row=20&
		//page=2
		//srch_name= a
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		$request["srch_name"] = $this->input->get('srch_name');
		
		$responsequery = $this->LocationModel->listCountry($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_data"] = $responsequery["response_data"];
		$this->response($response, 200);
	}
	
	public function listcity_get(){
		
		//row=20&
		//page=2
		//srch_name= a
		//country_id = 1
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		$request["srch_name"] = $this->input->get('srch_name');
		
		$responsequery = $this->LocationModel->listCity($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_data"] = $responsequery["response_data"];
		$this->response($response, 200);
	}
	
	public function listdistrict_get(){
		
		//row=20&
		//page=2
		//srch_name= a
		//city_id = 1
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		$request["srch_name"] = $this->input->get('srch_name');
		
		$responsequery = $this->LocationModel->listDistrict($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_data"] = $responsequery["response_data"];
		$this->response($response, 200);
	}
	
	public function listcommune_get(){
		
		//row=20&
		//page=2
		//srch_name= a
		//district_id = 1
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		$request["srch_name"] = $this->input->get('srch_name');
		
		$responsequery = $this->LocationModel->listCommune($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_data"] = $responsequery["response_data"];
		$this->response($response, 200);
	}
	
}

?>