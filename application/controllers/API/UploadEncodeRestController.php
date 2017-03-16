<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class UploadEncodeRestController extends REST_Controller{	
	public function __construct() {
		
		parent::__construct();
		$this->load->model('UploadModel');		
		
	}
	
	public function index(){
		$this->load->view('index');
	}	
	
	function uploadpostimagetotemp_post(){
		
		$response = array();
		
		$request = json_decode($this->input->raw_input_stream,true);
		$request = $request["request_data"];
		
		$imageData = $request["image_data"];
		
		$data = explode(',',$imageData);
		 
		$output_file = base64_decode($data[1]);
		 
		// $imagesize = getimagesizefromstring($output_file);
		
		$source = @imagecreatefromstring($output_file);
		
		// $uri = 'data://application/octet-stream;base64,' . $data[1];
		 
		//$this->response( strlen(base64_decode($data[1])) , 200); 
			
		 $request_upload["image_file"] = $source;
		$upload_target = "./uploadimages/temp/";		
		$response_data = $this->UploadModel->test($request_upload, $upload_target);
		/*
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
		}   */
		
	}
	
	
}
?>