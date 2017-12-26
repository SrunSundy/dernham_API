<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class OrderRestController extends REST_Controller{

  public function __construct() {
  
    parent::__construct();
    $this->load->model("OrderModel");

    if(strcasecmp($this->input->method(), "POST") == 0 && strcasecmp($_SERVER["CONTENT_TYPE"],"application/json")!=0 ){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }
  
  }
  
  public function index(){
    $this->load->view('index');
  }
  
  

  function send_delivery_email_post(){
    
    $request = json_decode($this->input->raw_input_stream,true);
      
    if(!isset($request["request_data"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }
  
    $request = $request["request_data"];
    
    $this->load->helper('validate');
    if(!isset($request["user_email"]) || IsNullOrEmptyString($request["user_email"])){
      $response["response_code"] = "400";
      $response["error"] = "user_email in valid";
      $this->response($response, 400);
      die();
    }
  
      $this->load->library('email');

      $result['address'] = "Phnom Penh New Life Church, Preah Trasak Paem St. (63), Phnom Phen City.";
      $result['order_code'] ="37_2_123123";
      $result['order_date_time'] ="04-12-2017 10:00 AM";
      $result['user_name'] ="Sopheamen";
      $result['user_phone'] ="096 444 4204";

      $result['tax'] ="N/A";
      $result['delivery_fee'] ="$ 1";
      $result['coupon'] ="N/A";
      $result['grand_total'] ="$ 35";

      $food1 = [
        'id' => 1,
        'pro_name' => 'Milk Green Tea',
        'pro_size' => 'Medium',
        'pro_image' => 'https://instagram.fpnh1-1.fna.fbcdn.net/t51.2885-15/s640x640/sh0.08/e35/23160955_382296242199276_2296046476574326784_n.jpg',
        'shop_name' => 'ABC Store',
        'note' => 'No Ice',
        'quantity' => '2',
        'pro_price' => '$ 10',
        'total_price' => '$ 20'
      ];
      
      $food2 = [
        'id' => 2,
        'pro_name' => 'Ice Latte',        
        'pro_size' => 'Medium',
        'pro_image' => 'https://instagram.fpnh1-1.fna.fbcdn.net/t51.2885-15/s640x640/sh0.08/e35/23160955_382296242199276_2296046476574326784_n.jpg',
        'shop_name' => 'ABC Store',
        'note' => 'No Ice',
        'quantity' => '3',
        'pro_price' => '$ 5',
        'total_price' => '$ 15'
      ];

    $result['food'] = [$food1, $food2];

    $mesg = $this->load->view('/ordering_header','',true);
    $mesg .= $this->load->view('/ordering_body',$result,true);
    $mesg .= $this->load->view('/ordering_footer','',true);

      $subject = 'This is a test';
      $config=array(
      'charset'=>'utf-8',
      'wordwrap'=> TRUE,
      'mailtype' => 'html'
      );

    $this->email->initialize($config);
    $result = $this->email
      ->from('info@dernham.com')
      ->reply_to('')    // Optional, an account where a human being reads.
      ->to($request["user_email"])
      ->subject($subject)
      ->message($mesg)
      ->send();

    //var_dump($result);
    //echo '<br />';
    //echo $this->email->print_debugger();
    
    //exit;
    $responsequery['response_data']=$result;
    $response["response_code"] = "200";
    $response["response_data"] = $responsequery['response_data'];
    $this->response($response, 200);
  
  }

  function user_order_post(){
    
    $request = json_decode($this->input->raw_input_stream,true);
    
    if(!isset($request["request_data"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }

    $request = $request["request_data"];
    $this->load->helper('validate');
    if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
      !isset($request["user_phone"]) || IsNullOrEmptyString($request["user_phone"]) ||
      !isset($request["order_code"]) || IsNullOrEmptyString($request["order_code"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }


    foreach ($request["product_list"] as $item) {
      $request["product_id"]  = $item["pro_id"];
      $request["size"]  = $item["pro_size"];
      $request["quantity"]  = $item["quantity"];
      $data = $this->OrderModel->userOrder($request);
    }
    

    if($data){
      $response["response_code"] = "200";
      $response["response_msg"] = "ordered successfully";
      $this->response($response ,200);

    }else{
      $response["response_code"] = "000";
      $response["response_msg"] = "order failed!";
      $this->response($response ,200);
    }


  }
  

  // Send Gmail to another user
  public function send_email_post() {
    $request = json_decode($this->input->raw_input_stream,true);
    $request = $request["request_data"];
          

    $result['address'] = "Phnom Penh New Life Church, Preah Trasak Paem St. (63), Phnom Phen City.";
      $result['order_code'] ="37_2_123123";
      $result['order_date_time'] ="04-12-2017 10:00 AM";
      $result['user_name'] ="Sopheamen";
      $result['user_phone'] ="096 444 4204";

      $result['tax'] ="N/A";
      $result['delivery_fee'] ="$ 1";
      $result['coupon'] ="N/A";
      $result['grand_total'] ="$ 35";

      $food1 = [
        'id' => 1,
        'pro_name' => 'Milk Green Tea',
        'pro_size' => 'Medium',
        'pro_image' => 'https://instagram.fpnh1-1.fna.fbcdn.net/t51.2885-15/s640x640/sh0.08/e35/23160955_382296242199276_2296046476574326784_n.jpg',
        'shop_name' => 'ABC Store',
        'note' => 'No Ice',
        'quantity' => '2',
        'pro_price' => '$ 10',
        'total_price' => '$ 20'
      ];
      
      $food2 = [
        'id' => 2,
        'pro_name' => 'Ice Latte',        
        'pro_size' => 'Medium',
        'pro_image' => 'https://instagram.fpnh1-1.fna.fbcdn.net/t51.2885-15/s640x640/sh0.08/e35/23160955_382296242199276_2296046476574326784_n.jpg',
        'shop_name' => 'ABC Store',
        'note' => 'No Ice',
        'quantity' => '3',
        'pro_price' => '$ 5',
        'total_price' => '$ 15'
      ];

    $result['food'] = [$food1, $food2];

    $mesg = $this->load->view('/ordering_header','',true);
    $mesg .= $this->load->view('/ordering_body',$result,true);
    $mesg .= $this->load->view('/ordering_footer','',true);


    $this->load->model("EmailModel");
      
    $subject = "Order From DerNham";
    $recipient = $request["recipient_email"];
	
	echo $mesg;
    //$this->response($mesg ,200);

  }
  
  public function sendmailtest_post(){
      
      $request = json_decode($this->input->raw_input_stream,true);
      if(!isset($request["request_data"])){
          $response["response_code"] = "400";
          $response["error"] = "bad request";
          $this->response($response, 400);
          die();
      }
      
      $request = $request["request_data"];
      
      $this->load->model("EmailModel");
      
      
      $recipient = $request["recipient_email"];
      
      $response_data = $this->EmailModel->sentEmail($recipient);
      if(isset($response_data->response_code) && ( $response_data->response_code == "200" || $response_data->response_code == "000")){
          $this->response($response_data,200);
      }else{
          $this->response($response_data,400);
      }
  }
  public function sendEmailto_post(){
	    $request = json_decode($this->input->raw_input_stream,true);           
		  if(!isset($request["request_data"])){
			  $response["response_code"] = "400";
			  $response["error"] = "bad request";
			  $this->response($response, 400);
			  die();
		  }

    $request = $request["request_data"];
          

    $result['address'] = "Phnom Penh New Life Church, Preah Trasak Paem St. (63), Phnom Phen City.";
      $result['order_code'] ="37_2_123123";
      $result['order_date_time'] ="04-12-2017 10:00 AM";
      $result['user_name'] ="Sopheamen";
      $result['user_phone'] ="096 444 4204";

      $result['tax'] ="N/A";
      $result['delivery_fee'] ="$ 1";
      $result['coupon'] ="N/A";
      $result['grand_total'] ="$ 35";

      $food1 = [
        'id' => 1,
        'pro_name' => 'Milk Green Tea',
        'pro_size' => 'Medium',
        'pro_image' => 'https://instagram.fpnh1-1.fna.fbcdn.net/t51.2885-15/s640x640/sh0.08/e35/23160955_382296242199276_2296046476574326784_n.jpg',
        'shop_name' => 'ABC Store',
        'note' => 'No Ice',
        'quantity' => '2',
        'pro_price' => '$ 10',
        'total_price' => '$ 20'
      ];
      
      $food2 = [
        'id' => 2,
        'pro_name' => 'Ice Latte',        
        'pro_size' => 'Medium',
        'pro_image' => 'https://instagram.fpnh1-1.fna.fbcdn.net/t51.2885-15/s640x640/sh0.08/e35/23160955_382296242199276_2296046476574326784_n.jpg',
        'shop_name' => 'ABC Store',
        'note' => 'No Ice',
        'quantity' => '3',
        'pro_price' => '$ 5',
        'total_price' => '$ 15'
      ];

    $result['food'] = [$food1, $food2];
   $mesg="";
   // $mesg = $this->load->view('/ordering_header','',true);
    $mesg .= $this->load->view('/ordering_body',$result,true);
    $mesg .= $this->load->view('/ordering_footer','',true);


    $this->load->model("EmailModel");
      
    $subject = "Order From DerNham";
    $recipient = $request["recipient_email"];
	
		  
		 // $contentHtml = "<h1>WELCOME TO DERNHAM</h1><p>Thanks for subscribing!</p>";
		  $subject = "SUCCESS";
		  $recipient = $request["recipient_email"];
	  
		$header= array('Content-Type: application/x-www-form-urlencoded'); 
		$urlapi="http://dev.dernham.com/sendemail.php";
		
		$postdata="recipient_email=$recipient&content=".base64_encode($mesg)."&subject=$subject";

	    $ch = curl_init($urlapi);                                                                    
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");    
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	
		curl_setopt($ch, CURLOPT_POST, 1);
																														
	    $result = curl_exec($ch);    
		echo   $result;
			  
 }


}
?>