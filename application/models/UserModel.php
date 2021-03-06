<?php
class UserModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function registerUser($request){
	
	    $current_time = new DateTime();
	    $current_time = $current_time->format('Y-m-d H:i:s');
	    
		$sql = "INSERT INTO nham_user(user_fullname, user_email, user_password, user_verification_code, fbid, type, created_date) VALUES( ? , ?, ?, ?, ?, ?, ?)";
		$param["fullname"] = $request["fullname"];
		$param["email"] = $request["email"];
		$param["password"] = $request["password"];
		$param["verification_code"] = $request["verification_code"];
		$param["fbid"] = "";
		$param["type"] = 0;
		$param["created_date"] = $current_time;
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	
	function updateUserVerificationCode($request){
		$sql = " UPDATE nham_user SET user_verification_code = ?
					 WHERE user_id = ? ";
		
		$param["verification_code"] = $request["verification_code"];
		$param["user_id"] = $request["user_id"];			
		$query = $this->db->query($sql , $param);
		return $query;
	}
	
	function loginUser($request){
		
		$sql = "SELECT u.user_id,
					u.user_email,
					u.user_fullname,
					u.user_gender,
					u.user_photo,
					u.user_quote,
					u.user_interest,
					u.fbid,
					u.type,
					u.user_phone,
					u.user_status
				FROM nham_user u
				WHERE u.user_email = ?
				AND u.user_password = ? LIMIT 1";
		
		$param["email"] = $request["email"];
		$param["password"] = $request["password"];
		$query = $this->db->query($sql , $param);
		return $query->row();
	}
	
	function checkIfUserexist($request){
		
		$sql = "SELECT u.user_id ,u.user_status
					FROM nham_user u
					WHERE u.user_email = ? LIMIT 1";
		
		$param["email"] = $request["email"];
		
		$query = $this->db->query($sql , $param);		
		return $query->row();
	}
	
	function getUserToVerify($request){
		
		$sql = " SELECT user_status 
					 FROM nham_user
					 WHERE user_email = ?
					 AND user_verification_code = ? ";
		
		$param["email"] = $request["email"];
		$param["v_code"] = $request["v_code"];
		
		$query = $this->db->query($sql , $param);
		return $query->result();
	}
	
	function verifyUser($request){
		
		$sql = " UPDATE nham_user SET user_status = ?
					 WHERE user_email = ?
					 AND user_verification_code = ? ";
		
		$this->load->helper('userstatus');
		$param["user_status"] = userstatus::Active;
		$param["email"] = $request["email"];
		$param["v_code"] = $request["v_code"];
		
		$query = $this->db->query($sql , $param);
		return $query;
	}
	
	
	
	function checkIfFBUserExist($request){
		$sql = "SELECT 	u.user_id,
					u.user_email,
					u.user_fullname,
					u.user_gender,
					u.user_photo,
					u.user_quote,
					u.user_interest,
					u.user_phone,
					u.user_status
					FROM nham_user u
					WHERE u.type = 1 and u.fbid=? LIMIT 1";
		
		$param["fbid"] = $request["fbid"];
		$query = $this->db->query($sql , $param);		
		return $query->row();
	}
	
	function getUserByUserId($request){
		$sql = "SELECT 	u.user_id,
					u.user_email,
					u.user_fullname,
					u.user_gender,
					u.user_photo,
					u.user_quote,
					u.user_interest,
					u.user_phone,
					u.user_status
					FROM nham_user u
					WHERE u.user_id = ? LIMIT 1";
		$param["user_id"] = $request["user_id"];
		$query = $this->db->query($sql , $param);
		return $query->row();
	}
	
	function registerFBUser($request){
	    
	    $current_time = new DateTime();
	    $current_time = $current_time->format('Y-m-d H:i:s');
	    
		$sql = "INSERT INTO nham_user(user_fullname, user_email, user_gender, user_password, user_verification_code, type, fbid, user_photo, created_date) VALUES(? , ?, ?, ?, ?, ?, ?, ?, ?)";
		$param["fullname"] = $request["fullname"];
		$param["email"] = ($request["email"] != "") ? $request["email"] : "N/A";
		$param["gender"] = ($request["gender"] != "") ? $request["gender"] : "N/A";
		$param["password"] = "N/A";
		$param["verification_code"] = "N/A";
		$param["type"] = 1; //0 normal login & 1 login as fb
		$param["fbid"] = $request["fbid"];
		$param["user_photo"] = $request["fbid"].'.jpg';
		$param["created_date"] = $current_time;
		
		$query = $this->db->query($sql , $param);
		if($this->db->affected_rows() != 1)
			return 0;
		$req["user_id"]=$this->db->insert_id();
		return $this->UserModel->getUserByUserId($req);
	}
	
