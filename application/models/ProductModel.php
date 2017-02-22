<?php
class ProductModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function listProduct( $request ){
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		$current_lat = (float)$request["current_lat"];
		$current_lng = (float)$request["current_lng"];
			
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		if(!$current_lat || $current_lat > 90 || $current_lat <-90) $current_lat= 0;
		if(!$current_lng || $current_lng > 180 || $current_lng < -180) $current_lng= 0;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$order_type = " pro.pro_id DESC ";		
		$param = array();
		$sql = "SELECT 
					pro.pro_id,
					pro.pro_name_en,
					pro.pro_name_kh,
					pro.pro_image,
					pro.pro_price,
					pro.pro_promote_price,
					pro.pro_short_description,
					pro.shop_id,
					pro.shop_name_en,
					pro.shop_name_kh
					
				FROM nham_product pro
				LEFT JOIN nham_country cou ON cou.country_id = pro.country_id
				LEFT JOIN nham_city city ON city.city_id = pro.city_id
				LEFT JOIN nham_district dis ON dis.district_id = pro.district_id
				LEFT JOIN nham_commune com ON com.commune_id = pro.commune_id ";
		
		$this->load->helper('validate');
		if( isset($request["serve_category_id"]) && validateNumeric($request["serve_category_id"]) ){
			$sql .="\n LEFT JOIN nham_serve_cate_map_shop cate  ON cate.shop_id = sh.shop_id ";
			$sql .="\n WHERE pro.pro_status = 1 ";
			$sql .= "\n AND cate.serve_category_id = ? ";
			array_push($param, (int)$request["serve_category_id"]);
		}else{
			$sql .="\n WHERE pro.pro_status = 1 ";
			
		}
		
		if( isset($request["country_id"]) && validateNumeric($request["country_id"]) ){
			$sql .= "\n AND cou.country_id = ? ";
			array_push($param, (int)$request["country_id"]);
		}
		
		if( isset($request["city_id"]) && validateNumeric($request["city_id"]) ){
			$sql .= "\n AND city.country_id = ? ";
			array_push($param, (int)$request["city_id"]);
		}
		
		if( isset($request["district_id"]) && validateNumeric($request["district_id"]) ){
			$sql .= "\n AND dis.district_id = ? ";
			array_push($param, (int)$request["district_id"]);
		}
		
		if( isset($request["commune_id"]) && validateNumeric($request["commune_id"]) ){
			$sql .= "\n AND com.commune_id = ? ";
			array_push($param, (int)$request["commune_id"]);
		}
		
		if( isset($request["is_popular"]) && $request["is_popular"] == true ){
			$order_type = " pro.pro_view_count DESC ";
		}
		
		if( isset($request["is_nearby"]) && $request["is_nearby"] == true ){
			$order_type = " SQRT(
						POW(69.1 * (pro.pro_lat_point - ? ), 2) +
						POW(69.1 * ( ? - pro.pro_lng_point) * COS(pro.pro_lat_point / 57.3), 2))*1.61 ";
			array_push($param, $current_lat , $current_lng);
			
		}
		
		$query_record = $this->db->query($sql , $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= "\n ORDER BY ".$order_type;
		$sql .= "\n LIMIT ? OFFSET ? ";
		array_push($param, $limit , $offset);
		
		$query = $this->db->query($sql , $param);
		$response["response_data"] = $query->result();
			
		return $response;
		
	}
	
	function listProductByShopid( $request ){
		
		$sql = "SELECT ";
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