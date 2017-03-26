<?php
class CommentModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function countCommentByPostid($request){
	
		$sql = "SELECT count(*) as count FROM nham_comment 
				WHERE status = 1 
				AND  post_id = ?";
		$param["post_id"] = $request["post_id"];
		$query = $this->db->query($sql , $param);
		return $query->row();
	}
	
	function listCommentByPostId($request){
		
		//order type :::::
		//default --- sort by oldest comment
		//1 --- sort by newest comment
		
		$row = (int)$request["row"];
		$page = (int)$request["page"];
		
		$order_type = 0;
		if(isset($request["order_type"])){
			$order_type = (int)$request["order_type"];
		}
				
		if(!$row) $row = 10;
		if(!$page) $page = 1;
		
		$limit = $row;
		$offset = ($row*$page)-$row;
		
		$order = " ORDER BY com.id ";
		
		if($order_type == 1){
			$order = " ORDER BY com.id DESC ";
		}
		
		$param = array();
		$sql = "SELECT 
					com.id,
					com.text,
					com.created_date,
					u.user_id,
					u.user_fullname,
					u.user_photo
				FROM nham_comment com
				LEFT JOIN nham_user u on com.user_id = u.user_id
				WHERE com.status = 1 
				AND com.post_id = ? ";
		$sql .= $order;
		
		array_push($param, $request["post_id"]);
		$query_record = $this->db->query($sql, $param);
		$total_record = count($query_record->result());
		$total_page = $total_record / $row;
		if( ($total_record % $row) > 0){
			$total_page += 1;
		}
		
		$response["total_record"] = $total_record;
		$response["total_page"] = (int)$total_page;
		
		$sql .=" LIMIT ? OFFSET ? ";
		array_push($param, $limit , $offset);
		$query = $this->db->query($sql, $param);
		
		$response["response_data"] = $query->result();
		return $response;
	}
	
	
	
}
?>