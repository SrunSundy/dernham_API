<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class CommentRestController extends REST_Controller{

	public function __construct() {
	
		parent::__construct();
	
		if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$this->load->model('CommentModel');
	
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	function listcommentbypostid_get(){
		//row=20&
		//page=2
		//post_id= 1
		//user_timezone = Asia/Phnom_Penh
		
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		$request["post_id"] = $this->input->get('post_id');
		$request["user_timezone"] = $this->input->get('user_timezone');
		
		if(!isset($request["post_id"])){
			$response["response_code"] = "400";
			$response["error"] = "post_id is invalid!";
			$this->response($response, 400);
			die();
		}
		
		$responsequery = $this->CommentModel->listCommentByPostId($request);
		
		$responsedata = $responsequery["response_data"];
		if($responsedata){
		    if(count($responsedata) > 0){
		        $this->load->helper('timecalculator');
		        foreach($responsedata as $item){
		            $item->created_date = tz($item->created_date, $request["user_timezone"]);
		        }
		    }
		    
		   
		}
		
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_data"] = $responsedata;
		$this->response($response, 200);
	
		
	}
}

?>