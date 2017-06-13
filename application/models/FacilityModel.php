<?php
class FacilityModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function insertShopFacility($request){
		
		$facilities = array();
		$shopdata["sh_facility"] = array_unique($request["sh_facility"]);
		for($i=0; $i< count($shopdata["sh_facility"]); $i++){
			
			$cateitem["sh_facility_id"] = $shopdata["sh_facility"][$i];
			$cateitem["shop_id"] = $request["shop_id"];
			array_push($facilities, $cateitem);
		}
		return $this->db->insert_batch('nham_shop_facility_map', $facilities);
		
	}
	
	function listFacility( $request ){
		
		$sql = "SELECT 	facility.sh_facility_id,
						facility.sh_facility_icon,
						facility.sh_facility_name
				FROM nham_shop_facility facility 
				WHERE facility.sh_facility_status = 1 ";
		$param = array();	
		if(isset($request["srch_key"]) && $request["srch_key"] != ""){
			
			$sql .= " AND REPLACE(facility.sh_facility_name,' ','') LIKE REPLACE(?,' ','') ";
			array_push($param, "%".$request["srch_key"]."%");
		}
		
		$sql .= " ORDER BY facility.sh_facility_id ";
		
		if( (isset($request["row"]) && $request["row"] != "") &&
			(isset($request["page"]) && $request["page"] != "")){
			
			$row = (int)$request["row"];
			$page = (int)$request["page"];

			$limit = $row;
			$offset = ($row*$page)-$row;
			
			
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
			
			
		}	
		$query = $this->db->query($sql , $param);
		$response["response_data"] = $query->result();
		return $response;
	}
	
	function listFacilityByShopid( $shop_id ){
		
		$sql = "SELECT 
					facility.sh_facility_icon,
					facility.sh_facility_name
				FROM nham_shop_facility_map map
				LEFT JOIN nham_shop_facility facility ON facility.sh_facility_id = map.sh_facility_id
				WHERE facility.sh_facility_status = 1
				AND map.shop_id = ?";
		$query = $this->db->query($sql, $shop_id);
		
		$response = $query->result();
		return $response;
		
	}
	
}
?>