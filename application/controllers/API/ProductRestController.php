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
		
		$is_popular = true;
		if(!isset($request["is_popular"]) || $request["is_popular"] == null){
			$is_popular = false;
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
	
}

?>