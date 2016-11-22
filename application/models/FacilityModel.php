<?php
class FacilityModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function getFacilityByShopid( $shop_id ){
		
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