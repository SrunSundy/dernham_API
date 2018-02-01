<?php 
class PostModel extends CI_Model{
  
  function __construct()
  {
    parent::__construct();
    $this->load->database();
  }
  
  function insertUserPost( $request ){
  
    $sql = "INSERT INTO nham_user_post(
        post_caption, 
        shop_id, 
        user_id,
                post_created_date
        ) 
      VALUES(?, ?, ?, ?)";
    
    $current_time = new DateTime();
    $current_time = $current_time->format('Y-m-d H:i:s');
    
    $param["post_caption"] = $request["caption"];
    $param["shop_id"] = $request["shop_id"];
    $param["user_id"] = $request["user_id"];
    $param["post_created_date"] = $current_time;
    
    
    $query = $this->db->query($sql , $param);     
      $inserted_id = $this->db->insert_id();    
    return $inserted_id;
  }
  
  function listUserPost( $request ){
    
    $row = (int)$request["row"];
    $page = (int)$request["page"];
    
    if(!$row) $row = 10;
    if(!$page) $page = 1;
    
    $limit = $row;
    $offset = ($row*$page)-$row;
    
    $order_type = " p.post_id DESC ";
    
    $param = array();
    $sql = "SELECT
          p.post_id,
          p.post_caption,
          p.post_created_date,
          p.shop_id,
          s.shop_name_en,
          s.shop_name_kh,
          s.shop_address,
          s.shop_status,
          s.is_delivery,
          p.user_id,
          u.user_fullname,
          u.user_photo,
          u.user_status,
          p.post_count_view,
          p.post_count_share,
           (SELECT count(ul.user_id) as count FROM nham_user_like ul where ul.post_id = p.post_id) AS like_count,
         (SELECT count(*) AS is_liked FROM nham_user_like ul WHERE ul.post_id = p.post_id AND ul.user_id = ?) AS is_liked,
           (SELECT count(*) AS is_saved
            FROM nham_saved_post sp WHERE sp.object_id = p.post_id AND sp.user_id = ? AND sp.saved_type = 'post' AND sp.status = 1) AS is_saved,
           (SELECT count(*) AS is_reported
            FROM nham_report_post rp WHERE rp.post_id = p.post_id AND rp.user_id = ? AND rp.status = 1) AS is_reported,       
           (SELECT count(*) FROM nham_user_follow uf WHERE uf.follower_id = ? AND uf.following_id = p.user_id ) AS is_followed
                  
    
        FROM nham_user_post p
        LEFT JOIN nham_shop s ON p.shop_id = s.shop_id
        LEFT JOIN nham_user u ON p.user_id = u.user_id
        WHERE p.post_status = 1 AND u.user_status = 1 ";
    
    
      
    array_push($param,  $request["user_id"], $request["user_id"] , $request["user_id"],  $request["user_id"]);
    $query_record = $this->db->query($sql, $param);
    $total_record = count($query_record->result());
    $total_page = $total_record / $row;
    if( ($total_record % $row) > 0){
      $total_page += 1;
    }
    
    $response["total_record"] = $total_record;
    $response["total_page"] = (int)$total_page;
    
    //0 means latest
    //1 means popular
    //2 means user that we follow
    //3 means post that is nearby
    if(isset($request["order_type"])){
      $orderNum = (int)$request["order_type"];
      switch($orderNum){
        case 0 : $order_type = "  p.post_id DESC ";break;
        case 1 : $order_type = "  like_count DESC, p.post_count_view DESC, p.post_count_share DESC ";break;
        case 2 : {
            $order_type = "  is_followed DESC , p.post_id DESC ";
            break;
        }
        case 3 : {
            
            if(!isset($request["current_lat"]) || !isset($request["current_lng"])) break;
            
            $current_lat = (float)$request["current_lat"];
            $current_lng = (float)$request["current_lng"];
                                  
            $order_type= " (CASE WHEN s.shop_id IS NOT NULL THEN 1 ELSE 0 END) DESC ,
                           (SQRT(POW(69.1 * (s.shop_lat_point - ? ), 2) +
                                    POW(69.1 * ( ? - s.shop_lng_point) * COS(s.shop_lat_point / 57.3), 2))*1.61) "; 
            array_push($param, $current_lat, $current_lng);
            break;
        } 
        default: $order_type = "  p.post_id DESC  ";break;
      }
      
    }
    
    $sql .= "\n ORDER BY ".$order_type;
    $sql .= "LIMIT ? OFFSET ?";
    array_push($param, $limit , $offset);
    $query = $this->db->query($sql, $param);
    
    $response["response_data"] = $query->result();
    return $response;
  }
  
  
  
  function nearestFriendPost($request){
      
      $current_lat = (float)$request["current_lat"];
      $current_lng = (float)$request["current_lng"];
      
      $row = (int)$request["row"];
      $page = (int)$request["page"];
      
      if(!$row) $row = 10;
      if(!$page) $page = 1;
      
      if(!$current_lat || $current_lat > 90 || $current_lat <-90) $current_lat= 0;
      if(!$current_lng || $current_lng > 180 || $current_lng < -180) $current_lng= 0;
      
      $limit = $row;
      $offset = ($row*$page)-$row;
      
      $sql = " SELECT 
                      up.post_id,
                      up.user_id,
                      u.user_fullname,
                      u.user_photo,
                      SQRT(
                      POW(69.1 * (sh.shop_lat_point - ? ), 2) +
                      POW(69.1 * ( ? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
                    FROM nham_user_post up
                    INNER JOIN nham_user u ON up.user_id = u.user_id
                    INNER JOIN nham_shop sh ON up.shop_id= sh.shop_id
                    WHERE up.user_id IN( SELECT following_id FROM nham_user_follow WHERE follower_id = ?)                 
                    ORDER BY distance ";
      
      $param = array();
      array_push($param, $current_lat, $current_lng ,$request["user_id"]);
      
      $query_record = $this->db->query($sql, $param);
      $total_record = count($query_record->result());
      $total_page = $total_record / $row;
      if( ($total_record % $row) > 0){
          $total_page += 1;
      }
      
      $response["total_record"] = $total_record;
      $response["total_page"] = (int)$total_page;
      
      $sql .= "LIMIT ? OFFSET ?";
      array_push($param, $limit , $offset);
      $query = $this->db->query($sql, $param);
      
      $response["response_data"] = $query->result();
      return $response;
  }
  
  
  function isUserLiked( $request ){
    
    $sql = "SELECT count(*) AS is_liked
      FROM nham_user_like WHERE post_id = ? AND user_id = ?";
      
    $param["post_id"] = $request["post_id"];
    $param["user_id"] = $request["user_id"]; 
    $query = $this->db->query($sql, $param);
    return $query->row();
  }
  
  function isUserSaved( $request ){
    
    $sql = "SELECT count(*) AS is_saved
      FROM nham_saved_post WHERE object_id = ? AND user_id = ? AND saved_type = ? AND status = 1";
      
    $param["object_id"] = $request["object_id"];
    $param["user_id"] = $request["user_id"]; 
    $param["saved_type"] = $request["saved_type"]; 
    $query = $this->db->query($sql, $param);
    return $query->row();
  }
  
  function isUserReported( $request ){
    
    $sql = "SELECT count(*) AS is_reported
      FROM nham_report_post WHERE post_id = ? AND user_id = ? AND status = 1";
      
    $param["post_id"] = $request["post_id"];
    $param["user_id"] = $request["user_id"]; 
    $query = $this->db->query($sql, $param);
    return $query->row();
  }
  
  
  function updateUserPost( $request ){
      
      $current_time = new DateTime();
      $current_time = $current_time->format('Y-m-d H:i:s');
      
    $sql = "UPDATE nham_user_post SET post_caption=? , post_updated_date=? ,shop_id=? WHERE post_id =? ";
    $param["post_caption"] = $request["caption"];
    $param["post_updated_date"] = $current_time;
    $param["shop_id"] = $request["shop_id"];
    $param["post_id"] = $request["post_id"];
    
    $query = $this->db->query($sql , $param);
    
    return ($this->db->affected_rows() != 1) ? false : true;
  }
  
  function userLike( $request ){
      
      $current_time = new DateTime();
      $current_time = $current_time->format('Y-m-d H:i:s');
    $sql = " INSERT INTO nham_user_like(user_id,post_id,created_date) 
        SELECT ?, ?, ? FROM dual 
        WHERE (
          SELECT count(*) from nham_user_like
          WHERE user_id = ? AND post_id = ?
        ) < 1 ";
    $param["user_id"] = $request["user_id"];
    $param["post_id"] = $request["post_id"];
    $param["created_date"] = $current_time;
    $param["user_id_1"] = $request["user_id"];
    $param["post_id_1"] = $request["post_id"];
    $query = $this->db->query($sql , $param);
    return $query;
  }
  
  
  function userUnlike( $request ){
    $sql = "DELETE from nham_user_like where user_id = ? and post_id = ?";
    $param["user_id"] = $request["user_id"];
    $param["post_id"] = $request["post_id"];
    
    $query = $this->db->query($sql , $param);
    return $query;
  }
  
  
  function countLike( $request ){
    $sql = "SELECT count(user_id) as count FROM nham_user_like where post_id = ?";
    $param["post_id"] = $request["post_id"];
    $query = $this->db->query($sql , $param);
    return $query->row();
  }
  
  function viewLikers( $request ){
  $sql = "SELECT u.user_id, 
    u.user_fullname, 
    u.user_photo, 
    u.user_quote,
    (SELECT count(*) FROM nham_user_follow WHERE follower_id = ? AND following_id = u.user_id  ) as followed
    FROM nham_user u
    WHERE u.user_id in (SELECT user_id FROM nham_user_like WHERE post_id = ?)" ;
    $param["user_id"] = $request["user_id"];
    $param["post_id"] = $request["post_id"];
    $query = $this->db->query($sql , $param);
    return $query->result();
  }
  
  function viewComment( $request ){
  $sql = "SELECT com.id,
    com.text,
    com.created_date,
    u.user_id,
    u.user_photo,
    u.user_fullname FROM nham_comment com
    LEFT JOIN nham_user u ON u.user_id = com.user_id
    WHERE com.post_id = ? AND com.status =1 ORDER BY com.id  " ;
    $param["post_id"] = $request["post_id"];
    $query = $this->db->query($sql , $param);
    return $query->result();
  }
  
  function userComment( $request ){
      
      $current_time = new DateTime();
      $current_time = $current_time->format('Y-m-d H:i:s');
  $sql = "INSERT INTO nham_comment(user_id, post_id, text, created_date) values(?, ?, ?, ?)" ;
    $param["user_id"] = $request["user_id"];
    $param["post_id"] = $request["post_id"];
    $param["text"] = $request["text"];
    $param["created_date"] = $current_time;
    $query = $this->db->query($sql , $param);
    return ($this->db->affected_rows() != 1) ? false : true;
  }
  
  function listProfilePost( $request ){
    
    $row = (int)$request["row"];
    $page = (int)$request["page"];
    $profile_id = $request["profile_id"];
    
    if(!$row) $row = 10;
    if(!$page) $page = 1;
    
    $limit = $row;
    $offset = ($row*$page)-$row;
    
    $param = array();
    $sql = "SELECT
          p.post_id,
          p.post_caption,
          p.post_created_date,
          p.shop_id,
          s.shop_name_en,
          s.shop_name_kh,
          s.shop_address,
          s.shop_status,
          p.user_id,
          u.user_fullname,
          u.user_photo,
          u.user_status,
          p.post_count_view,
          p.post_count_share          
        FROM nham_user_post p
        LEFT JOIN nham_shop s ON p.shop_id = s.shop_id
        LEFT JOIN nham_user u ON p.user_id = u.user_id
        WHERE p.post_status = 1 and p.user_id = ?
        ORDER BY p.post_id DESC ";
    
    $query_record = $this->db->query($sql, $profile_id);
    $total_record = count($query_record->result());
    $total_page = $total_record / $row;
    if( ($total_record % $row) > 0){
      $total_page += 1;
    }
    
    $response["total_record"] = $total_record;
    $response["total_page"] = (int)$total_page;
    
    $sql .= "LIMIT ? OFFSET ?";
    array_push($param, $profile_id, $limit , $offset);
    $query = $this->db->query($sql, $param);
    
    $response["response_data"] = $query->result();
    return $response;
  }
  
  function listProfilePostImages( $request ){
    
    $row = (int)$request["row"];
    $page = (int)$request["page"];
    $profile_id = $request["profile_id"];
    
    if(!$row) $row = 10;
    if(!$page) $page = 1;
    
    $limit = $row;
    $offset = ($row*$page)-$row;
    
    $param = array();
    $sql = "SELECT
          p.post_id,
          p.post_caption,
          p.post_created_date,
          pi.post_image_src       
        FROM nham_user_post p
        LEFT JOIN nham_user_post_image pi ON p.post_id = pi.post_id
        WHERE p.post_status = 1 and p.user_id = ?
        ORDER BY p.post_id DESC ";
    
    $query_record = $this->db->query($sql, $profile_id);
    $total_record = count($query_record->result());
    $total_page = $total_record / $row;
    if( ($total_record % $row) > 0){
      $total_page += 1;
    }
    
    $response["total_record"] = $total_record;
    $response["total_page"] = (int)$total_page;
    
    $sql .= "LIMIT ? OFFSET ?";
    array_push($param, $profile_id, $limit , $offset);
    $query = $this->db->query($sql, $param);
    
    $response["response_data"] = $query->result();
    return $response;
  }
  
  function listSavedPosts( $request ){
    
    $row = (int)$request["row"];
    $page = (int)$request["page"];
    $user_id = $request["user_id"];
    $saved_type = "post";
    
    if(!$row) $row = 10;
    if(!$page) $page = 1;
    
    $limit = $row;
    $offset = ($row*$page)-$row;
    
    $param = array();
    $sql = "SELECT
          sp.id,
          sp.object_id,
          pi.post_image_src,
          p.user_id,
          u.user_fullname,
                            u.user_photo,
                            sp.created_date
                                        
        FROM nham_saved_post sp
        LEFT JOIN nham_user_post p ON sp.object_id = p.post_id
        LEFT JOIN nham_user u ON p.user_id = u.user_id
        LEFT JOIN nham_shop s ON p.shop_id = s.shop_id
        LEFT JOIN nham_user_post_image pi ON sp.object_id = pi.post_id
        WHERE sp.saved_type = '".$saved_type."' AND p.post_status =  1  AND sp.user_id = ".$user_id." ORDER BY sp.created_date DESC ";
    
    
    $query_record = $this->db->query($sql);
    $total_record = count($query_record->result());
    $total_page = $total_record / $row;
    if( ($total_record % $row) > 0){
      $total_page += 1;
    }
    
    $response["total_record"] = $total_record;
    $response["total_page"] = (int)$total_page;
    
    $sql .= "LIMIT ? OFFSET ? ";
    array_push($param, $limit , $offset);
    $query = $this->db->query($sql, $param);
    
    $response["response_data"] = $query->result();
    return $response;
  }
  
  function listExpandedSavedPost( $request ){
      
      $row = (int)$request["row"];
      $page = (int)$request["page"];
      $user_id = $request["user_id"];
      
      
      if(!$row) $row = 10;
      if(!$page) $page = 1;
      
      $limit = $row;
      $offset = ($row*$page)-$row;
      
      $param = array();
      $sql = "SELECT 
                  sp.object_id,
                  p.post_caption,
                    p.post_count_view,
                    p.post_count_share,
                    p.post_created_date,
                  p.shop_id,
                    sh.shop_name_en,
                    sh.shop_name_kh,
                    sh.shop_address,
                    sh.shop_status,
                    p.user_id,
                    u.user_fullname,
                    u.user_photo,
                    u.user_status 
                FROM nham_saved_post sp 
                LEFT JOIN nham_user_post p ON p.post_id = sp.object_id
                LEFT JOIN nham_shop sh ON p.shop_id = sh.shop_id
                LEFT JOIN nham_user u ON p.user_id = u.user_id
                WHERE sp.user_id = ? 
                AND sp.saved_type='post' 
                AND p.post_status =  1 ";
      
      array_push($param, $user_id);
      if(isset($request["post_id"])){
          $post_id = $request["post_id"];
          $sql .=" AND sp.object_id <> ? ";
          array_push($param, $post_id);
      }
      
      $sql .= " ORDER BY sp.created_date DESC ";
      $query_record = $this->db->query($sql, $param);
      $total_record = count($query_record->result());
      $total_page = $total_record / $row;
      if( ($total_record % $row) > 0){
          $total_page += 1;
      }
      
      $response["total_record"] = $total_record;
      $response["total_page"] = (int)$total_page;
      
      $sql .= "LIMIT ? OFFSET ? ";
      array_push($param, $limit , $offset);
      $query = $this->db->query($sql, $param);
      
      $response["response_data"] = $query->result();
      return $response;
  }
  
  function getSavedPost($request){
      
      $user_id = $request["user_id"];
      $post_id = $request["post_id"];
      
      $param = array();
      $sql = "SELECT 
                    sp.object_id,
                    p.post_caption,
                    p.post_count_view,
                    p.post_count_share,
                    p.post_created_date,
                    p.shop_id,
                    sh.shop_name_en,
                    sh.shop_name_kh,
                    sh.shop_address,
                    sh.shop_status,
                    p.user_id,
                    u.user_fullname,
                    u.user_photo,
                    u.user_status 
                FROM nham_saved_post sp 
                LEFT JOIN nham_user_post p ON p.post_id = sp.object_id
                LEFT JOIN nham_shop sh ON p.shop_id = sh.shop_id
                LEFT JOIN nham_user u ON p.user_id = u.user_id
                WHERE sp.user_id = ? AND sp.saved_type='post' AND p.post_status =  1 
                AND sp.object_id= ? ";
      
      array_push($param, $user_id, $post_id );
      $query = $this->db->query($sql, $param);
      
      $response["response_data"] = $query->row();
      return $response;
      
  }
  
  function getUserInfo($request){
    $sql = "SELECT u.user_fullname
        FROM nham_user u
        WHERE u.user_id = ? LIMIT 1";
    
    $param["user_id"] = $request["user_id"];
    $query = $this->db->query($sql , $param);
    return $query->row();
  }
  
  function getTokenNotification($request){
    $sql = "SELECT distinct ul.token_id FROM nham_user_log ul WHERE ul.user_id in 
      (SELECT p.user_id from nham_user_post p where p.post_id = ?)
      order by ul.created_date DESC limit 1";
    
    $param["post_id"] = $request["post_id"];
    $query = $this->db->query($sql , $param);
    return $query->row();
  }
  
  function notifyUser($request){
    $des = array("","liked your post.","commented on your post.","followed you.", "created a post.");
    $sql = "INSERT INTO nham_notification(
        actioner_id, 
        user_id, 
        object_id, 
        action_id,
        description
        ) 
      VALUES(?, ?, ?, ?, ?)";
    
    $param["actioner_id"] = $request["actioner_id"];
    $param["user_id"] = $request["user_id"];
    $param["object_id"] = $request["object_id"];
    $param["action_id"] = $request["action_id"];
    $param["description"] = $des[$request["action_id"]];
    
    $query = $this->db->query($sql , $param);     
        $inserted_id = $this->db->insert_id();    
    return $inserted_id;
  }
  
  function notifyFollowers($request){
    $des = array("","liked your post.","commented on your post.","followed you.", "created a post.");
    
    $sql = "INSERT INTO nham_notification(actioner_id, user_id, object_id, action_id, description)
      SELECT following_id, follower_id, ?, ?, ? FROM nham_user_follow where following_id = ?";
    
    $param["object_id"] = $request["object_id"];
    $param["action_id"] = $request["action_id"];
    $param["description"] = $des[$request["action_id"]];  
    $param["following_id"] = $request["user_id"];   
    
    $query = $this->db->query($sql , $param);     
        $inserted_id = $this->db->insert_id();    
    return $inserted_id;
  }
  
  
  function listPostByID( $request ){
  
    $sql = "SELECT
          p.post_id,
          p.post_caption,
          p.post_created_date,
          p.shop_id,
          s.shop_name_en,
          s.shop_name_kh,
          s.shop_address,
          s.shop_status,
          p.user_id,
          u.user_fullname,
          u.user_photo,
          u.user_status,
          p.post_count_view,
          p.post_count_share
                    
        FROM nham_user_post p
        LEFT JOIN nham_shop s ON p.shop_id = s.shop_id
        LEFT JOIN nham_user u ON p.user_id = u.user_id
        WHERE p.post_status = 1 and p.post_id = ?";
        
    $param["post_id"] = $request["post_id"];
    $query = $this->db->query($sql, $param);    
    $response["response_data"] = $query->result();
    return $response;
  }
  
  function removeNotification( $request ){
    $sql = "DELETE from nham_notification where actioner_id = ? and object_id = ? and action_id = ?";
    $param["actioner_id"] = $request["user_id"];
    $param["object_id"] = $request["post_id"];
    $param["action_id"] = $request["action_id"];
    
    $query = $this->db->query($sql , $param);
    return $query;
  }
  
  function getDeviceNotification($request){
    $sql = "SELECT distinct ul.token_id, ul.os_type FROM nham_user_log ul WHERE ul.user_id in 
      (SELECT p.user_id from nham_user_post p where p.post_id = ?)
      order by ul.id DESC limit 1";
    
    $param["post_id"] = $request["post_id"];
    $query = $this->db->query($sql , $param);
    return $query->row();
  }
  
}

?>