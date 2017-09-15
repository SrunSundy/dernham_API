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
		$is_exist = count($checkuser);
		if($is_exist >= 1){
			
			$msg = "user is already existed. ";
			
			$this->load->helper('userstatus');
			switch((int)$checkuser->user_status){			
				case userstatus::Disabled : {
					$response["response_code"] = "000";
					$response["response_msg"] = $msg."user is disabled! contact adminstrator for the issue. ";
					$this->response($response, 200);
					break;
				}
				case userstatus::Active :{
					$response["response_code"] = "000";
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
						
				/*$data = $this->UserModel->loginUser($request);
				if(count($data)>0){
					$request["user_id"] = $data->user_id;
					$token = $this->UserModel->insertDeviceToken($request); 
				}*/
				
				if($sendemail){
					$response["response_code"] = "200";
					$response["response_msg"] = "Registration is successful!";
					$this->response($response ,200);
				}else{
					$response["response_code"] = "000";
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
	
	function resend_verification_code_post(){
		/* {
			 "request_data" : {
			 	"email" : "leap@gmail.com"
			 }
		 } */
		
		/*
		 *  000 response_code :: email doesn't match any record in db
		 *  001 response code :: fail to send the email to user*/
		
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		$this->load->helper('validate');
		if(!isset($request["email"]) || IsNullOrEmptyString($request["email"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$checkuser = $this->UserModel->checkIfUserexist($request);
		$is_exist = count($checkuser);
		if($is_exist >= 1){
			
			$this->load->helper('dernhamutils');			
			$verification_code = generateVerificationCode();			
						
			$u_request["user_id"] = $checkuser->user_id;
			$u_request["verification_code"] = $verification_code;
			$is_updated = $this->UserModel->updateUserVerificationCode($u_request);
			
			if($is_updated){				
				$this->load->model("EmailModel");
				$request["verification_code"] = $verification_code;
				$sendemail = $this->EmailModel->sendEmail($request);
				
				if($sendemail){
					$response["response_code"] = "200";
					$response["response_data"] = $is_updated;
					$response["response_msg"] = "Resend successfully!";
					$this->response($response ,200);
				}else{
					$response["response_code"] = "001";
					$response["response_data"] = false;
					$response["response_msg"] = "Fail to send email!";
					$this->response($response ,200);
				}
			}else{
				$response["response_code"] = "200";
				$response["response_data"] = $is_updated;
				$response["response_msg"] = "!";
				$this->response($response ,200);
			}
		
		}else{
			$response["response_code"] = "000";
			$response["response_data"] = false;
			$response["response_msg"] = "your email doesn't match in the system!";
			$this->response($response, 200);
		}
	}
	
	function verifyuser_post(){
		
		/* {
		 "request_data" : {
			 "email" : "leap@gmail.com",
			 "v_code" : "123"
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
		if(!isset($request["email"]) || IsNullOrEmptyString($request["email"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request. email is required!";
			$this->response($response, 400);
			die();
		}
		
		if(!isset($request["v_code"]) || IsNullOrEmptyString($request["v_code"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request. v_code is required!";
			$this->response($response, 400);
			die();
		}
		
		$data = $this->UserModel->getUserToVerify($request);
		$has_user = count($data);
		
		if($has_user >= 1){
			
			$user_status = $data[0]->user_status;
			$this->load->helper('userstatus');
			
			switch((int)$user_status){
				case userstatus::Disabled : {
					$response["response_code"] = "000";
					$response["response_msg"] = "verification fails. user is disabled!";
					$response["response_data"] = false;
					$this->response($response, 200);
					break;
				}
				case userstatus::Active :{
					$response["response_code"] = "200";
					$response["response_msg"] = "user is already active!";
					$response["response_data"] = false;
					$this->response($response, 200);
					break;
				}
				case userstatus::Unauthorized :{
					
					$data = $this->UserModel->verifyUser($request);
					
					if($data){
						$response["response_code"] = "200";
						$response["response_msg"] = "verify successfully.";
						$response["response_data"] = true;
						$this->response($response, 200);
					}
					
					break;
				}
			}
			
		}else{
			$response["response_code"] ="200";
			$response["response_msg"] = "verification fails. your verification code might be incorrect or expired ";
			$response["response_data"] = false;
			$this->response($response, 200);
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
		
		 /* //==============leap fix===========
			$response["response_code"] = "200";
					$response["response_data"] = $data->user_status;
					$this->response($response, 200);
					break;
		//============end fix================  */
			$this->load->helper('userstatus');
			switch((int) $data->user_status){
				case userstatus::Disabled : {
					$response["response_code"] = "000";
					$response["response_msg"] = "user is disabled!";
					$this->response($response, 200);
					break;
				}
				
				case userstatus::Active :{
					//=========record user log============	
					$request["user_id"] = $data->user_id;	
					$request["action_type"] = "login";	
					$datalog = $this->UserModel->insertUserLog($request);
					
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
		if(!isset($request["fbid"]) || IsNullOrEmptyString($request["fbid"]) ||
			!isset($request["fullname"]) || IsNullOrEmptyString($request["fullname"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$request["user_id"] = $request["fbid"];
		
		$data = $this->UserModel->checkIfFBUserExist($request);
		
		if(count($data)>=1){
			//=========record user log============	
					$request["user_id"] = $data->user_id;	
					$request["action_type"] = "FBlogin";	
					$datalog = $this->UserModel->insertUserLog($request);
					
			$response["response_code"] = "200";
			$response["response_msg"] = "login is successful.";
			$response["response_data"] = $data;
			$this->response($response ,200);

		}else{
			//====change cover profile and profile name from facebook;
        		$path = $_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/real/user/medium/';
        		$f_profile_output = $request["fbid"].'.jpg';
        		$f_profile= 'http://graph.facebook.com/'.$request["fbid"].'/picture?type=large';
        		file_put_contents($path.$f_profile_output, file_get_contents($f_profile));
        		
        		$path = $_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/real/user/small/';
        		file_put_contents($path.$f_profile_output, file_get_contents($f_profile));
        		
        		//===insert FB user infor
			$data1 = $this->UserModel->registerFBUser($request);
			
			if(count($data1)>=1){
			
				//=========record user log============	
				$request["user_id"] = $data1->user_id;	
				$request["action_type"] = "FBlogin";	
				$datalog = $this->UserModel->insertUserLog($request);
							
				$response["response_code"] = "200";
				$response["response_data"] = $data1;
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
		
		//$request["profile_id"] =  $request["id"];
		
		
		if(!isset($request["profile_id"]) || IsNullOrEmptyString($request["profile_id"])){
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
	
	
	
	function updateuserphoto_post(){
		
		/*{
			"data" :{
				"user_id" : 38
			}
		}*/
		
		$response = array();
		
		$this->load->helper('dernhamutils');
		$new_name = generateRandomString(10);
		
		if (empty($_FILES) || empty($_POST["data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$update_data = json_decode($_POST["data"]);
		
		$this->load->model("UploadModel");
		$request_upload["image_file"] = $_FILES;
		$request_upload["new_name"] = $new_name;
		
		$send = $this->UploadModel->uploadUserPhoto($request_upload);
		
		if($send["is_upload"]){
			$request_data["update_param"] = "user_photo";
			$request_data["update_value"] = $send["filename"];
			$request_data["user_id"] = $update_data->user_id;
			$data = $this->UserModel->updateUserProfileData($request_data);		
			if($data){
				$response["response_code"] = "200";
				$response["response_data"] = $send["filename"];
				$this->response($response,200);
			}else{
				$response["response_code"] = "000";
				$response["response_msg"] = "error update data";
				$this->response($response,200);
			}
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = $send["message"];
			$this->response($response,200);
			
		}	
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
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
			!isset($request["profile_id"]) || IsNullOrEmptyString($request["profile_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$data = $this->UserModel->getUserProfile($request);
		$followed = $this->UserModel->getUserProfileIsFollowed($request);
		$follower = $this->UserModel->getNumberFollower($request);
		$following = $this->UserModel->getNumberFollowing($request);
		$post = $this->UserModel->getNumberPost($request);
		if(count($data) == 1){
			$response["response_code"] = "200";
			$response["response_msg"] = "request successfully";
			$response["response_data"] = $data;
			$response["is_followed"] = $followed;
			$response["followers"] = $follower;
			$response["followings"] = $following;
			$response["posts"] = $post;
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "request failed!";
			$this->response($response ,200);
		}
	}
	
	function user_follow_post(){
		
		/* {
			 "request_data" : {
		 		"user_id" : "1",
		 		"profile_id" : "8"
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
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
			!isset($request["profile_id"]) || IsNullOrEmptyString($request["profile_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
	
	
		$data = $this->UserModel->reqUserFollow($request);
		if($data){
			
			//======insert into notification tb=========
			$this->load->model("PostModel");
			$request["actioner_id"]=$request["user_id"];
			$request["user_id"]=$request["profile_id"];
			$request["object_id"]="";
			$request["action_id"]=3; //1 = like, 2 = comment, 3 = follow, 4 = post
			$notify = $this->PostModel->notifyUser($request);
		
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
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
			!isset($request["profile_id"]) || IsNullOrEmptyString($request["profile_id"])){
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
	
	function get_profile_subinfo_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$request = $request["request_data"];
		$this->load->helper('validate');
		if(!isset($request["profile_id"]) || IsNullOrEmptyString($request["profile_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$follower = $this->UserModel->getNumberFollower($request);
		$following = $this->UserModel->getNumberFollowing($request);
		$post = $this->UserModel->getNumberPost($request);
		
		$response["response_code"] = "200";
			$response["response_msg"] = "request successfully";
			$response["followers"] = $follower;
			$response["followings"] = $following;
			$response["posts"] = $post;
			$this->response($response ,200);
	}
	
	function user_report_shop_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$request = $request["request_data"];
		$this->load->helper('validate');
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
		!isset($request["reported_type"]) || IsNullOrEmptyString($request["reported_type"]) ||
		!isset($request["object_id"]) || IsNullOrEmptyString($request["object_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$report = $this->UserModel->reportShop($request);
		if($report){
			$response["response_code"] = "200";
			$response["response_msg"] = "reported successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "report failed!";
			$this->response($response ,200);
		}
	}
	
	function user_report_post_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$request = $request["request_data"];
		$this->load->helper('validate');
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
		!isset($request["object_id"]) || IsNullOrEmptyString($request["object_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$report = $this->UserModel->reportPost($request);
		if($report){
			$response["response_code"] = "200";
			$response["response_msg"] = "reported successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "report failed!";
			$this->response($response ,200);
		}
	}
	
	function user_unreport_post_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$request = $request["request_data"];
		$this->load->helper('validate');
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
		!isset($request["object_id"]) || IsNullOrEmptyString($request["object_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$report = $this->UserModel->unreportPost($request);
		if($report){
			$response["response_code"] = "200";
			$response["response_msg"] = "unreported successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "unreport failed!";
			$this->response($response ,200);
		}
	}
	
	function user_save_post_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
					
		$request = $request["request_data"];
		$this->load->helper('validate');
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
		!isset($request["saved_type"]) || IsNullOrEmptyString($request["saved_type"]) ||
		!isset($request["object_id"]) || IsNullOrEmptyString($request["object_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}  
		
		$report = $this->UserModel->savePost($request);
		if($report){
			$response["response_code"] = "200";
			$response["response_msg"] = "saved successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "save failed!";
			$this->response($response ,200);
		}
	}
	
	function user_unsaved_post_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$request = $request["request_data"];
		$this->load->helper('validate');
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
		!isset($request["saved_type"]) || IsNullOrEmptyString($request["saved_type"]) ||
		!isset($request["object_id"]) || IsNullOrEmptyString($request["object_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$report = $this->UserModel->unsavedPost($request);
		if($report){
			$response["response_code"] = "200";
			$response["response_msg"] = "unsaved successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "unsave failed!";
			$this->response($response ,200);
		}
	}
	
	function user_delete_post_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$request = $request["request_data"];
		$this->load->helper('validate');
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
		!isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$report = $this->UserModel->deletePost($request);
		if($report){
			$response["response_code"] = "200";
			$response["response_msg"] = "deleted successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "delete failed!";
			$this->response($response ,200);
		}
	}
	
	
	function list_top_members_get(){
		
		//row=20&
		//page=2&
		//user_id
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');	
		
		$responsequery = $this->UserModel->listTopMembers($request);
		
		$response["response_code"] = "200";
			$response["total_record"] = $responsequery["total_record"];
			$response["total_page"] = $responsequery["total_page"];
			
			$response_data = $responsequery["response_data"];
			$response["response_data"] = $response_data;
			$this->response($response, 200);
		/*
		if($responsequery){
			$response["response_code"] = "200";
			$response["total_record"] = $responsequery["total_record"];
			$response["total_page"] = $responsequery["total_page"];
			
			$response_data = $responsequery["response_data"];
			$response["response_data"] = $response_data;
			$this->response($response, 200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "update failed!";
			$this->response($response ,200);
		}
		*/
	}
	
	function update_user_photo_name_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$request = $request["request_data"];
		$this->load->helper('validate');
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
		!isset($request["user_photo"]) || IsNullOrEmptyString($request["user_photo"]) ||
		!isset($request["temp_photo"]) || IsNullOrEmptyString($request["temp_photo"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$res = $this->UserModel->updateUserPhotoName($request);
		if($res){
			
			$response["response_code"] = "200";
			$response["response_msg"] = "updated successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "update failed!";
			$this->response($response ,200);
		}
	}
	
	
	function update_user_password_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		$this->load->helper('validate');
		
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"])||
		!isset($request["old_pwd"]) || IsNullOrEmptyString($request["old_pwd"]) ||
		!isset($request["new_pwd"]) || IsNullOrEmptyString($request["new_pwd"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}		
		
		$data = $this->UserModel->updateUserPassword($request);
		if($data){
				$response["response_code"] = "200";
				$response["response_msg"] = "updated successfully";
				$this->response($response ,200);
		}else{
				$response["response_code"] = "000";
				$response["response_msg"] = "update failed";
				$this->response($response ,200);
		}
	}
	
	function get_user_notification_get(){
		//row=20&
		//page=2&
		//user_id
		//user_timezone
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');	
		$request["user_id"] = $this->input->get('user_id');	
		$request["user_timezone"] = $this->input->get('user_timezone');
		
		$responsequery = $this->UserModel->getUserNotification($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		
		$response_data = $responsequery["response_data"];
		if(count($response_data) > 0){
		    $this->load->helper('timecalculator');
		    foreach($response_data as $item){
		        $item->created_date = tz($item->created_date, $request["user_timezone"]);
		    }
		}
				
		$response["response_data"] = $response_data;
		$this->response($response, 200);
	}

	
	function get_followers_get(){
		//row=20&
		//page=2&
		//user_id
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');	
		$request["user_id"] = $this->input->get('user_id');	
		
		$responsequery = $this->UserModel->getFollowers($request);
		$response["response_data"] = $responsequery["response_data"];
		
		if($responsequery){
		
			
			//$response["total_record"] = $responsequery["total_record"];
			//$response["total_page"] = $responsequery["total_page"];
				$response["response_code"] = "200";
				$response["response_msg"] = "selected successfully";
				$this->response($response ,200);
		}else{
				$response["response_code"] = "000";
				$response["response_msg"] = "select failed";
				$this->response($response ,200);
		}
	}
	
	function get_following_get(){
		//row=20&
		//page=2&
		//user_id
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');	
		$request["user_id"] = $this->input->get('user_id');	
		
		$responsequery = $this->UserModel->getFollowing($request);
		$response["response_data"] = $responsequery["response_data"];
		
		if($responsequery){
		
			
			//$response["total_record"] = $responsequery["total_record"];
			//$response["total_page"] = $responsequery["total_page"];
				$response["response_code"] = "200";
				$response["response_msg"] = "selected successfully";
				$this->response($response ,200);
		}else{
				$response["response_code"] = "000";
				$response["response_msg"] = "select failed";
				$this->response($response ,200);
		}
	}
	
	function user_forget_password_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		$this->load->helper('validate');
		
		if(!isset($request["email"]) || IsNullOrEmptyString($request["email"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$res = $this->UserModel->userForgetPassword($request);
		if(count($res)>=1){
			$this->load->model("EmailModel");
			$req["user_email"] = $res->user_email;
			$req["user_password"] = $res->user_password;
			$sendemail = $this->EmailModel->sendEmailForgetPassword($req);	
		
			$response["response_code"] = "200";
			$response["response_msg"] = "sent successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "send failed!";
			$this->response($response ,200);
		}
	}
	
	
	
	function user_feedback_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		$this->load->helper('validate');
		
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"])||
			!isset($request["description"]) || IsNullOrEmptyString($request["description"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request["image_src"] = (!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"])) ? "" : $request["image_src"];
		
		$res = $this->UserModel->userFeedback($request);
		if($res){
		
			if($request["image_src"]!=""){
				copy($_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/temp/user_feedback/medium/'.$request["image_src"], $_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/real/user_feedback/medium/'.$request["image_src"]);
			
			}
		
			$response["response_code"] = "200";
			$response["response_msg"] = "feedbacked successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "feedback failed!";
			$this->response($response ,200);
		}
	}
	
	
	function update_read_notification_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		$this->load->helper('validate');
		
		if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"])||
		!isset($request["already_read"]) || IsNullOrEmptyString($request["already_read"]) ||
		!isset($request["last_notify_id"]) || IsNullOrEmptyString($request["last_notify_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}		
		
		$data = $this->UserModel->updateReadNotification($request);
		if($data){
				$response["response_code"] = "200";
				$response["response_msg"] = "updated successfully";
				$this->response($response ,200);
		}else{
				$response["response_code"] = "000";
				$response["response_msg"] = "update failed";
				$this->response($response ,200);
		}
	}
	
	
	
}

?>