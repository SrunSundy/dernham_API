<?php
class UserModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function registerUser($request){
	
		$sql = "INSERT INTO nham_user(user_fullname, user_email, user_password, user_verification_code, fbid, type) VALUES(?, ?, ?, ?, ?, ?)";
		$param["fullname"] = $request["fullname"];
		$param["email"] = $request["email"];
		$param["password"] = $request["password"];
		$param["verification_code"] = $request["verification_code"];
		$param["fbid"] ="";
		$param["type"] =0;
		
		$query = $this->db->query($sql , $param);
		
		return ($this->db->affected_rows() != 1) ? false : true;
		
	}
	
	function loginUser($request){
		
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
				WHERE u.user_email = ?
				AND u.user_password = ? LIMIT 1";
		
		$param["email"] = $request["email"];
		$param["password"] = $request["password"];
		$query = $this->db->query($sql , $param);
		return $query->row();
	}
	
	function checkIfUserexist($request){
		
		$sql = "SELECT u.user_status
					FROM nham_user u
					WHERE u.user_email = ? LIMIT 1";
		
		$param["email"] = $request["email"];
		
		$query = $this->db->query($sql , $param);		
		return $query->row();
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
		$sql = "INSERT INTO nham_user(user_fullname, user_email, user_gender, user_password, user_verification_code, type, fbid) VALUES(?, ?, ?, ?, ?, ?, ?)";
		$param["fullname"] = $request["fullname"];
		$param["email"] = ($request["email"] != "") ? $request["email"] : "N/A";
		$param["gender"] = ($request["gender"] != "") ? $request["gender"] : "N/A";
		$param["password"] = "N/A";
		$param["verification_code"] = "N/A";
		$param["type"] = 1; //0 normal login & 1 login as fb
		$param["fbid"] = $request["fbid"];
		
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
		$sql = "SELECT count(*) as is_followed
				FROM nham_user_follow u
				WHERE u.follower_id = ? and u.following_id = ? LIMIT 1";
		
		$param["user_id"] = $request["user_id"];
		$param["profile_id"] = $request["profile_id"];
		$query = $this->db->query($sql , $param);
		return ($query->row()->is_followed) == 0 ? false : true ;
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
	
		$sql = "INSERT INTO nham_user_follow(follower_id, following_id) VALUES(?, ?)";
		$param["user_id"] = $request["user_id"];
		$param["profile_id"] = $request["profile_id"];
		
		$query = $this->db->query($sql , $param);
		return ($this->db->affected_rows() != 1) ? false : true;	
	}
	
	function getNumberFollower($request){
		$sql = "SELECT count(*) as count
				FROM nham_user_follow u
				WHERE u.following_id = ?";
		
		$param["profile_id"] = $request["profile_id"];
		$query = $this->db->query($sql , $param);
		return ($query->row()->count);
	}
	
	function getNumberFollowing($request){
		$sql = "SELECT count(*) as count
				FROM nham_user_follow u
				WHERE u.follower_id = ?";
		
		$param["profile_id"] = $request["profile_id"];
		$query = $this->db->query($sql , $param);
		return ($query->row()->count);
	}
	
	function getNumberPost($request){
		$sql = "SELECT count(*) as count
				FROM nham_user_post u
				WHERE u.user_id = ?";
		
		$param["profile_id"] = $request["profile_id"];
		$query = $this->db->query($sql , $param);
		return ($query->row()->count);
	}
	
	
	
	
}
?>