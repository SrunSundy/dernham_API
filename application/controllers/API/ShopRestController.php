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
	
	public function listshop_post(){
		/* {
			"request_data" : {
				"row" : 10,
				"page": 1,
				"serve_category_id" : 0,
				"is_nearby" : false,
				"nearby_value": 1000000,
				"is_popular" : false,
				"country_id" : 0,
				"city_id" : 0,
				"district_id" : 0,
				"commune_id" : 0,
				"current_lat" : 11.565723328439192,
				"current_lng" : 104.88913536071777
			}
		} */
		
		header('Access-Control-Allow-Origin:*');
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["response_msg"] = "bad request";
			$this->response($response);
			return;
		}
		$request = $request["request_data"];
		
		if(!isset($request["row"])) $request["row"] = 10;
		if(!isset($request["page"])) $request["page"] = 1;
		if(!isset($request["current_lat"])) $request["current_lat"] = 0;
		if(!isset($request["current_lng"])) $request["current_lng"] = 0;
		
		$responsequery = $this->ShopModel->listShop($request);
		
		$responsedata = $responsequery["response_data"];
		
		$this->load->helper('timecalculator');
		foreach($responsedata as $item){
				
			$now = new DateTime($item->shop_time_zone);
			$now = strtotime($now->format('H:i:s'));
			$is_open = 0;
			$time_to_close = 0;
			$time_to_open = 0;
		
			if(strtotime($item->shop_opening_time) < $now && strtotime($item->shop_close_time) > $now){
				$is_open = 1;
				$time_to_close = substractCurrentTime($item->shop_time_zone, $item->shop_close_time);
				$time_to_close = covertToMilisecond($time_to_close);
			}
				
			if(strtotime($item->shop_opening_time) > $now){
				$time_to_open = substractCurrentTime($item->shop_time_zone, $item->shop_opening_time);
				$time_to_open = covertToMilisecond($time_to_open);
			}
			if(strtotime($item->shop_close_time) < $now){
				$subfulltime = substractCurrentTime($item->shop_time_zone, "24:00:00");
				$subzerotime = substractTime($item->shop_opening_time, "00:00:00");
				$time_to_open = addTime($subfulltime , $subzerotime); // already return as milisecond
			}
		
			$item->is_shop_open = $is_open;
			$item->time_to_close = $time_to_close;
			$item->time_to_open = $time_to_open;
				
			$item->shop_img = [];
			if($item->shop_has_detail_img != null && $item->shop_has_detail_img !="" && $item->shop_has_detail_img > 0){
				$this->load->model('ShopImageModel');
				$item->shop_img = $this->ShopImageModel->getShopDetailImgByShopid(3, $item->shop_id, 6);
			}
				
		}
		$response["response_code"] = "200";
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_data"] = $responsedata;
		
		$this->response($response);
	}
	
	public function getshop_post(){
		
		/* {
			"request_data" : {
			"shop_id" : 1,
			"current_lat" : 11.565723328439192,
			"current_lng" : 104.88913536071777
			}
		} */
		
		$response = array();
		header('Access-Control-Allow-Origin:*');
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["response_msg"] = "bad request";
			$this->response($response);
			return;
		}
		
		$request = $request["request_data"];

		$this->load->helper('validate');
		$this->load->helper('timecalculator');
		
		if(!isset($request["shop_id"]) || !validateNumeric($request["shop_id"])){
			$response["response_code"] = "400";
			$response["response_msg"] = "invalid shop_id";
			$this->response($response);
		}
		if(!isset($request["current_lat"])) $request["current_lat"] = 0;
		if(!isset($request["current_lng"])) $request["current_lng"] = 0;
		
		$item = $this->ShopModel->getShop($request);
		
		$shop_id = (int)$request["shop_id"];
		
		$now = new DateTime($item->shop_time_zone);
		$now = strtotime($now->format('H:i:s'));
		$is_open = 0;
		$time_to_close = 0;
		$time_to_open = 0;
			
		if(strtotime($item->shop_opening_time) < $now && strtotime($item->shop_close_time) > $now){
			$is_open = 1;
			$time_to_close = substractCurrentTime($item->shop_time_zone, $item->shop_close_time);
			$time_to_close = covertToMilisecond($time_to_close);
		}
		
		if(strtotime($item->shop_opening_time) > $now){
			$time_to_open = substractCurrentTime($item->shop_time_zone, $item->shop_opening_time);
			$time_to_open = covertToMilisecond($time_to_open);
		}
		
		if(strtotime($item->shop_close_time) < $now){
			$subfulltime = substractCurrentTime($item->shop_time_zone, "24:00:00");
			$subzerotime = substractTime($item->shop_opening_time, "00:00:00");
			$time_to_open = addTime($subfulltime , $subzerotime); // already return as milisecond
		}
		$this->load->model('ProductModel');
		
		$item->product_average_price = $this->ProductModel->getProAveragePriceByShopid($shop_id)->average_price;
		$item->is_shop_open = $is_open;
		$item->time_to_close = $time_to_close;
		$item->time_to_open = $time_to_open;
		
		$this->load->model('ServeCategoryModel');
		$item->serve_category = $this->ServeCategoryModel->getServeCategoryByShopid($shop_id);
		
		$this->load->model('FacilityModel');
		$item->shop_facility = $this->FacilityModel->getFacilityByShopid($shop_id);
		
		$this->load->model('ShopImageModel');
		$item->shop_related_img = $this->ShopImageModel->getShopDetailImgByShopid(3 ,$shop_id, 6);
			
		$item->shop_popular_product = $this->ProductModel->getPopularProByShopid($shop_id, 6);
		
		$item->shop_branch = [];
		if($item->branch_id != null && $item->branch_id != "" && $item->branch_id > 0 ){
			$branch_request["shop_id"] = $shop_id;
			$branch_request["branch_id"] = $item->branch_id;
			$branch_request["limit"] = 6;
			$item->shop_branch = $this->ShopModel->getShopRelatedBranch($branch_request);
		}
		
		$response_data = $item;
		
		$response["response_code"] = "200";
		$response["response_data"] = $response_data;
		$this->response($response);
	}	
	
}
?>