<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class UploadEncodeRestController extends REST_Controller{	
	public function __construct() {
		
		parent::__construct();
		$this->load->model('UploadEncodeModel');		
		
	}
	
	public function index(){
		$this->load->view('index');
	}	
	
	function uploadpostimagetotemp_post(){
		
		$response = array();		
		$request = json_decode($this->input->raw_input_stream,true);
		$request = $request["request_data"];		
		$request_data["image_data"] = $request["image_data"];
	
		$request_data["target_file"] = "./uploadimages/temp/";		
		$response_data = $this->UploadEncodeModel->uploadMultipleImages($request_data);
		
		if($response_data["is_upload"]){
			$response["response_code"] = "200";
			$response["response_msg"] = $response_data["message"];
			$response["response_data"] = $response_data["fileupload"];
			$this->response($response, 200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = $response_data["message"];
			$response["response_data"] = $response_data["fileupload"];
			$this->response($response, 200);
		}   
		
	}
	
	function uploaduserphoto_post(){
		
		$response = array();
		$request = json_decode($this->input->raw_input_stream,true);
		$request = $request["request_data"];
		$request_data["image_data"] = $request["image_data"];

		$request_data["target_file"] = "./uploadimages/user/";
		$response_data = $this->UploadEncodeModel->uploadSingleImage($request_data);
		
		if($request_data["is_upload"]){
			$response["response_code"] = "200";
			$response["response_msg"] = $response_data["message"];
			$response["response_data"] = $response_data["fileupload"];
			$this->response($response, 200);
			
		}else{
			
			$response["response_code"] = "000";
			$response["response_msg"] = $response_data["message"];
			$response["response_data"] = $response_data["fileupload"];
			$this->response($response, 200);
		}
	}
	
	
}
?>