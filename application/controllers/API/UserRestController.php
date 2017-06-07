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
			$response["response_code"] = "200";
			$response["response_msg"] = "login is successful.";
			$response["response_data"] = $data;
			$this->response($response ,200);

		}else{
			//====change cover profile and profile name from facebook;
        		$path = $_SERVER['DOCUMENT_ROOT'].'/user_profile/';
        		$f_profile_output = $request["fbid"].'.jpg';
        		$f_profile= 'http://graph.facebook.com/'.$request["fbid"].'/picture?type=large';
        		file_put_contents($path.$f_profile_output, file_get_contents($f_profile));
			$data1 = $this->UserModel->registerFBUser($request);
			
			//$token = $this->UserModel->insertDeviceToken($request); 
			
			if(count($data1)>=1){
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
			
			$this->load->model("PostModel");
			//insert into notification tb
			$request["object_id"]=$request["profile_id"];
			$request["action_id"]=3; //1 = like, 2 = comment, 3 = follow
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
		!isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$report = $this->UserModel->reportPost($request);
		if($report){
			$response["response_code"] = "200";
			$response["response_msg"] = "report successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "report failed!";
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
		!isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])){
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
		!isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$report = $this->UserModel->unsavedPost($request);
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
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');	
		$request["user_id"] = $this->input->get('user_id');	
		
		$responsequery = $this->UserModel->getUserNotification($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		
		$response_data = $responsequery["response_data"];
				
		$response["response_data"] = $response_data;
		$this->response($response, 200);
	}
	
	
	
	
	
	
}

?>