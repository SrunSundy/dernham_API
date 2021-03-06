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
		
		$user_id = (isset($request["user_id"])) ? $request["user_id"] : 0;
			
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
                        sh.is_delivery,
                        (SELECT count(*) AS liked_cnt FROM nham_shop_like WHERE shop_id = sh.shop_id ) AS liked_cnt,
                        (SELECT count(*) AS is_saved FROM nham_saved_shop WHERE shop_id = sh.shop_id AND user_id = ?) AS is_saved,
                        (SELECT count(*) AS is_liked FROM nham_shop_like WHERE shop_id= sh.shop_id AND user_id = ? ) AS is_liked,
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
		array_push($param, $user_id, $user_id ,$current_lat , $current_lng);
		
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
			$order_type = " sh.shop_dis_order asc, liked_cnt desc ,sh.shop_view_count desc ";
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
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		 
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		if(!$current_lat || $current_lat > 90 || $current_lat <-90) $current_lat= 0;
		if(!$current_lng || $current_lng > 180 || $current_lng < -180) $current_lng= 0;
		if(!$nearby_value || $nearby_value <= 0) $nearby_value = 0.5;
		
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
					sh.shop_lat_point,
					sh.shop_lng_point,
					SQRT(POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * (? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh ";
		
		$this->load->helper('validate');
		if( isset($request["serve_category_id"]) && validateNumeric($request["serve_category_id"]) ){
		    $sql .="\n LEFT JOIN nham_serve_cate_map_shop cate  ON cate.shop_id = sh.shop_id ";
		}
		
		$sql .="  WHERE 1=1 ";
		if(!isset($request["status"]))
		  $sql .="\n AND sh.shop_status = 1 ";		
		
		$param = array();
		array_push($param, $current_lat, $current_lng);
		
		if( isset($request["serve_category_id"]) && validateNumeric($request["serve_category_id"]) ){
		    $sql .= "\n AND cate.serve_category_id = ? ";
		    array_push($param, (int)$request["serve_category_id"]);
		}
		
		if(isset($request["sch_str"])){
			$sql .= " AND REPLACE(CONCAT_WS(COALESCE(sh.shop_name_en,''),COALESCE(sh.shop_name_kh,''),COALESCE(sh.shop_serve_type,''),COALESCE(sh.shop_address,'')),' ','') LIKE REPLACE(?,' ','') ";
			array_push($param, "%".$request["sch_str"]."%");
		}
	//	$sql .= " HAVING distance < ? ORDER BY distance ";
	//	array_push($param, $nearby_value);
		if(!isset($request["status"]))
		    $sql .= " ORDER BY distance ";
		else 
		    $sql .= " ORDER BY sh.shop_status";
		
		$query_record = $this->db->query($sql , $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= " LIMIT ? OFFSET ? ";
		
		array_push($param, $limit, $offset);
		
		$query = $this->db->query($sql , $param);
		$response["response_data"] = $query->result();
			
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
                     (SELECT count(*) AS liked_cnt FROM nham_shop_like WHERE shop_id = sh.shop_id ) AS liked_cnt,
					SQRT(POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * (? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh
				WHERE sh.shop_status = 1
				ORDER by sh.shop_dis_order asc, liked_cnt desc ,sh.shop_view_count desc ";
		
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
					SQRT(POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * (? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh ";
		
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
			$sql .= "\n AND sh.country_id = ? ";
			array_push($param, (int)$request["country_id"]);
		}
		
		if( isset($request["city_id"]) && validateNumeric($request["city_id"]) ){
			$sql .= "\n AND sh.country_id = ? ";
			array_push($param, (int)$request["city_id"]);
		}
		
		if( isset($request["district_id"]) && validateNumeric($request["district_id"]) ){
			$sql .= "\n AND sh.district_id = ? ";
			array_push($param, (int)$request["district_id"]);
		}
		
		if( isset($request["commune_id"]) && validateNumeric($request["commune_id"]) ){
			$sql .= "\n AND sh.commune_id = ? ";
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
		
		$user_id = (isset($request["user_id"])) ? $request["user_id"] : 0;
			
		$sql = "SELECT 
                    sh.shop_id,
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
                    sh.is_delivery,
                    (SELECT count(*) AS liked_cnt FROM nham_shop_like WHERE shop_id = sh.shop_id ) AS liked_cnt,
                    (SELECT count(*) AS is_saved FROM nham_saved_shop WHERE shop_id = sh.shop_id AND user_id = ?) AS is_saved,
                    (SELECT count(*) AS is_liked FROM nham_shop_like WHERE shop_id= sh.shop_id AND user_id = ? ) AS is_liked,
					SQRT(
						POW(69.1 * (sh.shop_lat_point - ? ), 2) +
						POW(69.1 * ( ? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
				FROM nham_shop sh
				WHERE sh.shop_status = 1
				AND sh.shop_id = ?";
		$query = $this->db->query($sql , array($user_id, $user_id, $current_lat, $current_lng, $shop_id));
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
				AND br.branch_id = (SELECT branch_id from nham_shop where shop_id = ?)
				AND sh.shop_id <> ? ";
		array_push($param ,$current_lat, $current_lng, $request["shop_id"], $request["shop_id"]);
		
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
	
	function insertTempShop($request){
	    $sql = "INSERT INTO nham_shop(
					shop_name_en,
					shop_logo,
					shop_status,
                    shop_created_date
				)VALUES(?,?,?,?)";
	    $param["shop_name_en"] = $request["shop_name_en"];
	    $param["shop_logo"] = $request["shop_logo"];	  
	    $param["shop_status"] = $request["shop_status"];
	    $current_time = new DateTime();
	    $current_time = $current_time->format('Y-m-d H:i:s');
	    $param["shop_created_date"] = $current_time;
	    $query = $this->db->query($sql , $param);
	    return ($this->db->affected_rows() != 1) ? false : true;
	}
	
	function insertShop($request){
		
		
		$sql = "INSERT INTO nham_shop(
					shop_name_en,
					shop_name_kh,
					shop_logo,
					country_id,
					city_id,
					district_id,
					commune_id,
					shop_address,
					shop_opening_time,
					shop_close_time,
					shop_status,
					shop_time_zone,
					shop_lat_point,
					shop_lng_point,
                    shop_created_date
				)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$param["shop_name_en"] = $request["shop_name_en"];
		$param["shop_name_kh"] = $request["shop_name_kh"];
		$param["shop_logo"] = $request["shop_logo"];
		$param["country_id"] = $request["country_id"];
		$param["city_id"] = $request["city_id"];
		$param["district_id"] = $request["district_id"];
		$param["commune_id"] = $request["commune_id"];
		$param["shop_address"] = $request["shop_address"];
		$param["shop_opening_time"] = $request["shop_opening_time"];
		$param["shop_close_time"] = $request["shop_close_time"];
		$param["shop_status"] = $request["shop_status"];
		$param["shop_time_zone"] = (!isset($request["shop_time_zone"])) ? "Asia/Phnom_Penh" : $request["shop_time_zone"];
		$param["shop_lat_point"] = $request["shop_lat_point"];
		$param["shop_lng_point"] = $request["shop_lng_point"];
		
		$current_time = new DateTime();
		$current_time = $current_time->format('Y-m-d H:i:s');
		$param["shop_created_date"] = $current_time;
		$query = $this->db->query($sql , $param);		
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	
	/*function isUserBookmarked( $request ){
		
		$sql = "SELECT count(*) AS is_saved
			FROM nham_saved_shop WHERE shop_id = ? AND user_id = ? ";
			
		$param["shop_id"] = $request["shop_id"];
		$param["user_id"] = $request["user_id"]; 
		$query = $this->db->query($sql, $param);
		return $query->row();
	}*/
	
	function savePlace( $request ){
		
		$sql = "INSERT INTO nham_saved_shop( shop_id, user_id, created_date )
				SELECT 
					?,
					?,
					?
				FROM dual
				WHERE 
				( SELECT count(*) FROM nham_saved_shop WHERE shop_id = ? AND user_id = ? ) < 1 ";
		$param["shop_id"] = (int)$request["shop_id"];
		$param["user_id"] = (int)$request["user_id"];
		
		$current_time = new DateTime();
		$current_time = $current_time->format('Y-m-d H:i:s');
		$param["created_date"] =  $current_time;
		$param["shop_id_1"] = (int)$request["shop_id"];
		$param["user_id_1"] = (int)$request["user_id"];
		
		$query = $this->db->query($sql , $param);
		return $query;
	}
	
	function unSavePlace( $request ){
		
		$sql = "DELETE FROM nham_saved_shop WHERE shop_id = ? AND user_id = ?";
		$param["shop_id"] = $request["shop_id"];
		$param["user_id"] = $request["user_id"];
		
		$query = $this->db->query($sql , $param);
		return $query;
		
	}
	
	function likePlace($request){
	    
	    $sql = "INSERT INTO nham_shop_like(shop_id , user_id, created_date)
                SELECT ?,
                    ?,
                    ?
                FROM dual
                WHERE
                ( SELECT count(*) FROM nham_shop_like WHERE shop_id = ? AND user_id = ? ) < 1";
	    $param["shop_id"] = (int)$request["shop_id"];
	    $param["user_id"] = (int)$request["user_id"];
	    
	    $current_time = new DateTime();
	    $current_time = $current_time->format('Y-m-d H:i:s');
	    $param["created_date"] =  $current_time;
	    $param["shop_id_1"] = (int)$request["shop_id"];
	    $param["user_id_1"] = (int)$request["user_id"];
	    
	    $query = $this->db->query($sql , $param);
	    return $query;
	}
	
	function unLikePlace($request){
	    
	    $sql = "DELETE FROM nham_shop_like WHERE shop_id = ? AND user_id = ?";
	    $param["shop_id"] = $request["shop_id"];
	    $param["user_id"] = $request["user_id"];
	    
	    $query = $this->db->query($sql , $param);
	    return $query;
	}
	
	function listSavedShop( $request ){
	    
	    $row = (int)$request["row"];
	    $page = (int)$request["page"];
	    
	    if(!$row) $row = 10;
	    if(!$page) $page = 1;
	  
	    $limit = $row;
	    $offset = ($row*$page)-$row;
	   
	    
	    $param = array();
	    $sql = "SELECT 
                	sh.shop_id,
                	sh.shop_name_en,
                	sh.shop_name_kh,
                	sh.shop_address,
                	sh.shop_logo,
                	(SELECT count(*) FROM nham_shop_like WHERE shop_id = sh.shop_id) AS cnt_like,
                	ss.created_date
                	
                FROM nham_shop sh
                INNER JOIN nham_saved_shop ss
                ON ss.shop_id = sh.shop_id
                WHERE ss.user_id = ? ";
	    
	    if(isset($request["start_duration"]) && isset($request["end_duration"])){
	       $current_time = new DateTime();
	       $current_time = $current_time->format('Y-m-d H:i:s');
	       $sql .= " AND ss.created_date BETWEEN '".$current_time."' - INTERVAL ? DAY AND '".$current_time."' - INTERVAL ? DAY ";
	       array_push($param , $request["user_id"], $request["end_duration"],  $request["start_duration"]);
	    }else{
	       array_push($param , $request["user_id"]);
	    }
               
	    $query_record = $this->db->query($sql , $param);
	    $total_record = count($query_record->result());
	    $total_page = $total_record / $row;
	    if( ($total_record % $row) > 0){
	        $total_page += 1;
	    }
	    
	    $response["total_record"] = $total_record;
	    $response["total_page"] = (int)$total_page;
	    
	    $sql .= " ORDER BY ss.created_date DESC
				  LIMIT ? OFFSET ? ";
	    array_push($param , $limit, $offset);
	    
	    $query = $this->db->query($sql , $param);
	    $response["response_data"] = $query->result();
	    
	    return $response;
	}
	
	function userRequestCreateShop($request){
	    
	    $sql = "INSERT INTO nham_shop_temp(shop_name_kh, shop_name_en, shop_phone, user_id) values(?, ?, ? ,?)";
	    $param["shop_name_kh"] = $request["shop_name_kh"];
	    $param["shop_name_en"] = $request["shop_name_en"];
	    $param["shop_phone"] = $request["shop_phone"];
	    $param["user_id"] = $request["user_id"];
	    
	    $query = $this->db->query($sql , $param);
	    return $query;
	}

	function listEvent($request){	
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
	
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$param = array();
		$sql = "SELECT ev.evt_id, ev.shop_id, ns.shop_name_en, ns.shop_name_kh, ns.shop_logo, ev.evt_cntt, ev.evt_img, ev.created_date
					FROM nham_event ev
					LEFT JOIN nham_shop ns ON ns.shop_id = ev.shop_id 
					WHERE ev.evt_id = ? AND ev.status = 1
				UNION
				(SELECT ne.evt_id, ne.shop_id, ns.shop_name_en, ns.shop_name_kh, ns.shop_logo, ne.evt_cntt, ne.evt_img, ne.created_date
					FROM nham_event ne
					LEFT JOIN nham_shop ns ON ns.shop_id = ne.shop_id 
					WHERE ne.evt_id <> ? AND ne.status = 1 ORDER BY ne.evt_id DESC)";

		$event_id = (int)$request["event_id"];
		if(!$event_id) $event_id = 0;
		array_push($param, $event_id, $event_id);
			
		$query_record = $this->db->query($sql , $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= " LIMIT ? OFFSET ?";
		array_push($param , $limit, $offset);
		
		$query = $this->db->query($sql , $param);
		$response["response_data"] = $query->result();
		
		return $response;
	}

}

?>