<?php 
class PostModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function userPost( $request ){
	
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
		
		return ($this->db->affected_rows() != 1) ? false : true;
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
		$sql = "INSERT INTO nham_user_like(user_id,post_id) VALUES(?, ?)";
		$param["user_id"] = $request["user_id"];
		$param["post_id"] = $request["post_id"];
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
		$sql = "SELECT count(*) as count FROM nham_user_like where post_id = ?";
		$param["post_id"] = $request["post_id"];
		$query = $this->db->query($sql , $param);
		return $query->row();
	}
	
	function viewLikers( $request ){
	$sql = "SELECT user_id, 
		user_fullname, 
		user_photo, 
		user_quote FROM nham_user WHERE user_id in (SELECT user_id FROM nham_user_like WHERE post_id = ?)" ;
		$param["post_id"] = $request["post_id"];
		$query = $this->db->query($sql , $param);
		return $query->result();
	}
	
}

?>