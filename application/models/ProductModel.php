<?php
class ProductModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function listPopularProByShopid( $shop_id , $limit){
		
		$sql = "SELECT 
					pro.pro_id,
					pro.pro_name_en,
					pro.pro_name_kh,
					pro.pro_image,
					pro_short_description
				FROM nham_product pro
				WHERE pro.pro_status = 1
				AND pro.pro_local_popularity = 1
				AND pro.shop_id = ? 
				ORDER BY pro.pro_view_count
				LIMIT ?";
		$query = $this->db->query($sql, array($shop_id, $limit));
		
		$response = $query->result();
		return $response;
		
	}
	
	function getProAveragePriceByShopid( $shop_id ){
		
		$sql = "SELECT 
					COALESCE(AVG(pro_price),0)  as average_price
				FROM nham_product 
				WHERE pro_status=1
				AND shop_id = ?";
		$query = $this->db->query($sql, $shop_id);
		
		$response = $query->row();
		return $response;
	}
	
}
?>