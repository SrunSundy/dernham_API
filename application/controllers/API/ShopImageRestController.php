<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class ShopImageRestController extends REST_Controller{

	public function __construct() {
	
		parent::__construct();
	
		if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$this->load->model('ShopImageModel');
	
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	public function getshopimage_get( $shop_image_id = null ){
		
		$response_data = $this->ShopImageModel->getShopDetailImg($shop_image_id);
		
		if($response_data){
			$this->load->helper('ImageType');
			$response_data->shop_related_img = $this->ShopImageModel->getShopDetailImgByShopid($response_data->shop_id, 6, ImageType::Detail);		
		}

		$response["response_code"] = "200";
		$response["response_data"] = $response_data;

		$this->response($response, 200);
	}
}

?>