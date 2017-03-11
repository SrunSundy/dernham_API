<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class PostRestController extends REST_Controller{
	
	function __construct()
	{
		parent::__construct();		
		$this->load->model("PostModel");
	}
	
	public function index(){
		$this->load->view('index');
	}	
	
	function userpost_post(){
		
		/* {
		 "request_data" : {
			"caption" : "leap",
			"shop_id" : "20",
			"user_id" : "39"
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
		$check = $this->PostModel->userPost($request);
		
		if($check){			
			$response["response_code"] = "200";		
			$response["response_msg"] = "Post successfully";	
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "Post Fails!";
			$this->response($response ,200);
		}
		
	}
	
	function updateuserpost_post(){
		/* {
		 "request_data" : {
			"caption" : "leapsdfsd",
			"shop_id" : "20",
			"post_id" : "1"
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
		$check = $this->PostModel->updateUserPost($request);
		
		if($check){
			$response["response_code"] = "200";
			$response["response_msg"] = "update post successfully";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "update post Fails!";
			$this->response($response ,200);
		}
		
	}
	
	function user_like_post(){
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

		$data = $this->PostModel->userLike($request);
		if($data){
			$data1 = $this->PostModel->countLike($request);
	
			$response["response_data"] = $data1;
			$response["response_code"] = "200";
			$response["response_msg"] = "like succeed!";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "like failed!";
			$this->response($response ,200);
		}
	}
	
	function user_unlike_post(){
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
		
		$data = $this->PostModel->userUnlike($request);
		if($data){
			$data1 = $this->PostModel->countLike($request);
			
			$response["response_data"] = $data1;
			$response["response_code"] = "200";
			$response["response_msg"] = "unlike succeed!";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "unlike failed!";
			$this->response($response ,200);
		}
	}
	
	function view_likers_post(){
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		$this->load->helper('validate');
		if(!isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$data = $this->PostModel->viewLikers($request);
		
		//$this->load->model("UserModel");
		//$data1 = $this->UserModel->getUserProfileFollow($request);
		if($data){
			$response["response_data"] = $data;
			//$response["is_followed"] = $data1;
			$response["response_code"] = "200";
			$response["response_msg"] = "view succeed!";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "view failed!";
			$this->response($response ,200);
		}
	}
	
}

?>