	function updateUserProfile($request){
		$sql = "UPDATE nham_user set user_fullname = ?, user_email = ?, user_gender = ?, user_quote = ?, user_phone = ? where user_id = ?";
		$param["fullname"] = isset($request["fullname"]) ? $request["fullname"] : "";
		$param["email"] = isset($request["email"]) ? $request["email"] : "";
		$param["gender"] = isset($request["gender"]) ? $request["gender"] : "";
		$param["quote"] = isset($request["quote"]) ? $request["quote"] : "";
		$param["phone"] = isset($request["phone"]) ? $request["phone"] : "";
		$param["profile_id"] = $request["profile_id"];
		
		$query = $this->db->query($sql , $param);
		
		return ($query);
	}
	
	function getUserProfile($request){
		$sql = "SELECT u.user_id,
					u.user_email,
					u.user_fullname,
					u.user_gender,
					u.user_photo,
					u.user_quote,
					u.user_interest,
					u.user_phone,
					u.user_status
				FROM nham_user u
				WHERE u.user_id = ? LIMIT 1";
		
		$param["profile_id"] = $request["profile_id"];
		$query = $this->db->query($sql , $param);
		return $query->row();
	}
	
	function getUserProfileIsFollowed($request){
		$sql = "SELECT count(*) as count
				FROM nham_user_follow u
				WHERE u.follower_id = ? and u.following_id = ? LIMIT 1";
		
		$param["user_id"] = $request["user_id"];
		$param["profile_id"] = $request["profile_id"];
		$query = $this->db->query($sql , $param);
		
		return (($query->row()->count) == 0 ? 0 : 1);//($query->row()->is_followed) == 0 ? false : true ;
	}
	
	function updateUserProfileData($request){
		
		$u_param = $request["update_param"];
		$u_value = $request["update_value"];
		$sql = " UPDATE nham_user SET ". $u_param . " = ? WHERE user_id = ? ";
		
		$param["value"] = $u_value;
		$param["user_id"] = $request["user_id"];
		$query = $this->db->query($sql , $param);
		
		return ($query);
	}
	
	function reqUserUnfollow($request){
		$sql = "DELETE from nham_user_follow where follower_id = ? and following_id = ?";
		$param["user_id"] = $request["user_id"];
		$param["profile_id"] = $request["profile_id"];
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;
		
	}
	
	function reqUserFollow($request){
	    
	    $current_time = new DateTime();
	    $current_time = $current_time->format('Y-m-d H:i:s');
	
		$sql = "INSERT INTO nham_user_follow(follower_id, following_id, created_date ) SELECT ?, ?, ? FROM dual
				WHERE (
					SELECT count(*) from nham_user_follow
					WHERE follower_id = ? AND following_id = ?
				) < 1 ";
		$param["user_id"] = $request["user_id"];
		$param["profile_id"] = $request["profile_id"];
		$param["user_id_1"] = $request["user_id"];
		$param["profile_id_1"] = $request["profile_id"];
		$param["created_date"] = $current_time;
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;	
	}
	
	function getNumberFollower($request){
		$sql = "SELECT count(follower_id) as count
				FROM nham_user_follow u
				WHERE u.following_id = ?";
		
		$param["profile_id"] = $request["profile_id"];
		$query = $this->db->query($sql , $param);
		return ($query->row()->count);
	}
	
	function getNumberFollowing($request){
		$sql = "SELECT count(following_id) as count
				FROM nham_user_follow u
				WHERE u.follower_id = ?";
		
		$param["profile_id"] = $request["profile_id"];
		$query = $this->db->query($sql , $param);
		return ($query->row()->count);
	}
	
	function getFollowers($request){
		$sql = "SELECT user_id, user_fullname, user_photo, user_quote from nham_user 
		where user_id in (SELECT distinct follower_id FROM nham_user_follow WHERE following_id = ?)";
		
		$param["user_id"] = $request["user_id"];
		$query = $this->db->query($sql , $param);
		$response["response_data"] = $query->result();
		return $response;
	}
	
