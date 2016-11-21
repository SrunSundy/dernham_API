<?php 
class ShopRestController extends CI_Controller{	
	public function __construct() {
		
		parent::__construct();

		$this->load->model('ShopModel');
		
		
	}
	public function index(){
		$this->load->view('index');
	}	
	
	public function listShop(){
		header('Access-Control-Allow-Origin:*');
		$request = json_decode($this->input->raw_input_stream,true);
		$request = $request["request_data"];
		
		$response = $this->ShopModel->listShop($request);
		$json = json_encode($response, JSON_PRETTY_PRINT); 
		echo $json;
	}
}
?>