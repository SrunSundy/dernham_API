
<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class AdvertisementRestController extends REST_Controller{

  public function __construct() {
  
    parent::__construct();
  
    if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }
    $this->load->model('AdvertisementModel');
  
  }
  
  public function index(){
    $this->load->view('index');
  }

  function list_home_advertisement_header_get(){
    
    $request["user_id"] = $this->input->get('user_id');
    
    if(!isset($request["user_id"])){
      $response["response_code"] = "400";
      $response["error"] = "user_id is invalid!";
      $this->response($response, 400);
      die();
    }
    
    $responsequery = $this->AdvertisementModel->homeAdvertisementHeader($request);

    $response["response_code"] = "200";
    $response["response_data"] = $responsequery['response_data'];
    $this->response($response, 200);
  
    
  }

}
?>