	function getFollowing($request){
		$sql = "SELECT user_id, user_fullname, user_photo, user_quote from nham_user 
		where user_id in (SELECT distinct following_id FROM nham_user_follow WHERE follower_id = ?)";
		
		$param["user_id"] = $request["user_id"];
		$query = $this->db->query($sql , $param);
		$response["response_data"] = $query->result();
		return $response;
	}
	
	function getNumberPost($request){
		$sql = "SELECT count(*) as count
				FROM nham_user_post u
				WHERE u.user_id = ? and u.post_status = 1";
		
		$param["profile_id"] = $request["profile_id"];
		$query = $this->db->query($sql , $param);
		return ($query->row()->count);
	}
	
	function reportShop($request){
	
	    $current_time = new DateTime();
	    $current_time = $current_time->format('Y-m-d H:i:s');
	    
		$sql = "INSERT INTO nham_user_report(user_id, object_id, reported_type, created_date) VALUES(?, ?, ?, ?)";
		
		$param["user_id"] = $request["user_id"];
		$param["object_id"] = $request["object_id"];
		$param["reported_type"] = $request["reported_type"];
		$param["created_date"] = $current_time;
		
		$query = $this->db->query($sql , $param);
		
		return ($this->db->affected_rows() != 1) ? false : true;
		
	}
	
