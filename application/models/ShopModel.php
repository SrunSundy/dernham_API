<?php

class ShopModel extends CI_Model{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	
	public function listShop($request){
		
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
		$order_type = " sh.shop_id DESC ";
			
		$param = array();
		$sql = "SELECT sh.shop_id,
						TRIM(COALESCE(sh.shop_logo,'')) shop_logo,
						sh.shop_cover,
						sh.shop_name_en,
						sh.shop_name_kh,
						sh.shop_address,
						sh.shop_time_zone,
						sh.shop_opening_time,
						sh.shop_close_time,
						sh.shop_has_detail_img,
						SQRT(
						POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * ( ? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh		
				LEFT JOIN nham_country cou ON cou.country_id = sh.country_id
				LEFT JOIN nham_city city ON city.city_id = sh.city_id
				LEFT JOIN nham_district dis ON dis.district_id = sh.district_id
				LEFT JOIN nham_commune com ON com.commune_id = sh.commune_id ";

		$this->load->helper('validate');
		if( isset($request["serve_category_id"]) && validateNumeric($request["serve_category_id"]) ){
			$sql .="\n LEFT JOIN nham_serve_cate_map_shop cate  ON cate.shop_id = sh.shop_id ";
		}	
			
		$sql .="\n WHERE sh.shop_status = 1 ";		
		array_push($param, $current_lat , $current_lng);
		
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
		
		if( isset($request["serve_category_id"]) && validateNumeric($request["serve_category_id"]) ){
			$sql .= "\n AND cate.serve_category_id = ? ";
			array_push($param, (int)$request["serve_category_id"]);
		}
		
		if( isset($request["is_popular"]) && $request["is_popular"] == true ){
			$order_type = " sh.shop_dis_order asc,sh.shop_view_count desc ";
		}
		
		if( isset($request["is_nearby"]) && $request["is_nearby"] == true ){			
			$order_type = " distance ";
			
			if(isset($request["nearby_value"]) && validateNumeric($request["nearby_value"]) ){
				$nearby_value = (int)$request["nearby_value"];	
				$sql .= "\n HAVING distance < ? ";
				array_push($param, $nearby_value );
			}							
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
	
	public function listNearbyShop( $request ){
		
		$current_lat = (float)$request["current_lat"];
		$current_lng = (float)$request["current_lng"];
		$nearby_value = (float)$request["nearby_value"];
		 
		if(!$current_lat || $current_lat > 90 || $current_lat <-90) $current_lat= 0;
		if(!$current_lng || $current_lng > 180 || $current_lng < -180) $current_lng= 0;
		if(!$nearby_value || $nearby_value <= 0) $nearby_value = 0.5;
		$sql = "SELECT 
					sh.shop_id,
					sh.shop_logo,
					sh.shop_name_en,
					sh.shop_name_kh,
					sh.shop_address,
					sh.shop_time_zone,
					sh.shop_opening_time,
					sh.shop_close_time,
					sh.shop_lat_point,
					sh.shop_lng_point,
					SQRT(POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * (? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh
				WHERE sh.shop_status = 1
				HAVING distance < ? ORDER BY distance";
		$query = $this->db->query($sql , array($current_lat, $current_lng, $nearby_value));
		$response = $query->result();
			
		return $response;
		
	}
	
	public function listPopularShop($request){
		
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
		
		$sql = "SELECT 
					sh.shop_id,
					sh.shop_logo,
					sh.shop_name_en,
					sh.shop_name_kh,
					sh.shop_address,
					sh.shop_time_zone,
					sh.shop_opening_time,
					sh.shop_close_time,
					SQRT(POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * (? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh
				WHERE sh.shop_status = 1
				ORDER by sh.shop_dis_order asc,sh.shop_view_count desc ";
		
		$query_record = $this->db->query($sql ,  array($current_lat, $current_lng));
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
	
		$sql .= " LIMIT ? OFFSET ? ";
		
		$query = $this->db->query($sql , array($current_lat, $current_lng, $limit, $offset));
		$response["response_data"] = $query->result();
			
		return $response;
	}
	
	public function listSearchShop($request){
		
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
		$order_type = "  sh.shop_id DESC ";
			
		$param = array();
		
		$sql = "SELECT 
					sh.shop_id,
					sh.shop_name_en,
					sh.shop_name_kh,
					sh.shop_logo,
					sh.shop_address,
					sh.shop_time_zone,
					sh.shop_opening_time,
					sh.shop_close_time,
					SQRT(POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * (? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh
				LEFT JOIN nham_country cou ON cou.country_id = sh.country_id
				LEFT JOIN nham_city city ON city.city_id = sh.city_id
				LEFT JOIN nham_district dis ON dis.district_id = sh.district_id
				LEFT JOIN nham_commune com ON com.commune_id = sh.commune_id ";
		
		$this->load->helper('validate');
		if( isset($request["serve_category_id"]) && validateNumeric($request["serve_category_id"]) ){
			$sql .="\n LEFT JOIN nham_serve_cate_map_shop cate  ON cate.shop_id = sh.shop_id ";
			$sql .="\n WHERE sh.shop_status = 1 ";
			$sql .= "\n AND cate.serve_category_id = ? ";
			array_push($param, $current_lat , $current_lng, (int)$request["serve_category_id"]);
		}else{
			$sql .="\n WHERE sh.shop_status = 1 ";
			array_push($param, $current_lat , $current_lng);
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
		
		$sql .= "\n AND REPLACE(CONCAT_WS(sh.shop_name_en,sh.shop_name_kh,sh.shop_serve_type,sh.shop_address),' ','') LIKE REPLACE(?,' ','') ";
		array_push($param ,"%".$request["srch_text"]."%");
		
		if(isset($request["is_best_match"]) && $request["is_best_match"] == true){
			$order_type = " CASE  WHEN REPLACE(CONCAT_WS(sh.shop_name_en,sh.shop_name_kh,sh.shop_serve_type,sh.shop_address),' ','') = REPLACE('".$request["srch_text"]."',' ','') THEN 0  
					              WHEN REPLACE(CONCAT_WS(sh.shop_name_en,sh.shop_name_kh,sh.shop_serve_type,sh.shop_address),' ','') LIKE REPLACE('".$request["srch_text"]."%',' ','') THEN 1  
					              WHEN REPLACE(CONCAT_WS(sh.shop_name_en,sh.shop_name_kh,sh.shop_serve_type,sh.shop_address),' ','') LIKE REPLACE('%".$request["srch_text"]."%',' ','') THEN 2  
					              WHEN REPLACE(CONCAT_WS(sh.shop_name_en,sh.shop_name_kh,sh.shop_serve_type,sh.shop_address),' ','') LIKE REPLACE('%".$request["srch_text"]."',' ','') THEN 3  
					              ELSE 4
					         END, CONCAT_WS(sh.shop_name_en,sh.shop_name_kh,sh.shop_serve_type, sh.shop_address)  ";
			//array_push($param ,$request["srch_text"], $request["srch_text"]."%"  ,"%".$request["srch_text"]."%", "%".$request["srch_text"]);
		}
		
		if(isset($request["is_latest"]) && $request["is_latest"] == true){
			$order_type = " sh.shop_id DESC ";
		}
		
		if( isset($request["is_popular"]) && $request["is_popular"] == true ){
			$order_type = " sh.shop_view_count ";
		}
		
		if( isset($request["is_nearby"]) && $request["is_nearby"] == true ){
			$order_type = " distance ";
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
		
	public function getShop( $request ){
		
		$current_lat = (float)$request["current_lat"];
		$current_lng = (float)$request["current_lng"];
		$shop_id = (int)$request["shop_id"];
		
		if(!$current_lat || $current_lat > 90 || $current_lat <-90) $current_lat= 0;
		if(!$current_lng || $current_lng > 180 || $current_lng < -180) $current_lng= 0;
			
		$sql = "SELECT 
					sh.branch_id,
					sh.shop_logo,
					sh.shop_cover,
					sh.shop_name_en,
					sh.shop_name_kh,
					sh.shop_description,
					sh.shop_address,
					sh.shop_phone,
					sh.shop_email,
					sh.shop_working_day,
					sh.shop_opening_time,
					sh.shop_close_time,
					sh.shop_capacity,
					sh.shop_lat_point,
					sh.shop_lng_point,
					sh.shop_view_count,
					sh.shop_social_media,
					sh.shop_time_zone,
					SQRT(
						POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * ( ? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh
				WHERE sh.shop_status = 1
				AND sh.shop_id = ?";
		$query = $this->db->query($sql , array($current_lat, $current_lng, $shop_id));
		$response = $query->row();
			
		return $response;
					
	}
	
	
	public function listShopRelatedBranch( $request ){
		
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
		
		$param = array();
		$sql = "SELECT  
					sh.shop_id,
					sh.shop_name_en,
					sh.shop_name_kh,
					sh.shop_logo,
					sh.shop_address,
					SQRT(
						POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * ( ? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh
				LEFT JOIN nham_branch br ON br.branch_id = sh.branch_id
				WHERE sh.shop_status = 1
				AND br.branch_id = ?
				AND sh.shop_id <> ? ";
		array_push($param ,$current_lat, $current_lng, $request["branch_id"], $request["shop_id"]);
		
		$query_record = $this->db->query($sql , $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= " ORDER BY sh.shop_view_count
				  LIMIT ? OFFSET ? ";
		array_push($param , $limit, $offset);
								
		$query = $this->db->query($sql , $param);
		$response["response_data"] = $query->result();
		
		return $response;
		
	}
	
	
	
	

}

?>