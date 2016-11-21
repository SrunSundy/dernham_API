<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class ShopRestController extends REST_Controller{	
	public function __construct() {
		
		parent::__construct();

		$this->load->model('ShopModel');
		
	}
	public function index(){
		$this->load->view('index');
	}	
	
	public function index_post(){		
		$this->response("Hello This is sundy");
	}
	
	public function index_get(){
		$this->response("Hello This is me");
	}
	
	public function listshop_post(){
		
		header('Access-Control-Allow-Origin:*');
		$request = json_decode($this->input->raw_input_stream,true);
		$request = $request["request_data"];
		
		$response = $this->ShopModel->listShop($request);
		$this->response($response);
	}
}
?>