	function reportPost($request){	
	    
	    $current_time = new DateTime();
	    $current_time = $current_time->format('Y-m-d H:i:s');
	    
		$sql = "INSERT INTO nham_report_post(post_id, user_id, report_description, created_date) VALUES(?, ?, ?, ?)";
		$param["post_id"] = $request["object_id"];
		$param["user_id"] = $request["user_id"];
		$param["report_description"] = "Inappropriate content";
		$param["created_date"] = $current_time;
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	
	function unreportPost($request){
		$sql = "DELETE from nham_report_post where user_id = ? and post_id = ?";
		$param["user_id"] = $request["user_id"];
		$param["post_id"] = $request["object_id"];
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;
		
	}
	
	function savePost($request){
	
		$sql = "INSERT INTO nham_saved_post( object_id, user_id, saved_type ,created_date )
				SELECT 
					?,
					?,
					?,
					?
				FROM dual
				WHERE 
				( SELECT count(*) FROM nham_saved_post WHERE object_id = ? AND user_id = ? ) < 1 ";
		$param["object_id"] = $request["object_id"];
		$param["user_id"] = $request["user_id"];
		$param["saved_type"] = "post";
		
		$current_time = new DateTime();
		$current_time = $current_time->format('Y-m-d H:i:s');
		$param["created_date"] = $current_time;
		$param["object_id_1"] = $request["object_id"];
		$param["user_id_1"] = $request["user_id"];
		
		$query = $this->db->query($sql , $param);
		
		return $query;
		
	}
	
	function unsavedPost($request){
		$sql = "DELETE from nham_saved_post where user_id = ? and object_id = ? and saved_type = ?";
		$param["user_id"] = $request["user_id"];
		$param["object_id"] = $request["object_id"];
		$param["saved_type"] = $request["saved_type"];
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;
		
	}
	
	function deletePost($request){
	
		$sql = "UPDATE nham_user_post set post_status = 0 WHERE post_id = ?";
		$param["post_id"] = $request["post_id"];
		
		$query = $this->db->query($sql , $param);
		
		return ($query);
		
	}
	
	function listTopMembers( $request ){
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$param = array();
		$sql = "SELECT 
					a.*,
					count(up.user_id) AS post_cnt
				FROM 
					(SELECT u.user_id,
					 u.user_fullname,
					 u.user_photo,
					 u.user_quote,
					 count(uf.follower_id) AS follower_cnt
					FROM nham_user u
					LEFT JOIN nham_user_follow uf ON u.user_id = uf.following_id
					WHERE u.user_status = 1
					GROUP BY u.user_id) a
				LEFT JOIN nham_user_post up ON a.user_id = up.user_id
				WHERE up.post_status = 1
				GROUP BY up.user_id 
				ORDER BY count(up.user_id)+(a.follower_cnt*3) DESC ";
		
		
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
	
	function updateUserPhotoName( $request ){
		
		if(copy($_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/temp/user/medium/'.$request["temp_photo"], $_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/real/user/medium/'.$request["temp_photo"]) &&
 copy($_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/temp/user/small/'.$request["temp_photo"], $_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/real/user/small/'.$request["temp_photo"]) &&
copy($_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/temp/user/big/'.$request["temp_photo"], $_SERVER['DOCUMENT_ROOT'].'/dernham_API/uploadimages/real/user/big/'.$request["temp_photo"])){
			$sql = "Update nham_user set user_photo = ? where user_id = ?";
			$param["user_photo"] = $request["temp_photo"];
			$param["user_id"] = $request["user_id"];
			$query = $this->db->query($sql , $param);
			return ($this->db->affected_rows() != 1) ? false : true;
		}
		
		return false;
	}
	
	function updateUserPassword($request){
	
		$sql = " UPDATE nham_user SET user_password = ? WHERE user_id = ? and user_password = ? ";
		
		
		$param["new_pwd"] = $request["new_pwd"];
		$param["user_id"] = $request["user_id"];
		$param["old_pwd"] = $request["old_pwd"];
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	
	function insertDeviceToken($request){
	
		$sql = "INSERT INTO nham_device_token(token_id, user_id, type) VALUES(?, ?, ?)";
		$param["token_id"] = $request["token_id"];
		$param["user_id"] = $request["user_id"];
		$param["type"] = $request["type"];
		
		$query = $this->db->query($sql , $param);
		
		return ($this->db->affected_rows() != 1) ? false : true;
		
	}
	
	
	function getUserNotification( $request ){
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$param = array();
		
		
		$sql = "select n.id, n.actioner_id, n.user_id, n.object_id, n.action_id, n.description, n.created_date, 
			u.user_fullname, u.user_photo, pi.post_image_src	
			from nham_notification n 
			left join nham_user u on u.user_id = n.actioner_id
			left join nham_user_post_image pi on pi.post_id = n.object_id
			where (n.user_id = ". $request["user_id"] .") or
			(n.object_id in (select p.post_id from nham_user_post p where p.user_id = ". $request["user_id"] ."))
            AND actioner_id <> ".$request["user_id"]."
			order by n.created_date DESC";
		
		
		$query_record = $this->db->query($sql);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= " LIMIT ? OFFSET ? ";
		array_push($param, $limit , $offset);
		$query = $this->db->query($sql, $param);
		
		$response["response_data"] = $query->result();
		return $response;
	}
	
	function userForgetPassword($request){
	
		$sql = "SELECT user_email, user_password from nham_user where user_email = ? and user_status = 1 limit 1";
		$param["email"] = $request["email"];
		$query = $this->db->query($sql , $param);		
		return $query->row();
	}
	
	function insertUserLog($request){
	    
	    $current_time = new DateTime();
	    $current_time = $current_time->format('Y-m-d H:i:s');
		$sql = "INSERT INTO nham_user_log(user_id, action_type, token_id, os_type, model, created_date) VALUES(?,?,?,?,?,?)";
		
		$param["user_id"] = $request["user_id"];
		$param["action_type"] = $request["action_type"];
		$param["token_id"] = $request["token_id"];
		$param["os_type"] = $request["os_type"];
		$param["model"] = $request["model"];
		$param["created_date"] = $current_time;
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	
	
	function userFeedback($request){
	    
	    $current_time = new DateTime();
	    $current_time = $current_time->format('Y-m-d H:i:s');
		$sql = "INSERT INTO nham_user_feedback(user_id, image_src, description, created_date) VALUES(?,?,?,?)";
		
		$param["user_id"] = $request["user_id"];
		$param["image_src"] = $request["image_src"];
		$param["description"] = $request["description"];
		$param["created_date"] = $current_time;
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	
	function updateReadNotification($request){
		
		$param["already_read"] = $request["already_read"];
		$param["user_id1"] = $request["user_id"];
		$param["user_id2"] = $request["user_id"];
		$param["last_notify_id"] = $request["last_notify_id"];
		
		$sql = "update nham_notification n set already_read = ? 
			where (n.object_id in (select p.post_id from nham_user_post p where p.user_id = ? and p.post_status = 1) or n.user_id = ?) 
			and (n.already_read = 0 and n.id <= ?)";
			
		$query = $this->db->query($sql, $param);
		return $query;
	}	
	
	function checkIfNotifyUser($request){
		$sql = "SELECT count(*) count FROM nham_user_post where post_id = ? and user_id = ? limit 1";
		
		$param["post_id"] = $request["object_id"];
		$param["user_id"] = $request["actioner_id"];
		
		$query = $this->db->query($sql , $param);
		return (($query->row()->count) == 0 ? true : false);
	}
	
}
?>