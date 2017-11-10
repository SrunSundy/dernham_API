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
					s.shop_id,
					s.shop_name_en,
					s.shop_name_kh,
                    s.shop_logo
					
				FROM nham_product pro 
                LEFT JOIN nham_shop s ON pro.shop_id = s.shop_id ";
		
		$this->load->helper('validate');
		if( isset($request["serve_category_id"]) && validateNumeric($request["serve_category_id"]) ){
			$sql .="\n LEFT JOIN nham_serve_cate_map_shop cate  ON cate.shop_id = s.shop_id ";
			$sql .="\n WHERE pro.pro_status = 1 AND s.shop_status = 1 ";
			$sql .= "\n AND cate.serve_category_id = ? ";
			array_push($param, (int)$request["serve_category_id"]);
		}else{
			$sql .="\n WHERE pro.pro_status = 1 AND s.shop_status = 1 ";
			
		}
		
		if( isset($request["country_id"]) && validateNumeric($request["country_id"]) ){
			$sql .= "\n AND s.country_id = ? ";
			array_push($param, (int)$request["country_id"]);
		}
		
		if( isset($request["city_id"]) && validateNumeric($request["city_id"]) ){
			$sql .= "\n AND s.country_id = ? ";
			array_push($param, (int)$request["city_id"]);
		}
		
		if( isset($request["district_id"]) && validateNumeric($request["district_id"]) ){
			$sql .= "\n AND s.district_id = ? ";
			array_push($param, (int)$request["district_id"]);
		}
		
		if( isset($request["commune_id"]) && validateNumeric($request["commune_id"]) ){
			$sql .= "\n AND s.commune_id = ? ";
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
	
	function listSearchProduct( $request ){
	    
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
	    
	    $order_type = "  p.pro_id DESC ";
	    
	    $param = array();
	    
	    $sql = "SELECT 
                	p.pro_id,
                	p.pro_name_en,
                	p.pro_name_kh,
                	p.pro_image,
                	p.pro_short_description,
                	s.shop_id,
                	s.shop_name_en,
                	s.shop_name_kh,
                  SQRT(POW(69.1 * (s.shop_lat_point - ? ), 2) +
                			 POW(69.1 * (? - s.shop_lng_point) * COS(s.shop_lat_point / 57.3), 2))*1.61 AS distance
                FROM nham_product p
                LEFT JOIN nham_shop s ON p.shop_id = s.shop_id";
	    
	    $this->load->helper('validate');
	    
	    if( isset($request["serve_category_id"]) && validateNumeric($request["serve_category_id"]) ){
	        $sql .="\n LEFT JOIN nham_serve_cate_map_pro pro  ON p.pro_id = pro.pro_id ";
	        $sql .="\n WHERE p.pro_status = 1 AND s.shop_status = 1 ";
	        $sql .= "\n AND pro.serve_category_id = ? ";
	        array_push($param, $current_lat , $current_lng, (int)$request["serve_category_id"]);
	    }else{
	        $sql .="\n WHERE p.pro_status = 1 AND s.shop_status = 1 ";
	        array_push($param, $current_lat , $current_lng);
	    }
	    
	    if( isset($request["country_id"]) && validateNumeric($request["country_id"]) ){
	        $sql .= "\n AND s.country_id = ? ";
	        array_push($param, (int)$request["country_id"]);
	    }
	    
	    if( isset($request["city_id"]) && validateNumeric($request["city_id"]) ){
	        $sql .= "\n AND s.country_id = ? ";
	        array_push($param, (int)$request["city_id"]);
	    }
	    
	    if( isset($request["district_id"]) && validateNumeric($request["district_id"]) ){
	        $sql .= "\n AND s.district_id = ? ";
	        array_push($param, (int)$request["district_id"]);
	    }
	    
	    if( isset($request["commune_id"]) && validateNumeric($request["commune_id"]) ){
	        $sql .= "\n AND s.commune_id = ? ";
	        array_push($param, (int)$request["commune_id"]);
	    }
	    
	    $sql .= "\n AND REPLACE(CONCAT_WS(p.pro_name_en, p.pro_name_kh, p.pro_serve_type ,s.shop_name_en,s.shop_name_kh, s.shop_address),' ','') LIKE REPLACE(?,' ','') ";
	    array_push($param ,"%".$request["srch_text"]."%");
	    	    
	    if(isset($request["is_best_match"]) && $request["is_best_match"] == true){
	        $order_type = " CASE  WHEN REPLACE(CONCAT_WS(p.pro_name_en, p.pro_name_kh, p.pro_serve_type ,s.shop_name_en,s.shop_name_kh, s.shop_address),' ','') = REPLACE('".$request["srch_text"]."',' ','') THEN 0
					              WHEN REPLACE(CONCAT_WS(p.pro_name_en, p.pro_name_kh, p.pro_serve_type ,s.shop_name_en,s.shop_name_kh, s.shop_address),' ','') LIKE REPLACE('".$request["srch_text"]."%',' ','') THEN 1
					              WHEN REPLACE(CONCAT_WS(p.pro_name_en, p.pro_name_kh, p.pro_serve_type ,s.shop_name_en,s.shop_name_kh, s.shop_address),' ','') LIKE REPLACE('%".$request["srch_text"]."%',' ','') THEN 2
					              WHEN REPLACE(CONCAT_WS(p.pro_name_en, p.pro_name_kh, p.pro_serve_type ,s.shop_name_en,s.shop_name_kh, s.shop_address),' ','') LIKE REPLACE('%".$request["srch_text"]."',' ','') THEN 3
					              ELSE 4
					         END, CONCAT_WS(p.pro_name_en, p.pro_name_kh, p.pro_serve_type ,s.shop_name_en,s.shop_name_kh, s.shop_address)  ";
	        //array_push($param ,$request["srch_text"], $request["srch_text"]."%"  ,"%".$request["srch_text"]."%", "%".$request["srch_text"]);
	    }
	    
	    if(isset($request["is_latest"]) && $request["is_latest"] == true){
	        $order_type = " p.pro_id DESC ";
	    }
	    
	    if( isset($request["is_popular"]) && $request["is_popular"] == true ){
	        $order_type = " p.pro_view_count ";
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
	
	function listProductByShopid( $request ){
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		$shop_id = (int)$request["shop_id"];
		$is_pop = $request["is_popular"];
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$params = array();
		
		$sql = "SELECT 
					pro.pro_id,
					pro.pro_image,
					pro.pro_name_en,
					pro.pro_name_kh,
					pro.pro_price,
					pro.pro_promote_price,
					pro.pro_description
				FROM nham_product pro
				WHERE pro.pro_status = 1
				AND pro.shop_id = ? ";
		
		array_push($params, $shop_id);
		if($is_pop){
			$sql .= " AND pro.pro_local_popularity = 1 ";									
		}
		
		$query_record = $this->db->query($sql , $params);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .=" ORDER BY pro.pro_local_popularity DESC, pro.pro_view_count
				  LIMIT ? OFFSET ? ";
		array_push($params, $limit, $offset);
		
		$query = $this->db->query($sql , $params);
		
		$response["response_data"] = $query->result();
		return $response;
	}
	
	function listPopularProduct( $request ){
	    
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
                	p.pro_id,
                	p.pro_image,
                	p.pro_name_en,
                	p.pro_name_kh,
                	p.pro_short_description,
                	s.shop_id,
                	s.shop_name_en,
                	s.shop_name_kh,
                	SQRT(POW(69.1 * (s.shop_lat_point - ? ), 2) +
                        POW(69.1 * (? - s.shop_lng_point) * COS(s.shop_lat_point / 57.3), 2))*1.61 AS distance
                FROM nham_product p
                LEFT JOIN nham_shop s ON p.shop_id = s.shop_id
                ORDER BY p.pro_dis_order ,p.pro_view_count DESC, p.pro_local_popularity DESC ";
	    
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
	
	function getTotalProduct($request){
		
		$params = array();
		$sql = "SELECT count(*) as total_record FROM nham_product pro WHERE pro.pro_status = ? ";
		array_push($params, 1);
		
		$this->load->helper('validate');
		if(isset($request["shop_id"]) || !IsNullOrEmptyString($request["shop_id"])){
			$sql .= " AND pro.shop_id= ? ";
			array_push($params, $request["shop_id"]);
		}
		
		$query = $this->db->query($sql, $params);
		$response = $query->row();
		
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
	
	function getProductDetail( $request ){
		$sql = "SELECT p.pro_name_en, 
                    p.pro_name_kh, 
                    p.pro_price, 
                    p.pro_promote_price, 
                    p.pro_image, 
                    s.shop_id ,
                    s.shop_name_en, 
                    s.shop_name_kh, 
                    s.shop_logo,
                    s.shop_working_day, 
		            s.shop_opening_time,
                    s.shop_close_time, 
                    s.shop_lat_point, 
                    s.shop_time_zone,
                    s.shop_lng_point from nham_product p 
		LEFT JOIN nham_shop s ON p.shop_id = s.shop_id 
		where p.pro_id = ? and p.pro_status = 1 and s.shop_status = 1 limit 1";
				
		$param["product_id"] = $request["product_id"];
				
		$query = $this->db->query($sql, $param);
		$response = $query->result();
		return $response;
	}
	
}
?>