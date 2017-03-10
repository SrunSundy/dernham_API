<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class UserRestController extends REST_Controller{
	
	function __construct()
	{
		parent::__construct();
		//if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
		//	$response["response_code"] = "400";
		//	$response["error"] = "bad request";
		//	$this->response($response, 400);
		//	die();
		//}
		
		$this->load->model("UserModel");
	}
	
	/* function sendVerifiedCode_post(){
		
		$sendemail = $this->EmailModel->sendEmail();		
		$this->response($sendemail , 200);
	} */
	
	function registeruser_post(){
		/* {
		 "request_data" : {
			"fullname" : "leap",
			"email" : "leap@gmail.com",
			"password" : "123"
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
		
		$this->load->helper('validate');
		if(!isset($request["email"]) || IsNullOrEmptyString($request["email"]) ||
		!isset($request["password"]) || IsNullOrEmptyString($request["password"]) || 
		!isset($request["fullname"]) || IsNullOrEmptyString($request["fullname"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		 
		$checkuser = $this->UserModel->checkIfUserexist($request);
		$is_exist = count($checkuser->user_status);
		if($is_exist >= 1){
			
			$msg = "user already exists. ";
			
			$this->load->helper('userstatus');
			switch((int)$checkuser->user_status){			
				case userstatus::Disabled : {
					$response["response_code"] = "000";
					$response["response_msg"] = $msg."user is disabled!";
					$this->response($response, 200);
					break;
				}
				case userstatus::Active :{
					$response["response_code"] = "200";
					$response["response_msg"] = $msg. "user is active";
					$this->response($response, 200);
					break;
				}
				case userstatus::Unauthorized :{
					$response["response_code"] = "001";
					$response["response_msg"] = $msg."user is unauthorized!";
					$this->response($response, 200);
					break;
				}
			}
		}else{
			$this->load->helper('dernhamutils');
			
			$verification_code = generateVerificationCode();
			
			$request["verification_code"] = $verification_code;
			$status = $this->UserModel->registerUser($request);
			if($status){
					
				$this->load->model("EmailModel");
				$sendemail = $this->EmailModel->sendEmail($request);
					
				if($sendemail){
					$response["response_code"] = "200";
					$response["response_msg"] = "Registration is successful!";
					$this->response($response ,200);
				}else{
					$response["response_code"] = "001";
					$response["response_msg"] = "Registration fails!";
					$this->response($response ,200);
				}
					
			}else{
				$response["response_code"] = "000";
				$response["response_msg"] = "Registration fails!";
				$this->response($response ,200);
			}
		}
		
		
			
	}
	
	function loginuser_post(){
		/* {
		 "request_data" : {
			"email" : "leap@gmail.com",
			"password" : "123"
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
		
		$this->load->helper('validate');
		if(!isset($request["email"]) || IsNullOrEmptyString($request["email"]) || 
		!isset($request["password"]) || IsNullOrEmptyString($request["password"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$data = $this->UserModel->loginUser($request);
		$has_user = count($data);	

		
		if($has_user >= 1){
		
			switch((int)$data->user_status){
				
				
				case userstatus::Disabled : {
					$response["response_code"] = "000";
					$response["response_msg"] = "user is disabled!";
					$this->response($response, 200);
					break;
				}
				case userstatus::Active :{
					$response["response_code"] = "200";
					$response["response_data"] = $data;
					$this->response($response, 200);
					break;
				}
				case userstatus::Unauthorized :{
					$response["response_code"] = "001";
					$response["response_msg"] = "user is unauthorized!";
					$this->response($response, 200);
					break;
				}
			}
		}else{
			$response["response_code"] = "002";
			$response["response_msg"] = "username or password is incorrect!";
			$this->response($response, 200);
		}
		
		
	}
	
	function check_fbuser_login_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		$this->load->helper('validate');
		if(!isset($request["fbid"]) || IsNullOrEmptyString($request["fbid"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$data = $this->UserModel->checkIfFBUserExist($request);
		$has_user = count($data);	

		
		if($has_user >= 1){
			$response["response_code"] = "200";
			$response["response_msg"] = "login successfully";
			$response["response_data"] = $data;
			$this->response($response ,200);
		
		}else{
			$data = $this->UserModel->registerFBUser($request);
			if($data){
				$response["response_code"] = "200";
				$response["response_msg"] = "Registration is successful!";
				$this->response($response ,200);
			}else{
				$response["response_code"] = "000";
				$response["response_msg"] = "Registration fails!";
				$this->response($response ,200);
			}
		}
		
		
	}
	
	function update_user_profile_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		$this->load->helper('validate');
		if(!isset($request["id"]) || IsNullOrEmptyString($request["id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$data = $this->UserModel->updateUserProfile($request);
		if($data){
			$data1 = $this->UserModel->getUserProfile($request);
				$response["response_code"] = "200";
				$response["response_msg"] = "Profile is updatd!";
				$response["response_data"] = $data1;
				$this->response($response ,200);
		}else{
				$response["response_code"] = "000";
				$response["response_msg"] = "Failed to update!";
				$this->response($response ,200);
		}
	}
	
	
	
	function update_user_photo_post(){
		
		$new_name = $this->generateRandomString(10);
		
		$this->load->model("UploadModel");
		$request_upload["image_file"] = $_FILES;
		$request_upload["new_name"] = $new_name;
		
		$send = $this->UploadModel->uploadUserPhoto($request_upload);
		
		$this->response($send ,200);
		
		//$request_data["update_param"] = "user_photo";
		//$request_data["update_value"] = "sssssssssssssssss.jpg";
		//$request_data["user_id"] = 38;
		//$data = $this->UserModel->updateUserProfileData($request_data);
		
		//if($data){
			
		//}else{
			//echo "Error update";
		//}
		
	}
	
	
	
	function generateRandomString($length) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString.time();
	}
	
	function get_user_profile_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		$this->load->helper('validate');
		if(!isset($request["follower_id"]) || IsNullOrEmptyString($request["follower_id"]) ||
			!isset($request["following_id"]) || IsNullOrEmptyString($request["following_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$data = $this->UserModel->getUserProfile($request);
		$data1 = $this->UserModel->getUserProfileFollow($request);
		if(count($data) == 1){
			$response["response_code"] = "200";
			$response["response_msg"] = "request successfully";
			$response["response_data"] = $data;
			$response["is_followed"] = $data1;
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "request failed!";
			$this->response($response ,200);
		}
	}
	
	function user_follow_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		$this->load->helper('validate');
		if(!isset($request["follower_id"]) || IsNullOrEmptyString($request["follower_id"]) ||
			!isset($request["following_id"]) || IsNullOrEmptyString($request["following_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
	
	
		$data = $this->UserModel->reqUserFollow($request);
		if($data){
			$response["response_code"] = "200";
			$response["response_msg"] = "follow successful!";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "follow fails!";
			$this->response($response ,200);
		}
	}
	
	function user_unfollow_post(){
		
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		$this->load->helper('validate');
		if(!isset($request["follower_id"]) || IsNullOrEmptyString($request["follower_id"]) ||
			!isset($request["following_id"]) || IsNullOrEmptyString($request["following_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$data = $this->UserModel->reqUserUnfollow($request);
		if($data){
			$response["response_code"] = "200";
			$response["response_msg"] = "unfollow successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "unfollow failed!";
			$this->response($response ,200);
		}
	}
	
}

?>