<?php
class ShopImageModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	

	public function listShopDetailImgByShopid( $request ){

		//$shop_id , $limit , $img_type, $is_front_show, $has_defined
		
		
		$param = array();
		$sql = "SELECT 
					img.sh_img_id,
					img.sh_img_name,
					img.sh_img_created_date
				FROM nham_shop_image img
				WHERE img.sh_img_status = 1
				AND img.sh_img_type = ?
				AND img.shop_id = ? ";
		
		
		array_push($param, (int)$request["img_type"] , (int)$request["shop_id"]);
		
		$this->load->helper('yesnoimagefrontshow');
		if(isset($request["is_front_show"]) && $request["is_front_show"] == yesnoimagefrontshow::YES ){ 
			
			$sql .=" AND img.sh_img_is_front_show = ? "; 			
			array_push($param, yesnoimagefrontshow::YES);
		}
		
		if(isset($request["has_defined"])){
			$sql .=" AND img.sh_img_id <> ? ";
			array_push($param, (int)$request["has_defined"]);			
		}
		
		$sql .=	" ORDER BY img.sh_img_dis_order  ";
		if(isset($request["limit"])){
						
			$sql .= " LIMIT ? ";
			array_push($param, (int)$request["limit"]);
			if(isset($request["offset"])){
				$sql .= " OFFSET ? ";
				array_push($param, (int)$request["offset"]);
			}
		}
				
		$query = $this->db->query($sql , $param);
		$response = $query->result();
		
		return $response;
		
	}
	
	public function countListShopDetailImgByShopid( $request ){
		
		$sql = "SELECT count(*) as total_record,
					CASE WHEN count(*)% ? != 0 THEN count(*)/ ? +1 ELSE count(*)/ ? END as total_page 
				FROM nham_shop_image
				WHERE sh_img_status = 1
				AND sh_img_type = ?
				AND shop_id = ?";
		$query = $this->db->query($sql, array($request["limit"]
				, $request["limit"]
				, $request["limit"]
				, (int)$request["img_type"] 
				, (int)$request["shop_id"]) );
		$response = $query->row();
		
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