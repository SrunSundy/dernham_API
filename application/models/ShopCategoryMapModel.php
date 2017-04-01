<?php
class ShopCategoryMapModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	
	function insertShopServeCategory($request){
		
		$servecategories = array();
		$shopdata["serve_categories"] = array_unique($request["serve_categories"]);
		for($i=0; $i< count($shopdata["serve_categories"]); $i++){
		
			$cateitem["serve_category_id"] = $shopdata["serve_categories"][$i];
			$cateitem["shop_id"] = $request["shop_id"];
			array_push($servecategories , $cateitem);
		}
		return $this->db->insert_batch('nham_serve_cate_map_shop', $servecategories);
	}
	
}
?>
