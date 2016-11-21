<?php
class ServeCategoryModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	
	function listServeCategory(){
		
		$sql = "SELECT serve_category_id,serve_category_icon,serve_category_name from nham_serve_category
				WHERE serve_category_status = 1
				ORDER BY serve_category_type DESC";
		$query = $this->db->query($sql);
		$response = $query->result();
		return $response;
		
	}
	
}
?>