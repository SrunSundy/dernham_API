<?php 
class AdvertisementModel extends CI_Model{
  
  function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

  function homeAdvertisementHeader( $request ){
    $sql = "SELECT title, image, shop_id FROM nham_advertisement WHERE type = 1 and status = 1" ;
    $query = $this->db->query($sql);
    $response["response_data"] = $query->result();
    return $response;
  }
}

?>