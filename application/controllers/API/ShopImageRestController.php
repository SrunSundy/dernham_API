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
	
	public function listshopimagebyshopid_post(){
		
		/* {
			"request_data" : {
				"shop_id" : 35,
				"img_type" :3,
				"row" : 10,
				"page": 2
				
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
		
		if(!isset($request["shop_id"])){
			$response["response_code"] = "400";
			$response["error"] = "shop_id is required!";
			$this->response($response, 400);
			die();
		}
		if(!isset($request["img_type"])) {
			$response["response_code"] = "400";
			$response["error"] = "img_type is required!";
			$this->response($response, 400);
			die();
		}
		
		
		//$this->load->helper('imagetype');
		
		$response_data = $this->ShopImageModel->listShopDetailImgByShopid($request);		
		$response_count = $this->ShopImageModel->countListShopDetailImgByShopid($request);
		
	
		$response["response_code"] = "200";
		$response["total_page"] = (int)$response_count->total_page;
		$response["total_record"] = $response_count->total_record;
		$response["response_data"] = $response_data;
		
		
		$this->response($response, 200);
	}
	
	public function getshopimage_get(){
		
	    //sh_img_id
	    //user_timezone
	    
	    $request["user_timezone"] =  $this->input->get('user_timezone');
	    $request["sh_img_id"] =  $this->input->get('sh_img_id');
	    
	    if(!isset($request["sh_img_id"])){
	        $response["response_code"] = "400";
	        $response["error"] = "invalid sh_img_id";
	        $this->response($response, 400);
	        die();
	    }
	    
	    $response_data = $this->ShopImageModel->getShopDetailImg($request["sh_img_id"]);
		
		if($response_data){
		    
		    $this->load->helper('timecalculator');
		    $response_data->sh_img_created_date = tz($response_data->sh_img_created_date, $request["user_timezone"]);
			/*$this->load->helper('imagetype');
			
			$request["shop_id"] = $response_data->shop_id;
			$request["limit"] = 6 ; 
			$request["img_type"] = imagetype::Detail;
			$request["has_defined"] = $shop_image_id;
			$response_data->shop_related_img = $this->ShopImageModel->listShopDetailImgByShopid($request);	*/	
		}

		$response["response_code"] = "200";
		$response["response_data"] = $response_data;

		$this->response($response, 200);
	}
}

?>