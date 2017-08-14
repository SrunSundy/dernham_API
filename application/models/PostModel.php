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
				user_id
				) 
			VALUES(?, ?, ?)";
		
		$param["post_caption"] = $request["caption"];
		$param["shop_id"] = $request["shop_id"];
		$param["user_id"] = $request["user_id"];
		
		
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
	
	
	function updateUserPost( $request ){
		
		$sql = "UPDATE nham_user_post SET post_caption=? , shop_id=? WHERE post_id =? ";
		$param["post_caption"] = $request["caption"];
		$param["shop_id"] = $request["shop_id"];
		$param["post_id"] = $request["post_id"];
		
		$query = $this->db->query($sql , $param);
		
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	
	function userLike( $request ){
		$sql = " INSERT INTO nham_user_like(user_id,post_id) 
				SELECT ?, ? FROM dual 
				WHERE (
					SELECT count(*) from nham_user_like
					WHERE user_id = ? AND post_id = ?
				) < 1 ";
		$param["user_id"] = $request["user_id"];
		$param["post_id"] = $request["post_id"];
		$param["user_id_1"] = $request["user_id"];
		$param["post_id_1"] = $request["post_id"];
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	
	
	function userUnlike( $request ){
		$sql = "DELETE from nham_user_like where user_id = ? and post_id = ?";
		$param["user_id"] = $request["user_id"];
		$param["post_id"] = $request["post_id"];
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() < 1) ? false : true;
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
	$sql = "INSERT INTO nham_comment(user_id, post_id, text) values(?, ?, ?)" ;
		$param["user_id"] = $request["user_id"];
		$param["post_id"] = $request["post_id"];
		$param["text"] = $request["text"];
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
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$param = array();
		$sql = "SELECT
					sp.id,
					sp.post_id,
					pi.post_image_src,
					p.user_id,
					u.user_fullname,
                    u.user_photo,
                    sp.created_date
                    										
				FROM nham_saved_post sp
				LEFT JOIN nham_user_post p ON sp.post_id = p.post_id
				LEFT JOIN nham_user u ON p.user_id = u.user_id
				LEFT JOIN nham_shop s ON p.shop_id = s.shop_id
				LEFT JOIN nham_user_post_image pi ON sp.post_id = pi.post_id
				WHERE sp.status = 1 and sp.user_id = ".$user_id." GROUP BY sp.post_id ORDER BY sp.created_date DESC ";
		
		
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
	
	function getUserNotification($request){
		$sql = "SELECT 	u.user_fullname
				FROM nham_user u
				WHERE u.user_id = ? LIMIT 1";
		
		$param["user_id"] = $request["user_id"];
		$query = $this->db->query($sql , $param);
		return $query->row();
	}
	
	function getTokenNotification($request){
		$sql = "SELECT t.token_id FROM nham_device_token t WHERE t.user_id in 
			(SELECT p.user_id from nham_user_post p where p.post_id = ?)";
		
		$param["post_id"] = $request["post_id"];
		$query = $this->db->query($sql , $param);
		return $query->row();
	}
	
	function notifyUser($request){
		$des = array("","liked","commented","followed");
		$sql = "INSERT INTO nham_notification(
				actioner_id, 
				object_id, 
				action_id,
				description
				) 
			VALUES(?, ?, ?, ?)";
		
		$param["actioner_id"] = $request["user_id"];
		$param["object_id"] = $request["object_id"];
		$param["action_id"] = $request["action_id"];
		$param["description"] = $des[$request["action_id"]];
		
		
		$query = $this->db->query($sql , $param);			
	    	$inserted_id = $this->db->insert_id();		
		return $inserted_id;
	}
	
	
	
	
}

?>