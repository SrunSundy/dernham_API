<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class ShopRestController extends REST_Controller{	
	public function __construct() {
		
		parent::__construct();

		if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$this->load->model('ShopModel');
		
		
	}
	public function index(){
		$this->load->view('index');
	}	
	
	public function sundy_get(){
		
		$this->load->helper("imagepath");
		echo imagepath::getIconPath();
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
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$request = $request["request_data"];
		
		if(!isset($request["row"])) $request["row"] = 10;
		if(!isset($request["page"])) $request["page"] = 1;
		if(!isset($request["current_lat"])) $request["current_lat"] = 0;
		if(!isset($request["current_lng"])) $request["current_lng"] = 0;
		
		$responsequery = $this->ShopModel->listShop($request);
		
		$responsedata = $responsequery["response_data"];
		
		$this->load->helper('timecalculator');
	
		
		if(count($responsedata) > 0){
			foreach($responsedata as $item){
			
				if($item->shop_time_zone == null || trim($item->shop_time_zone)== "" ){
					$item->shop_time_zone = "Asia/Phnom_Penh";
				}
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
				
				$this->load->helper('distancecalculator');
				$item->distance = distanceFormat($item->distance);
			
				$item->shop_img = [];
				if($item->shop_has_detail_img != null && $item->shop_has_detail_img !="" && $item->shop_has_detail_img > 0){
					$this->load->model('ShopImageModel');
					$this->load->helper('imagetype');
					$this->load->helper('yesnoimagefrontshow');
					
					$request_img["shop_id"] = $item->shop_id;
					$request_img["limit"] = 6 ; 
					$request_img["img_type"] = imagetype::Detail;
					$request_img["is_front_show"] = yesnoimagefrontshow::YES;
		
					$item->shop_img = $this->ShopImageModel->listShopDetailImgByShopid($request_img);
				}
			
			}
		}
		
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_code"] = "200";
		$response["response_data"] = $responsedata;
		
		$this->response($response, 200);
	}
	
	public function listnearbyshop_post(){
				
		/* {
			"request_data" : {
				"current_lat" : 11.565723328439192,
				"current_lng" :104.88913536071777,
				"nearby_value" : 3			
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
		if(!isset($request["current_lat"])) $request["current_lat"] = 0;
		if(!isset($request["current_lng"])) $request["current_lng"] = 0;
		if(!isset($request["nearby_value"])) $request["nearby_value"] = 0.5;
		
		$responsedata = $this->ShopModel->listNearbyShop($request);
		
		if(count($responsedata) > 0){
			$this->load->model('ServeCategoryModel');
			$this->load->helper('distancecalculator');
			
			foreach($responsedata as $item){
				if($item->shop_time_zone == null || trim($item->shop_time_zone)== "" ){
					$item->shop_time_zone = "Asia/Phnom_Penh";
				}
				$now = new DateTime($item->shop_time_zone);
				$now = strtotime($now->format('H:i:s'));
				
				$is_open = 0;
				
				if(strtotime($item->shop_opening_time) < $now && strtotime($item->shop_close_time) > $now){
					$is_open = 1;
				}
				$item->is_shop_open = $is_open;							
				$item->distance = distanceFormat($item->distance);
								
				$serve_cate_string = $this->ServeCategoryModel->listServeCategoryByShopid($item->shop_id);
				$serve_cate_str = "";
				if(count($serve_cate_str)> 0){
					for($i=0; $i<count($serve_cate_string); $i++){
						$serve_cate_str .= $serve_cate_string[$i]->serve_category_name . " - ";
					}
				}				
				
				$shopopen = new DateTime($item->shop_opening_time);	
				$shopclose = new DateTime($item->shop_close_time);
				
				$item->shop_opening_time = $shopopen->format('h:i a');
				$item->shop_close_time = $shopclose->format('h:i a');
				$item->serve_category = $serve_cate_str;
			}		
		}
				
		$response["response_code"] = "200";
		$response["response_data"] = $responsedata;
		$this->response($response, 200);
	}
	
	public function listPopularShop_get(){
		
		//current_lat=11.565723328439&
		//current_lng=104.889135360&
		//row=20&
		//page=2
		
		$request["current_lat"] = $this->input->get('current_lat');
		$request["current_lng"] = $this->input->get('current_lng');
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		
		$responsequery = $this->ShopModel->listPopularShop($request);
		$response["response_code"] = "200";
		
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_data"] = $responsequery["response_data"];
		$this->response($response, 200);
		
		
	}
	
	public function listsearchshop_post(){
		
		/* {
			"request_data" : {
			"row" : 10,
			"page": 1,
			"serve_category_id" : 0,
			"is_nearby" : false,
			"is_popular" : false,
			"is_latest" : false,
			"is_best_match": true,
			"country_id" : 0,
			"city_id" : 0,
			"district_id" : 0,
			"commune_id" : 0,
			"current_lat" : 11.565723328439192,
			"current_lng" : 104.88913536071777,
			"srch_text": ""
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
		
		if(!isset($request["row"])) $request["row"] = 10;
		if(!isset($request["page"])) $request["page"] = 1;
		if(!isset($request["current_lat"])) $request["current_lat"] = 0;
		if(!isset($request["current_lng"])) $request["current_lng"] = 0;
		if(!isset($request["srch_text"])) $request["srch_text"] = "";
		
		$responsequery = $this->ShopModel->listSearchShop($request);		
		$responsedata = $responsequery["response_data"];
		
		if(count($responsedata) > 0){
			$this->load->model('ServeCategoryModel');
			$this->load->helper('distancecalculator');
				
			foreach($responsedata as $item){
				if($item->shop_time_zone == null || trim($item->shop_time_zone)== "" ){
					$item->shop_time_zone = "Asia/Phnom_Penh";
				}
				$now = new DateTime($item->shop_time_zone);
				$now = strtotime($now->format('H:i:s'));
		
				$is_open = 0;
		
				if(strtotime($item->shop_opening_time) < $now && strtotime($item->shop_close_time) > $now){
					$is_open = 1;
				}
				$item->is_shop_open = $is_open;
				$item->distance = distanceFormat($item->distance);
		
				$item->serve_category = $this->ServeCategoryModel->listServeCategoryByShopid($item->shop_id);
			}
		}
		
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_code"] = "200";
		$response["response_data"] = $responsedata;
		
		$this->response($response, 200);
		
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
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$this->load->helper('validate');
		$this->load->helper('timecalculator');
		$request = $request["request_data"];
		if(!isset($request["shop_id"]) || !validateNumeric($request["shop_id"])){
			$response["response_code"] = "400";
			$response["error"] = "invalid shop_id";
			$this->response($response, 400);
			die();
		}
				
		if(!isset($request["current_lat"])) $request["current_lat"] = 0;
		if(!isset($request["current_lng"])) $request["current_lng"] = 0;
		
		$item = $this->ShopModel->getShop($request);
		
		if($item){
			$shop_id = (int)$request["shop_id"];
			
			if($item->shop_time_zone == null || trim($item->shop_time_zone)== "" ){
				$item->shop_time_zone = "Asia/Phnom_Penh";
			}
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
			$item->product_average_price = number_format((float)$item->product_average_price, 2, '.', '');
			if($item->shop_phone){
				$item->shop_phone = explode("|",$item->shop_phone);
			}
			if($item->shop_working_day){
				$item->shop_working_day= explode("|",$item->shop_working_day);
			}
			
			$this->load->helper('distancecalculator');
			$item->distance = distanceFormat($item->distance);
			
			$this->load->model('ServeCategoryModel');
			$item->serve_category = $this->ServeCategoryModel->listServeCategoryByShopid($shop_id);
			
			$this->load->model('FacilityModel');
			$item->shop_facility = $this->FacilityModel->listFacilityByShopid($shop_id);
			
			$this->load->model('ShopImageModel');
			$this->load->helper('imagetype');
			
			$request_img["shop_id"] = $shop_id;
			$request_img["limit"] = 6 ;
			$request_img["img_type"] = imagetype::Detail;
			$item->shop_related_img = $this->ShopImageModel->listShopDetailImgByShopid($request_img);
				
			$item->shop_popular_product = $this->ProductModel->listPopularProByShopid($shop_id, 6);
			
			$item->shop_branch = [];
			if($item->branch_id != null && $item->branch_id != "" && $item->branch_id > 0 ){
				$branch_request["shop_id"] = $shop_id;
				$branch_request["branch_id"] = $item->branch_id;
				$branch_request["limit"] = 6;
				$item->shop_branch = $this->ShopModel->listShopRelatedBranch($branch_request);
			}
		}
		
		
		$response_data = $item;
		
		$response["response_code"] = "200";
		$response["response_data"] = $response_data;
		$this->response($response, 200);
	}	
	
}
?>