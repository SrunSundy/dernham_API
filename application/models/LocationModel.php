<?php
class LocationModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function listCountry( $request ){
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$sql = "SELECT  
					country_id,
					country_name
				FROM nham_country
				WHERE country_status = 1 ";
		$param = array();
		
		if(isset($request["srch_name"]) && $request["srch_name"] != ""){
			
			$sql .= " AND  REPLACE(country_name,' ','')  LIKE REPLACE(?,' ','') ";
			array_push($param, "%".$request["srch_name"]."%");			
		}
		
		$query_record = $this->db->query($sql , $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= " ORDER BY country_name ";
		$sql .= " LIMIT ? OFFSET ? ";
		array_push($param, $limit, $offset);
		$query = $this->db->query($sql , $param);
		
		$response["response_data"] = $query->result();
		return $response;
		
	}
	
	function listCity( $request ){
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$sql = " SELECT  
					city_id,
					city_name
				FROM nham_city
				WHERE city_status = 1 ";
		$param = array();
		
		if(isset($request["country_id"])){
			
			$sql .= " AND country_id = ? ";
			array_push($param, $request["country_id"]);
		}
		
		if(isset($request["srch_name"]) && $request["srch_name"] != ""){
			
			$sql .= " AND  REPLACE(city_name,' ','') LIKE REPLACE(?,' ','') ";
			array_push($param, "%".$request["srch_name"]."%");
		}
		
		$query_record = $this->db->query($sql , $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= " ORDER BY city_name ";
		$sql .= " LIMIT ? OFFSET ? ";
		array_push($param, $limit, $offset);
		$query = $this->db->query($sql , $param);
		
		$response["response_data"] = $query->result();
		return $response;
		
	}
	
	function listDistrict( $request ){
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$sql = " SELECT  
					district_id,
					district_name
				FROM nham_district
				WHERE district_status = 1 ";
		$param = array();
		
		if(isset($request["city_id"])){
		
			$sql .= " AND city_id = ? ";
			array_push($param, $request["city_id"]);
		}
		
		if(isset($request["srch_name"]) && $request["srch_name"] != ""){
			
			$sql .= " AND REPLACE(district_name,' ','') LIKE REPLACE(?,' ','') ";
			array_push($param, "%".$request["srch_name"]."%");
		}
		
		$query_record = $this->db->query($sql , $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= " ORDER BY district_name ";
		$sql .= " LIMIT ? OFFSET ? ";
		array_push($param, $limit, $offset);
		$query = $this->db->query($sql , $param);
		
		$response["response_data"] = $query->result();
		return $response;
		
	}
	
	function listCommune( $request ){
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$sql = " SELECT  
					commune_id,
					commune_name
				FROM nham_commune
				WHERE commune_status = 1 ";
		$param = array();
		
		if(isset($request["district_id"])){
			
			$sql .= " AND district_id = ? ";
			array_push($param, $request["district_id"]);
		}
		
		if(isset($request["srch_name"]) && $request["srch_name"] != ""){
			
			$sql .= " AND REPLACE(commune_name,' ','') LIKE REPLACE(?,' ','') ";
			array_push($param, "%".$request["srch_name"]."%");
		}
		
		$query_record = $this->db->query($sql , $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= " ORDER BY commune_name ";
		$sql .= " LIMIT ? OFFSET ? ";
		array_push($param, $limit, $offset);
		$query = $this->db->query($sql , $param);
		
		$response["response_data"] = $query->result();
		return $response;
		
	}
	
}
?>