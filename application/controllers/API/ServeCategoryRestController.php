<?php
class ServeCategoryRestController extends CI_Controller{
	
	function __construct()
	{
		parent::__construct();
		$this->load->model("ServeCategoryModel");
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	public function listServeCategory(){
		
		$response = $this->ServeCategoryModel->listServeCategory();
		$json = json_encode($response, JSON_PRETTY_PRINT);
		echo $json;
	}
	
	
}

?>