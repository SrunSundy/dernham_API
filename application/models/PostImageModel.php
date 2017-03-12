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
	
}

?>