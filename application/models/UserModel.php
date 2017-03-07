<?php
class UserModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function registerUser($request){
	
		$sql = "INSERT INTO nham_user(user_fullname, user_email, user_password, user_verification_code) VALUES(?, ?, ?, ?)";
		$param["fullname"] = $request["fullname"];
		$param["email"] = $request["email"];
		$param["password"] = $request["password"];
		$param["verification_code"] = $request["verification_code"];
		
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
	
	function updateUserProfile( $request){
		
		$param = $request["update_param"];
		$value = $request["update_value"];
		
		$sql = " UPDATE nham_user SET ";
	}
	
	
}
?>