<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class ShopImageRestController extends REST_Controller{

	public function __construct() {
	
		parent::__construct();
	
		$this->load->model('ShopImageModel');
	
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	public function getshopimage_get( $shop_image_id ){
		
		header('Access-Control-Allow-Origin:*');
		$response_data = $this->ShopImageModel->getShopDetailImg($shop_image_id);
		
		$response_data->shop_related_img = $this->ShopImageModel->getShopDetailImgByShopid($response_data->shop_id, 6);
		
		$response["response_code"] = "200";
		$response["response_data"] = $response_data;
		
		$this->response($response);
	}
}

?>