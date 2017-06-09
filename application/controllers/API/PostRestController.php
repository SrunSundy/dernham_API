<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

require APPPATH . '/libraries/REST_Controller.php';

// push notification

//==========end notification=========

class PostRestController extends REST_Controller{
	
	function __construct()
	{
		parent::__construct();		
		$this->load->model("PostModel");
	}
	
	public function index(){
		$this->load->view('index');
	}	
	
	function insertuserpost_post(){
		
		/* {
		 "request_data" : {
		 	"post_image" : [{
		 		image_name : "abc.jpg"
		 	},
		 	{
		 		image_name : "bct.jpg"
		 	}],
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
		
		$this->db->trans_begin();
		$request = $request["request_data"];
		$inserted_post_id = $this->PostModel->insertUserPost($request);
		
		if($inserted_post_id != 0){			
			
			$this->load->model("PostImageModel");
			$request["post_id"] = $inserted_post_id;
			$status = $this->PostImageModel->insertUserPostImage( $request);
			if ($this->db->trans_status() === FALSE)
			{
				$this->db->trans_rollback();
				$response["response_code"] = "000";
				$response["response_msg"] = "Transaction rollback!";
				$this->response($response ,200);
			}
			else
			{
				$this->db->trans_commit();
				for($i=0; $i< count($request["post_image"]); $i++){
					copy('./uploadimages/temp/post/big/'.$request["post_image"][$i]["image_name"], $_SERVER['DOCUMENT_ROOT'].'/user_postimage/'.$request["post_image"][$i]["image_name"]);
					
				}
				$response["response_code"] = "200";
				$response["response_msg"] = "Post successfully";
				$this->response($response ,200);
			}
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "Post Fails!";
			$this->response($response ,200);
		} 
	}
	
	function updateuserpost_post(){
		/* {
		 "request_data" : {
		 	"add_post_image" : [{
		 		"image_name" : "tjlksd.jpg"
		 	},{
		 		"image_name" : "jslfjsjf.jpg"	
		 	}],
		 	"remove_post_image" : [{
		 		"post_image_id" : 1
		 	}, {
		 		"post_image_id" : 2
		 	}],
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
		/* 
		{
			"request_data" : {
			"user_id" : "1",
			"post_id" : "56"
			}
		}  */
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
			
			//insert into notification tb
			$request["object_id"]=$request["post_id"];
			$request["action_id"]=1; //1 = like, 2 = comment, 3 = follow
			$notify = $this->PostModel->notifyUser($request);
	
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
	
	function listuserpost_get(){
		
		//row=20&
		//page=2&
		//user_id
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');	
		
		$responsequery = $this->PostModel->listUserPost($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		
		$response_data = $responsequery["response_data"];
		
		if(count($response_data) > 0){
			foreach($response_data as $item){					
				$request_com["post_id"] = $item->post_id;
				$this->load->model("CommentModel");
				$item->comment_count = $this->CommentModel->countCommentByPostid($request_com)->count;
				
				$request_pimg["post_id"] = $item->post_id;
				$request_pimg["row"] = 9999999999;
				$request_pimg["page"] = 1;
				$this->load->model("PostImageModel");
				$item->post_img = $this->PostImageModel->listUserPostImageByPostid($request_pimg)["response_data"];
				
				$request_dcom["post_id"] = $item->post_id;
				$request_dcom["row"] = 1;
				$request_dcom["page"] = 1;
				$request_dcom["order_type"]= 1;
				$item->comment_item = $this->CommentModel->listCommentByPostId($request_dcom)["response_data"];
				$item->like_count = $this->PostModel->countLike($request_com)->count;
				
				$request_islike["post_id"] = $item->post_id;
				$request_islike["user_id"] = $this->input->get("user_id");
				$item->is_liked = $this->PostModel->isUserLiked($request_islike)->is_liked;
			}
		}
		
		$response["response_data"] = $response_data;
		$this->response($response, 200);
		
	}
	
	function list_saved_posts_get(){
		
		//row=20&
		//page=2&
		//user_id
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');	
		$request["user_id"] = $this->input->get('user_id');	
		
		$responsequery = $this->PostModel->listSavedPosts($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		
		$response_data = $responsequery["response_data"];
				
		$response["response_data"] = $response_data;
		$this->response($response, 200);
		
	}
	
	function listprofilepost_get(){
		
		//row=20&
		//page=2
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		$request["profile_id"] = $this->input->get('profile_id');
		
		$responsequery = $this->PostModel->listProfilePost($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		
		$response_data = $responsequery["response_data"];
		
		if(count($response_data) > 0){
			foreach($response_data as $item){					
				$request_com["post_id"] = $item->post_id;
				$this->load->model("CommentModel");
				$item->comment_count = $this->CommentModel->countCommentByPostid($request_com)->count;
				
				$request_pimg["post_id"] = $item->post_id;
				$request_pimg["row"] = 9999999999;
				$request_pimg["page"] = 1;
				$this->load->model("PostImageModel");
				$item->post_img = $this->PostImageModel->listUserPostImageByPostid($request_pimg)["response_data"];
				
				$request_dcom["post_id"] = $item->post_id;
				$request_dcom["row"] = 1;
				$request_dcom["page"] = 1;
				$request_dcom["order_type"]= 1;
				$item->comment_item = $this->CommentModel->listCommentByPostId($request_dcom)["response_data"];
				$item->like_count = $this->PostModel->countLike($request_com)->count;
				
				$request_islike["post_id"] = $item->post_id;
				$request_islike["user_id"] = $this->input->get("user_id");
				$item->is_liked = $this->PostModel->isUserLiked($request_islike)->is_liked;
			}
		}
		
		$response["response_data"] = $response_data;
		$this->response($response, 200);
		
	}
	
	function list_profile_postimage_get(){
		
		//row=20&
		//page=2
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		$request["profile_id"] = $this->input->get('profile_id');
		
		$responsequery = $this->PostModel->listProfilePostImages($request);
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		
		$response_data = $responsequery["response_data"];
		
		
		$response["response_data"] = $response_data;
		$this->response($response, 200);
		
	}
	
	function view_comment_post(){
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
		
		$data = $this->PostModel->viewComment($request);
		
		if($data){
			$response["response_data"] = $data;
			$response["response_code"] = "200";
			$response["response_msg"] = "view succeed!";
			$this->response($response ,200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "view failed!";
			$this->response($response ,200);
		}
	}
	
	function user_comment_post(){
		
		/* {
			"request_data" : {
				"user_id" : "1",
				"post_id" : "56",
				"text" : "abc"
			}
		}  */
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
			!isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"]) ||
			!isset($request["text"]) || IsNullOrEmptyString($request["text"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}

		$data = $this->PostModel->userComment($request);
		if($data){
		
			
			//insert into notification tb
			$request["object_id"]=$request["post_id"];
			$request["action_id"]=2; //1 = like, 2 = comment, 3 = follow
			
			$notify = $this->PostModel->notifyUser($request);
			
			$user = $this->PostModel->getUserNotification($request);
			$token = $this->PostModel->getTokenNotification($request);
			if(!empty($token)){
				$this->load->helper('notification');
				push_notification($token,$user);
			}
			$response["response_code"] = "200";
			$response["response_msg"] = "comment successfully!";
			$this->response($response ,200);
			
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "comment failed!";
			$this->response($response ,200);
		}
	}
	
	
	
}

?>