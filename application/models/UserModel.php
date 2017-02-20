<?php
class UserModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function registerUser($request){
	
		$sql = "INSERT INTO nham_user(user_fullname, user_email, user_password) VALUES(?, ?, ?)";
		$param["fullname"] = $request["fullname"];
		$param["email"] = $request["email"];
		$param["password"] = $request["password"];
		
		$query = $this->db->query($sql , $param);
		
		return ($this->db->affected_rows() != 1) ? false : true;
		
	}
	
	
}
?>