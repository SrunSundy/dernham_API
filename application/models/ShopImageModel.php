<?php
class ShopImageModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	

	public function listShopDetailImgByShopid( $shop_id , $limit , $img_type){
			
		$sql = "SELECT 
					img.sh_img_id,
					img.sh_img_name
				FROM nham_shop_image img
				WHERE img.sh_img_status = 1
				AND img.sh_img_type = ?
				AND img.shop_id = ?
				ORDER BY img.sh_img_dis_order
				LIMIT ?";
		
		$query = $this->db->query($sql , array($img_type ,$shop_id ,$limit));
		$response = $query->result();
		
		return $response;
		
	}
	
	public function getShopDetailImg( $shop_detail_id ){
		
		$sql = "SELECT 
					img.shop_id,
					img.sh_img_name,
					img.sh_img_remark,
					img.sh_img_created_date,
					img.admin_id,
					sh.shop_name_en,
					sh.shop_name_kh,
					sh.shop_logo,
					admin.admin_name,
					admin.admin_photo
			FROM nham_shop_image img
			LEFT JOIN nham_admin admin ON admin.admin_id = img.admin_id
			LEFT JOIN nham_shop sh ON sh.shop_id = img.shop_id
			WHERE img.sh_img_status = 1
			AND img.sh_img_id = ?";
		
		$query = $this->db->query($sql , $shop_detail_id );
		$response = $query->row();
		
		return $response;
	}
	
	
	
}
?>