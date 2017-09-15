<?php 
class FacebookModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function loadFacebookThumnail($post_id){
		$sql = "SELECT post_id,post_caption, (SELECT post_image_src FROM nham_user_post_image WHERE post_id=?) as path FROM nham_user_post  	
                        WHERE post_id= ?";
		
	//	$param["post_id"] = $post_id;
		$query = $this->db->query($sql , array($post_id,$post_id));
		return $query->row();
	}
	
}