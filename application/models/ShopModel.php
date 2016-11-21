<?php

class ShopModel extends CI_Model{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	public function listShop($request){
		
		if(!isset($request["row"])) $request["row"] = 10;
		if(!isset($request["page"])) $request["page"] = 1;
		if(!isset($request["current_lat"])) $request["current_lat"] = 0;
		if(!isset($request["current_lng"])) $request["current_lng"] = 0;
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		$current_lat = (float)$request["current_lat"];
		$current_lng = (float)$request["current_lng"];
			
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		if(!$current_lat) $current_lat= 0;
		if(!$current_lng) $current_lng= 0;
			
		$limit = $row;
		$offset = ($row*$page)-$row;
			
		$sql = "SELECT sh.shop_id,
			 TRIM(COALESCE(sh.shop_logo,'')) shop_logo,
			 sh.shop_cover,
			 sh.shop_name_en,
			 sh.shop_name_kh,
			 sh.shop_address,
			 sh.shop_time_zone,
			 sh.shop_opening_time,
			 sh.shop_close_time,
			 SQRT(
					POW(69.1 * (sh.shop_lat_point - ?), 2) +
					POW(69.1 * (? - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
			FROM nham_shop sh
			WHERE sh.shop_status = 1
			LIMIT ? OFFSET ? ";
		/* 
		SELECT sh.shop_id,
		TRIM(COALESCE(sh.shop_logo,'')) shop_logo,
		sh.shop_cover,
		sh.shop_name_en,
		sh.shop_name_kh,
		sh.shop_address,
		sh.shop_time_zone,
		sh.shop_opening_time,
		sh.shop_close_time,
		SQRT(
		POW(69.1 * (sh.shop_lat_point - 11.565723328439192), 2) +
		POW(69.1 * (104.88913536071777 - sh.shop_lng_point) * COS(sh.shop_lat_point / 57.3), 2))*1.61 AS distance
		FROM nham_shop sh
		LEFT JOIN nham_serve_cate_map_shop cate	ON cate.shop_id = sh.shop_id
		LEFT JOIN nham_country cou ON cou.country_id = sh.country_id
		LEFT JOIN nham_city city ON city.city_id = sh.city_id
		LEFT JOIN nham_district dis ON dis.district_id = sh.district_id
		LEFT JOIN nham_commune com ON com.commune_id = sh.commune_id
		WHERE sh.shop_status = 1
		AND cate.serve_category_id = 10
		--HAVING distance < 1
		ORDER BY sh.shop_id
		LIMIT 100 OFFSET 0 */
		$query = $this->db->query($sql , array($current_lat, $current_lng, $limit, $offset));
		$responsequery = $query->result();
			
		foreach($responsequery as $item){
			
			$now = new DateTime($item->shop_time_zone);
			$now = strtotime($now->format('H:i:s'));		
			$is_open = 0;
			$time_to_close = 0;
			$time_to_open = 0;

			if(strtotime($item->shop_opening_time) < $now && strtotime($item->shop_close_time) > $now){
				$is_open = 1;
				$time_to_close = $this->substractCurrentTime($item->shop_time_zone, $item->shop_close_time);
				$time_to_close = $this->covertToMilisecond($time_to_close);
			}
			
			if(strtotime($item->shop_opening_time) > $now){
				$time_to_open = $this->substractCurrentTime($item->shop_time_zone, $item->shop_opening_time);
				$time_to_open = $this->covertToMilisecond($time_to_open);
			}
			if(strtotime($item->shop_close_time) < $now){
				$subfulltime = $this->substractCurrentTime($item->shop_time_zone, "24:00:00");
				$subzerotime = $this->substractTime($item->shop_opening_time, "00:00:00");
				$time_to_open = $this->addTime($subfulltime , $subzerotime); // already return as milisecond
			}
						
			$item->is_shop_open = $is_open;
			$item->time_to_close = $time_to_close;
			$item->time_to_open = $time_to_open;
		}
			
		$response["response_data"] = $responsequery;
		return $response;
		
	}
	
	function substractCurrentTime($timezone , $value){
	
		$now =new DateTime($timezone);
		$now =  $now->format('H:i:s');
		$now = new DateTime($now);
	
		$shoptime = new DateTime($value);
		$interval = $shoptime->diff($now);
		return $interval->format('%H:%I:%S');
	
	}
	function substractTime($value1 , $value2){
	
		$shoptime1 = new DateTime($value1);
		$shoptime2 = new DateTime($value2);
		$interval = $shoptime2->diff($shoptime1);
		return $interval->format('%H:%I:%S');
	
	}
	
	function addTime($time1, $time2) {
		$times = array($time1, $time2);
		$seconds = 0;
		foreach ($times as $time)
		{
			list($hour,$minute,$second) = explode(':', $time);
			$seconds += $hour*3600;
			$seconds += $minute*60;
			$seconds += $second;
		}
		return $seconds * 1000;
	}
	
	function covertToMilisecond($time){
		
		$seconds = 0;		
		list($hour,$minute,$second) = explode(':', $time);
		$seconds += $hour*3600;
		$seconds += $minute*60;
		$seconds += $second;		
		return $seconds * 1000;
		
	}
	

}

?>