<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class ProductRestController extends REST_Controller{
	
	function __construct()
	{
		parent::__construct();
		if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$this->load->model("ProductModel");
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	public function listproduct_post(){
		
		/* {
		 "request_data" : {
			"row" : 10,
			"page": 1,
			"serve_category_id" : 0,
			"is_nearby" : false,
			"is_popular" : false,
			"country_id" : 0,
			"city_id" : 0,
			"district_id" : 0,
			"commune_id" : 0,
			"current_lat" : 11.565723328439192,
			"current_lng" : 104.88913536071777
			}
		} */
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		if(!isset($request["row"])) $request["row"] = 10;
		if(!isset($request["page"])) $request["page"] = 1;
		if(!isset($request["current_lat"])) $request["current_lat"] = 0;
		if(!isset($request["current_lng"])) $request["current_lng"] = 0;
		
		$responsequery = $this->ProductModel->listProduct($request);
		
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_code"] = "200";
		$response["response_data"] = $responsequery["response_data"];
		
		$this->response($response, 200);
	}
	
	
}

?>