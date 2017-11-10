<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class ProductRestController extends REST_Controller{
	
	function __construct()
	{
		parent::__construct();
		if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		$this->load->model("ProductModel");
	}
	
	public function index(){
		$this->load->view('index');
	}
	
	public function listproduct_post(){
		
		/* {
		 "request_data" : {
			"row" : 10,
			"page": 1,
			"serve_category_id" : 0,
			"is_nearby" : false,
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
		
		$responsequery = $this->ProductModel->listProduct($request);
		
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_code"] = "200";
		$response["response_data"] = $responsequery["response_data"];
		
		$this->response($response, 200);
	}
	
	public function listproductbyshopid_get(){
		
		//shop_id = 12&		
		//is_popular : false&
		//row=20&
		//page=1
	
		$request["shop_id"] = $this->input->get('shop_id');
		$request["is_popular"] = $this->input->get('is_popular');
		$request["row"] = $this->input->get('row');
		$request["page"] = $this->input->get('page');
		
		$this->load->helper('validate');
		if(!isset($request["shop_id"]) || IsNullOrEmptyString($request["shop_id"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		$is_popular = false;
		if(isset($request["is_popular"])){
			$is_popular = $request["is_popular"];
		}
		$request["is_popular"] = $is_popular;
		
		$responsequery = $this->ProductModel->listProductByShopid($request);
		$response["response_code"] = "200";
		
		$response["total_record"] = $responsequery["total_record"];
		$response["total_page"] = $responsequery["total_page"];
		$response["response_data"] = $responsequery["response_data"];
		$this->response($response, 200);
	}
	
	public function listsearchproduct_post(){
	    
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
	    
	    $responsequery = $this->ProductModel->listSearchProduct($request);
	    $responsedata = $responsequery["response_data"];
	    
	    if(count($responsedata) > 0){
	      
	        $this->load->helper('distancecalculator');        
	        foreach($responsedata as $item){
	          
	            $item->distance = distanceFormat($item->distance);
	            //	$item->shop_display_time =  date('h:i A', strtotime($item->shop_opening_time)).' - '.date('h:i A', strtotime($item->shop_close_time));
	            
	            //$item->serve_category = $this->ServeCategoryModel->listServeCategoryByShopid($item->shop_id);
	        }
	    }
	    
	    $response["total_record"] = $responsequery["total_record"];
	    $response["total_page"] = $responsequery["total_page"];
	    $response["response_code"] = "200";
	    $response["response_data"] = $responsedata;
	    
	    $this->response($response, 200);
	}
	
	public function listpopularproduct_get(){
	    
	    //current_lat=11.565723328439&
	    //current_lng=104.889135360&
	    //row=20&
	    //page=2
	    $request["current_lat"] = $this->input->get('current_lat');
	    $request["current_lng"] = $this->input->get('current_lng');
	    $request["row"] = $this->input->get('row');
	    $request["page"] = $this->input->get('page');
	    
	    $responsequery = $this->ProductModel->listPopularProduct($request);
	    
	    $responsedata = $responsequery["response_data"];
	    if(count($responsedata) > 0){
	        
	        $this->load->helper('distancecalculator');
	        foreach($responsedata as $item){
	            $item->distance = distanceFormat($item->distance);
	        }
	    }
	    $response["response_code"] = "200";
	    $response["total_record"] = $responsequery["total_record"];
	    $response["total_page"] = $responsequery["total_page"];
	    $response["response_data"] = $responsedata;
	    $this->response($response, 200);
	}
	
	public function get_product_detail_post(){
        /*{
         * user_timezone : Asia/Phnom_Penh
         * }
         * */
		$request = json_decode($this->input->raw_input_stream,true);
		
		if(!isset($request["request_data"])){
			$response["response_code"] = "400";
			$response["error"] = "bad request";
			$this->response($response, 400);
			die();
		}
		
		
		$request = $request["request_data"];
		$this->load->helper('validate');
	   	if(!isset($request["product_id"]) || !validateNumeric($request["product_id"])){
		        $response["response_code"] = "400";
		        $response["error"] = "invalid product_id";
		        $this->response($response, 400);
		        die();
	    }
	    if(!isset($request["user_timezone"])){
	        $request["user_timezone"] = "Asia/Phnom_Penh";
	    }
	        
		
		$product_detail = $this->ProductModel->getProductDetail($request);		  
		if(count($product_detail) > 0){
			
		    $now = new DateTime($product_detail[0]->shop_time_zone);
		    $now = strtotime($now->format('H:i:s'));
		    
		    $is_open = 0;
		    
		    if(strtotime($product_detail[0]->shop_opening_time) < $now && strtotime($product_detail[0]->shop_close_time) > $now){
		        $is_open = 1;
		    }
		    $product_detail[0]->is_shop_open = $is_open;
		    
		    $tz_date_s = new DateTime($product_detail[0]->shop_opening_time, new DateTimeZone($product_detail[0]->shop_time_zone));
		    $tz_date_s->setTimezone(new DateTimeZone($request["user_timezone"]));
		    $tz_date_e = new DateTime($product_detail[0]->shop_close_time, new DateTimeZone($product_detail[0]->shop_time_zone));
		    $tz_date_e->setTimezone(new DateTimeZone($request["user_timezone"]));
		    
		    $product_detail[0]->shop_opening_time = $tz_date_s->format('H:i:s');
		    $product_detail[0]->shop_close_time = $tz_date_e->format('H:i:s');
		    
		    $product_detail[0]->display_time =  date('h:i A', strtotime($product_detail[0]->shop_opening_time))." - ".date('h:i A', strtotime($product_detail[0]->shop_close_time));
		    
			//===========request related product===============
			$reqest_related_pro["shop_id"] = $product_detail[0]->shop_id;
			$reqest_related_pro["row"] = 5;
			$reqest_related_pro["page"] = 1;
			$reqest_related_pro["is_popular"] = 1;
			$related_products = $this->ProductModel->listProductByShopid($reqest_related_pro);
								
			$response["product_detail"] = $product_detail[0];
			$response["related_products"] = $related_products;
			$response["response_code"] = "200";
			$response["response_msg"] = "list successfully";
			$this->response($response, 200);
		}else{
			$response["response_code"] = "000";
			$response["response_msg"] = "not found";
			$this->response($response, 200);
		}
	}
	
	
	
}

?>