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
				WHERE p.post_status = 1
				ORDER BY p.post_id DESC ";
		
		$query_record = $this->db->query($sql);
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
	
	
	
}

?>