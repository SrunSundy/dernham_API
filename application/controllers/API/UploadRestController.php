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
		$upload_target = UPLOAD_FILE_PATH ."/uploadimages/temp/post/";
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
		$upload_target = UPLOAD_FILE_PATH ."/uploadimages/temp/post/";
		
		$response_data = array();
		if (is_array($_FILES["file"]["name"])){
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
		}else{ 
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
	}
	
	/* function movepostimagetoreal_post(){
	    
	    $request = json_decode($this->input->raw_input_stream,true);
	     
	    if(!isset($request["request_data"])){
	        $response["response_code"] = "400";
	        $response["error"] = "bad request";
	        $this->response($response, 400);
	        die();
	    }
	    $request = $request["request_data"]; 
	     if(!file_exists(UPLOAD_FILE_PATH ."/uploadimages/temp/post/small/4C2neExppj1500959996.jpg")){
	        $response["data"] = "File Not found";
	        $this->response($response, 200);
	        die();
	    } 
	    $arr = array('dQXF9A4pxF1500965900.jpg','YMNWvACcxd1500965893.jpg');
	    $response_data = $this->UploadModel->moveThreeTpyeImageToReal( UPLOAD_FILE_PATH ."/uploadimages/temp/post/",
	        UPLOAD_FILE_PATH ."/uploadimages/real/post/", $arr);
	  //  $s = rename(UPLOAD_FILE_PATH ."/uploadimages/temp/post/small/4C2neExppj1500959996.jpg",UPLOAD_FILE_PATH ."/uploadimages/real/post/small/4C2neExppj1500959996.jpg");
	    $response["data"] = $response_data;
	    $this->response($response, 200);
	} */
	
	function removeuserimagefromtemp_post(){
	    /*
	     {
    	     request_data : {
    	     'remove_image' : 'can be array or string'
    	     }
	     }
	     
	     */
	    
	    $request = json_decode($this->input->raw_input_stream,true);
	    
	    if(!isset($request["request_data"])){
	        $response["response_code"] = "400";
	        $response["error"] = "bad request";
	        $this->response($response, 400);
	        die();
	    }
	    $request = $request["request_data"];
	    
	    $is_remove = $this->UploadModel->removeThreeTypeImage( UPLOAD_FILE_PATH ."/uploadimages/temp/user/", $request["remove_image"]);
	    
	    if($is_remove){
	        $response["response_code"] = "200";
	        $response["response_msg"] = "File(s) is removed!";
	        $response["response_data"] = $is_remove;
	        
	        $this->response($response, 200);
	    }else{
	        $response["response_code"] = "000";
	        $response["response_msg"] = "Fail to remove!";
	        $response["response_data"] = $is_remove;
	        $this->response($response, 200);
	    }
	}
	
	function removepostimagefromtemp_post(){
	    /*
	     {
	       request_data : {
	           'remove_image' : 'can be array or string' 
	       }
	     }
	     
	     */
	    
	    $request = json_decode($this->input->raw_input_stream,true);
	    
	    if(!isset($request["request_data"])){
	        $response["response_code"] = "400";
	        $response["error"] = "bad request";
	        $this->response($response, 400);
	        die();
	    }
	    $request = $request["request_data"];
	    
	    $is_remove = $this->UploadModel->removeThreeTypeImage( UPLOAD_FILE_PATH ."/uploadimages/temp/post/", $request["remove_image"]);
	    
	    if($is_remove){
	        $response["response_code"] = "200";
	        $response["response_msg"] = "File(s) is removed!";
	        $response["response_data"] = $is_remove;
	        
	        $this->response($response, 200);
	    }else{
	        $response["response_code"] = "000";
	        $response["response_msg"] = "Fail to remove!";
	        $response["response_data"] = $is_remove;
	        $this->response($response, 200);
	    }
	    
	}
	
	function upload_profile_photo_totemp_post(){
		$response = array();
		
		if (empty($_FILES)){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
			
		$request_upload["image_file"] = $_FILES;
		$upload_target = UPLOAD_FILE_PATH ."/uploadimages/temp/user/";
		$response_data = $this->UploadModel->uploadUserPhoto($request_upload, $upload_target);
		
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
	
	
}
?>