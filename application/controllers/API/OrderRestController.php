
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
    // Storing submitted values
    $sender_email = "mengky@dernham.com";
    $user_password = "01021992Men";
    $receiver_email = $request["user_email"];
    $username = "DerNham";
    $subject = "Test Email";
    $message = "Hello From DerNham. :)";

    // Configure email library
    //$config['protocol'] = 'smtp';
    $config['smtp_host'] = 'chi-node9.websitehostserver.net';
    $config['smtp_port'] = 587;
    $config['smtp_user'] = $sender_email;
    $config['smtp_pass'] = $user_password;

    // Load email library and passing configured values to email library
    $this->load->library('email', $config);
    $this->email->set_newline("\r\n");

    // Sender email address
    $this->email->from($sender_email, $username);
    // Receiver email address
    $this->email->to($receiver_email);
    // Subject of email
    $this->email->subject($subject);
    // Message in email
    $this->email->message($message);

    if ($this->email->send()) {
       $data['message_display'] = 'Email Successfully Send !';
    } else {
        $data['message_display'] =  '<p class="error_msg">Invalid Gmail Account or Password !</p>';
    }
    print_r($data);

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
      
      $contentHtml ="<h1>WELCOME TO DERNHAM</h1><p>Thanks for subscribing!</p>";
      $subject = "SUCCESS";
      $recipient = $request["recipient_email"];
      
      $this->response( $this->EmailModel->sentEmail($recipient, $subject , $contentHtml) ,200);
  }


}
?>