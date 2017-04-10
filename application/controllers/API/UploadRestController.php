<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class UploadRestController extends REST_Controller{	
	public function __construct() {
		
		parent::__construct();
		$this->load->model('UploadModel');		
		
	}
	
	public function index(){
		$this->load->view('index');
	}	
	
	function uploadsingleposttotemp_post(){
		$response = array();
		
		if (empty($_FILES)){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
			
		$request_upload["image_file"] = $_FILES;
		$upload_target = "./uploadimages/temp/";
		$response_data = $this->UploadModel->uploadSinglePostImage($request_upload, $upload_target);
		
		if($response_data["is_upload"]){
			$response["response_code"] = "200";
			$response["response_msg"] = $response_data["message"];
			$response["response_data"] = $response_data["filename"];
			$this->response($response, 200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = $response_data["message"];
			$response["response_data"] = $response_data["filename"];
			$this->response($response, 200);
		}
	}
	
	function uploadpostimagetotemp_post(){
		
		$response = array();
		
		if (empty($_FILES)){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
			
		$request_upload["image_file"] = $_FILES;
		$upload_target = "./uploadimages/temp/";		
		$response_data = $this->UploadModel->uploadMutilplePostImages($request_upload, $upload_target);
		
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
	
	
}
?>