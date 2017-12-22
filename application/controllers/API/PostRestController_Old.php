<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

require APPPATH . '/libraries/REST_Controller.php';

// push notification

//==========end notification=========

class PostRestController extends REST_Controller{
  
  function __construct()
  {
    parent::__construct();    
    $this->load->model("PostModel");
  }
  
  public function index(){
    $this->load->view('index');
  } 
  
  function insertuserpost_post(){
    
    /* {
     "request_data" : {
      "post_image" : [{
        image_name : "abc.jpg"
      },
      {
        image_name : "bct.jpg"
      }],
      "caption" : "leap",
      "shop_id" : "20",
      "user_id" : "39",
      "type" : ""
      }
    } */
    
    $request = json_decode($this->input->raw_input_stream,true);
    if(!isset($request["request_data"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request.";
      $this->response($response, 400);
      die();
    }
    
    
    $request = $request["request_data"];
    $this->load->helper('validate');
    if(!isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"]) ||
      //!isset($request["shop_id"]) || IsNullOrEmptyString($request["shop_id"])||
      //!isset($request["caption"]) || IsNullOrEmptyString($request["caption"])||
      !isset($request["type"]) || IsNullOrEmptyString($request["type"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request..";
      $this->response($response, 400);
      die();
    }
    
    if(!isset($request["shop_id"]) || IsNullOrEmptyString($request["shop_id"])){
      $request["shop_id"] = "";
    }
    if(!isset($request["caption"]) || IsNullOrEmptyString($request["caption"])){
      $request["caption"] = "";
    }
    
    $this->db->trans_begin();
    $inserted_post_id = $this->PostModel->insertUserPost($request);
    
    if($inserted_post_id != 0){     
      
      $this->load->model("PostImageModel");
      $request["post_id"] = $inserted_post_id;
      $status = $this->PostImageModel->insertUserPostImage( $request);
      if ($this->db->trans_status() === FALSE)
      {
        $this->db->trans_rollback();
        $response["response_code"] = "000";
        $response["response_msg"] = "Transaction rollback!";
        $this->response($response ,200);
      }
      else
      {
        $this->db->trans_commit();
        /* for($i=0; $i< count($request["post_image"]); $i++){
          copy('./uploadimages/temp/post/big/'.$request["post_image"][$i]["image_name"], $_SERVER['DOCUMENT_ROOT'].'/user_postimage/'.$request["post_image"][$i]["image_name"]);
          
        } */
        $this->load->model("UploadModel");
        $is_move = $this->UploadModel->moveThreeTpyeImageToReal(UPLOAD_FILE_PATH ."/uploadimages/temp/post/",
            UPLOAD_FILE_PATH ."/uploadimages/real/post/", $request["post_image"]);
            
        //=============notify user=============
        $request["action_id"] = 4; // post, notify all followers
        $request["object_id"] = $request["post_id"];
        $notifyfollowers = $this->PostModel->notifyFollowers($request); 
        
        $response["response_code"] = "200";
        $response["response_msg"] = "Post successfully";
        $this->response($response ,200);    
      }
    }else{
      $response["response_code"] = "000";
      $response["response_msg"] = "Post Fails!";
      $this->response($response ,200);
    } 
  }
  
  function updateuserpost_post(){
    /* {
     "request_data" : {
      "add_post_image" : [{
        "image_name" : "tjlksd.jpg"
      },{
        "image_name" : "jslfjsjf.jpg" 
      }],
      "remove_post_image" : [{
        "post_image_id" : 1
      }, {
        "post_image_id" : 2
      }],
      "caption" : "leapsdfsd",
      "shop_id" : "20",
      "post_id" : "1"
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
    $check = $this->PostModel->updateUserPost($request);
    
    if($check){
      $response["response_code"] = "200";
      $response["response_msg"] = "update post successfully";
      $this->response($response ,200);
    }else{
      $response["response_code"] = "000";
      $response["response_msg"] = "update post Fails!";
      $this->response($response ,200);
    }
    
  }
  
  
  
  function user_like_post(){
    /* 
    {
      "request_data" : {
      "user_id" : "1",
      "post_id" : "56"
      }
    }  */
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
      !isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }

    $data = $this->PostModel->userLike($request);
    if($data){
      $data1 = $this->PostModel->countLike($request);
      
      //insert into notification tb
      $request["actioner_id"]=$request["user_id"];
      $request["object_id"]=$request["post_id"];
      $request["user_id"]="";
      $request["action_id"]=1; //1 = like, 2 = comment, 3 = follow, 4 = post
      
      $this->load->model("UserModel");
      $is_notify = $this->UserModel->checkIfNotifyUser($request);
      if($is_notify){
        $notify = $this->PostModel->notifyUser($request);
      }
  
      $response["response_data"] = $data1;
      $response["response_code"] = "200";
      $response["response_msg"] = "like succeed!";
      $this->response($response ,200);
    }else{
      $response["response_code"] = "000";
      $response["response_msg"] = "like failed!";
      $this->response($response ,200);
    }
  }
  
  function user_unlike_post(){
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
      !isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }
    
    $data = $this->PostModel->userUnlike($request);
    if($data){
      $data1 = $this->PostModel->countLike($request);
      
      $request["action_id"] = 1; // like
      $remove = $this->PostModel->removeNotification($request);
      
      $response["response_data"] = $data1;
      $response["response_code"] = "200";
      $response["response_msg"] = "unlike succeed!";
      $this->response($response ,200);
      
      
    }else{
      $response["response_code"] = "000";
      $response["response_msg"] = "unlike failed!";
      $this->response($response ,200);
    }
  }
  
  function view_likers_post(){
    $request = json_decode($this->input->raw_input_stream,true);
    
    if(!isset($request["request_data"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }
    $request = $request["request_data"];
    
    $this->load->helper('validate');
    if(!isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])||
    !isset($request["user_id"]) || IsNullOrEmptyString($request["user_id"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }
    
    $data = $this->PostModel->viewLikers($request);
    
    //$this->load->model("UserModel");
    //$data1 = $this->UserModel->getUserProfileFollow($request);
    if($data){
      $response["response_data"] = $data;
      //$response["is_followed"] = $data1;
      $response["response_code"] = "200";
      $response["response_msg"] = "view succeed!";
      $this->response($response ,200);
    }else{
      $response["response_code"] = "000";
      $response["response_msg"] = "view failed!";
      $this->response($response ,200);
    }
  }
  
  function listuserpost_get(){
    
    //row=20&
    //page=2&
    //user_id
    //user_timezone=
    
    $request["row"] = $this->input->get('row');
    $request["page"] = $this->input->get('page');
    $request["user_timezone"] = $this->input->get('user_timezone');
    
    $responsequery = $this->PostModel->listUserPost($request);
    
    $response["response_code"] = "200";
    $response["total_record"] = $responsequery["total_record"];
    $response["total_page"] = $responsequery["total_page"];
    
    
    $response_data = $responsequery["response_data"];
    
    if(count($response_data) > 0){
        
        $this->load->helper('timecalculator');
        $this->load->model("CommentModel");
        $this->load->model("PostImageModel");
        
      foreach($response_data as $item){         
        $request_com["post_id"] = $item->post_id;     
        $item->comment_count = $this->CommentModel->countCommentByPostid($request_com)->count;
        
        $request_pimg["post_id"] = $item->post_id;
        $request_pimg["row"] = 9999999999;
        $request_pimg["page"] = 1;        
        $item->post_img = $this->PostImageModel->listUserPostImageByPostid($request_pimg)["response_data"];
        
        $request_dcom["post_id"] = $item->post_id;
        $request_dcom["row"] = 1;
        $request_dcom["page"] = 1;
        $request_dcom["order_type"]= 1;
        $item->comment_item = $this->CommentModel->listCommentByPostId($request_dcom)["response_data"];
        $item->like_count = $this->PostModel->countLike($request_com)->count;
        
        $request_islike["post_id"] = $item->post_id;
        $request_islike["user_id"] = $this->input->get("user_id");
        $item->is_liked = $this->PostModel->isUserLiked($request_islike)->is_liked;
                
        $request_issaved["object_id"] = $item->post_id;
        $request_issaved["user_id"] = $this->input->get("user_id");
        $request_issaved["saved_type"] = "post";
        $item->is_saved = $this->PostModel->isUserSaved($request_issaved)->is_saved;
        
        $request_isreported["post_id"] = $item->post_id;
        $request_isreported["user_id"] = $this->input->get("user_id");
        $item->is_reported = $this->PostModel->isUserReported($request_isreported)->is_reported;
        
        $item->post_created_date = tz($item->post_created_date, $request["user_timezone"]);
        
      }
    }
    
    $response["response_data"] = $response_data;
    $this->response($response, 200);
    
  }
  
  function listexpandedsavedpost_get(){
      
      //row=20&
      //page=1&
      //user_id
      //user_timezone
      //post_id
      
      $request["row"] = $this->input->get('row');
      $request["page"] = $this->input->get('page');
      $request["user_id"] = $this->input->get('user_id');
      $request["post_id"] = $this->input->get('post_id');
      $request["user_timezone"] = $this->input->get('user_timezone');
      
      if(!isset( $request["user_id"])){
          
          $response["response_code"] = "400";
          $response["error"] = "user_id is required!";
          $this->response($response, 400);
          die();
      }
      
      
      
      $responsequery = $this->PostModel->listExpandedSavedPost($request);
      
      $response["response_code"] = "200";
      $response["total_record"] = $responsequery["total_record"];
      $response["total_page"] = $responsequery["total_page"];
      
      $response_data = array();
      
      if( isset($request["post_id"]) && (int)$request["page"] == 1){
          $addition = $this->PostModel->getSavedPost($request);
          if(isset($addition["response_data"])){
              array_push($response_data, $addition["response_data"]);
          }
         
          /*$response["response_data"] = $response_data;
          $this->response($response, 200);
          die;*/
         
      }
      
      if(count($responsequery["response_data"]) > 0){
          foreach($responsequery["response_data"] as $data){
              array_push($response_data , $data);
          }
      }
            
      if(count($response_data) > 0){
          
          $this->load->helper('timecalculator');   
          $this->load->model("CommentModel");
          $this->load->model("PostImageModel");
          
          foreach($response_data as $item){
              $request_com["post_id"] = $item->object_id;
              $item->comment_count = $this->CommentModel->countCommentByPostid($request_com)->count;
              
              $request_pimg["post_id"] = $item->object_id;
              $request_pimg["row"] = 9999999999;
              $request_pimg["page"] = 1;           
              $item->post_img = $this->PostImageModel->listUserPostImageByPostid($request_pimg)["response_data"];             
             
              $request_dcom["post_id"] = $item->object_id;
              $request_dcom["row"] = 1;
              $request_dcom["page"] = 1;
              $request_dcom["order_type"]= 1;
              $item->comment_item = $this->CommentModel->listCommentByPostId($request_dcom)["response_data"];
              $item->like_count = $this->PostModel->countLike($request_com)->count;
              
              $request_islike["post_id"] = $item->object_id;
              $request_islike["user_id"] = $request["user_id"];
              $item->is_liked = $this->PostModel->isUserLiked($request_islike)->is_liked;
              
              $request_issaved["object_id"] = $item->object_id;
              $request_issaved["user_id"] = $this->input->get("user_id");
              $request_issaved["saved_type"] = "post";
              $item->is_saved = $this->PostModel->isUserSaved($request_issaved)->is_saved;
              
              $request_isreported["post_id"] = $item->object_id;
              $request_isreported["user_id"] = $this->input->get("user_id");
              $item->is_reported = $this->PostModel->isUserReported($request_isreported)->is_reported;
              
              $item->post_created_date = tz($item->post_created_date, $request["user_timezone"]);
          }
      }
      
      $response["response_data"] = $response_data;
      $this->response($response, 200);
      
  }
  
  function list_saved_posts_get(){
    
    //row=20&
    //page=2&
    //user_id
    //user_timezone
      
    $request["row"] = $this->input->get('row');
    $request["page"] = $this->input->get('page'); 
    $request["user_id"] = $this->input->get('user_id');
    $request["user_timezone"] = $this->input->get('user_timezone');
    
    $responsequery = $this->PostModel->listSavedPosts($request);
    
    $response["response_code"] = "200";
    $response["total_record"] = $responsequery["total_record"];
    $response["total_page"] = $responsequery["total_page"];
    
    $response_data = $responsequery["response_data"];
    
    if(count($response_data) > 0){
        $this->load->helper('timecalculator');
        foreach($response_data as $item){
            $item->created_date = tz($item->created_date, $request["user_timezone"]);
        }
    }
        
    $response["response_data"] = $response_data;
    $this->response($response, 200);
    
  }
  
  function listprofilepost_get(){
    
    //row=20&
    //page=2
    //user_timezone
    
    $request["row"] = $this->input->get('row');
    $request["page"] = $this->input->get('page');
    $request["profile_id"] = $this->input->get('profile_id');
    $request["user_timezone"] = $this->input->get('user_timezone');
    
    $responsequery = $this->PostModel->listProfilePost($request);
    
    $response["response_code"] = "200";
    $response["total_record"] = $responsequery["total_record"];
    $response["total_page"] = $responsequery["total_page"];
    
    $response_data = $responsequery["response_data"];
    
    if(count($response_data) > 0){
        
        $this->load->helper('timecalculator');
        $this->load->model("CommentModel");
        $this->load->model("PostImageModel");
      foreach($response_data as $item){         
        $request_com["post_id"] = $item->post_id;       
        $item->comment_count = $this->CommentModel->countCommentByPostid($request_com)->count;
        
        $request_pimg["post_id"] = $item->post_id;
        $request_pimg["row"] = 9999999999;
        $request_pimg["page"] = 1;
        
        $item->post_img = $this->PostImageModel->listUserPostImageByPostid($request_pimg)["response_data"];       
        $request_dcom["post_id"] = $item->post_id;
        $request_dcom["row"] = 1;
        $request_dcom["page"] = 1;
        $request_dcom["order_type"]= 1;
        $item->comment_item = $this->CommentModel->listCommentByPostId($request_dcom)["response_data"];
        $item->like_count = $this->PostModel->countLike($request_com)->count;
        
        $request_islike["post_id"] = $item->post_id;
        $request_islike["user_id"] = $this->input->get("user_id");
        $item->is_liked = $this->PostModel->isUserLiked($request_islike)->is_liked;
        
        $request_issaved["object_id"] = $item->post_id;
        $request_issaved["user_id"] = $this->input->get("user_id");
        $request_issaved["saved_type"] = "post";
        $item->is_saved = $this->PostModel->isUserSaved($request_issaved)->is_saved;
        
        $request_isreported["post_id"] = $item->post_id;
        $request_isreported["user_id"] = $this->input->get("user_id");
        $item->is_reported = $this->PostModel->isUserReported($request_isreported)->is_reported;
        
        $item->post_created_date = tz($item->post_created_date, $request["user_timezone"]);
        
      }
    }
    
    $response["response_data"] = $response_data;
    $this->response($response, 200);
    
  }
  
  
  function list_profile_postimage_get(){
    
    //row=20&
    //page=2
    //profile_id
    //user_timezone
    
    $request["row"] = $this->input->get('row');
    $request["page"] = $this->input->get('page');
    $request["profile_id"] = $this->input->get('profile_id');
    $request["user_timezone"] = $this->input->get('user_timezone');
    
    $responsequery = $this->PostModel->listProfilePostImages($request);
    
    $response["response_code"] = "200";
    $response["total_record"] = $responsequery["total_record"];
    $response["total_page"] = $responsequery["total_page"];
    
    $response_data = $responsequery["response_data"];
    
    if(count($response_data) > 0){
        $this->load->helper('timecalculator');
        foreach($response_data as $item){
            $item->post_created_date = tz($item->post_created_date, $request["user_timezone"]);
        }
    }
    
    
    $response["response_data"] = $response_data;
    $this->response($response, 200);
    
  }
  
  function view_comment_post(){
    $request = json_decode($this->input->raw_input_stream,true);
    
    if(!isset($request["request_data"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }
    $request = $request["request_data"];
    
    $this->load->helper('validate');
    if(!isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }
    
    if(!isset($request["user_timezone"])){
        $request["user_timezone"] = "Asia/Phnom_Penh";
    }
    
    $data = $this->PostModel->viewComment($request);
    
    if($data){
        if(count($data) > 0){
            $this->load->helper('timecalculator');
            foreach($data as $item){
                $item->created_date = tz($item->created_date, $request["user_timezone"]);
            }
        }
        
      $response["response_data"] = $data;
      $response["response_code"] = "200";
      $response["response_msg"] = "view succeed!";
      $this->response($response ,200);
    }else{
      $response["response_code"] = "000";
      $response["response_msg"] = "view failed!";
      $this->response($response ,200);
    }
  }
  
  function user_comment_post(){
    
    /* {
      "request_data" : {
        "user_id" : "1",
        "post_id" : "56",
        "text" : "abc"
      }
    }  */
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
      !isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"]) ||
      !isset($request["text"]) || IsNullOrEmptyString($request["text"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }

    $data = $this->PostModel->userComment($request);
    if($data){
    
      
      //insert into notification tb
      $request["actioner_id"]=$request["user_id"];
      $request["object_id"]=$request["post_id"];
      $request["user_id"]="";
      $request["action_id"] = 2; //1 = like, 2 = comment, 3 = follow, 4 = post
      
      $this->load->model("UserModel");
      $is_notify = $this->UserModel->checkIfNotifyUser($request);
      if($is_notify){
        $notify = $this->PostModel->notifyUser($request);
        
        $user["user_id"] = $request["actioner_id"];
        $user = $this->PostModel->getUserInfo($user);
        $device = $this->PostModel->getDeviceNotification($request);
        
        if(!empty($device)){
          $this->load->helper('notification');
          push_notification($device, $user, $request["post_id"]);
        }

      }
      
      $response["response_code"] = "200";
      $response["response_msg"] = "comment successfully!";
      $this->response($response ,200);
      
    }else{
      $response["response_code"] = "000";
      $response["response_msg"] = "comment failed!";
      $this->response($response ,200);
    }
  }
  
  function get_post_by_id_post(){ 
                                      
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
      !isset($request["post_id"]) || IsNullOrEmptyString($request["post_id"])){
      $response["response_code"] = "400";
      $response["error"] = "bad request";
      $this->response($response, 400);
      die();
    }
    
    if(!isset($request["user_timezone"])){
        $request["user_timezone"] = "Asia/Phnom_Penh";
    }
    
    $responsequery = $this->PostModel->listPostByID($request);
    
    $response["response_code"] = "200";
    $response_data = $responsequery["response_data"];
    
    if(count($response_data) > 0){
        
        $this->load->helper('timecalculator');
        $this->load->model("CommentModel");
        $this->load->model("PostImageModel");$this->input->get("user_id");
      foreach($response_data as $item){         
        $request_com["post_id"] = $item->post_id;       
        $item->comment_count = $this->CommentModel->countCommentByPostid($request_com)->count;
        
        $request_pimg["post_id"] = $item->post_id;
        $request_pimg["row"] = 9999999999;
        $request_pimg["page"] = 1;        
        $item->post_img = $this->PostImageModel->listUserPostImageByPostid($request_pimg)["response_data"];
        
        $request_dcom["post_id"] = $item->post_id;
        $request_dcom["row"] = 1;
        $request_dcom["page"] = 1;
        $request_dcom["order_type"]= 1;
        $item->comment_item = $this->CommentModel->listCommentByPostId($request_dcom)["response_data"];
        $item->like_count = $this->PostModel->countLike($request_com)->count;
        
        $request_islike["post_id"] = $item->post_id;
        $request_islike["user_id"] = $request["user_id"];
        $item->is_liked = $this->PostModel->isUserLiked($request_islike)->is_liked;
              
        $item->post_created_date = tz($item->post_created_date, $request["user_timezone"]);
        
      }
    }
    
    $response["response_data"] = $response_data;
    $this->response($response, 200);
    
  }

  
  
  
  
}

?>