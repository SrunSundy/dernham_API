<?php 
class PostImageModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function insertUserPostImage( $request ){
	
		$postimage = array();
		for($i=0; $i< count($request["post_image"]); $i++){
				
			$postimageitem["post_image_src"] = $request["post_image"][$i]["image_name"];
			$postimageitem["post_id"] = $request["post_id"];
			array_push($postimage , $postimageitem);
		}
		$status = $this->db->insert_batch('nham_user_post_image', $postimage);
		return $status;
	}
	
	function listUserPostImageByPostid($request){
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$param = array();
		$sql = "SELECT 
					pi.post_image_id,
					pi.post_image_src
				FROM nham_user_post_image pi
				WHERE pi.status = 1
				AND pi.post_id = ? 
				ORDER BY pi.post_image_id ";
		
		array_push($param, $request["post_id"]);
		$query_record = $this->db->query($sql, $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .= "LIMIT ? OFFSET ?";
		array_push($param, $limit , $offset);
		$query = $this->db->query($sql, $param);
		
		$response["response_data"] = $query->result();
		return $response;
	}
	
}

?>