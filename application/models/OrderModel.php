<?php
class OrderModel extends CI_Model{
  
  function __construct()
  {
    parent::__construct();
    $this->load->database();
  }
  
  function userOrder($request){
  
    $sql = "INSERT INTO nham_order(user_id, order_code, product_id, phone, address, quantity, size) VALUES(?, ?, ?, ?, ?, ?, ?)";
    $param["user_id"] = $request["user_id"];  
    $param["order_code"] = $request["order_code"];
    $param["product_id"] = $request["product_id"];
    $param["user_phone"] = $request["user_phone"];
    $param["address"] = $request["address"];
    $param["quantity"] = $request["quantity"];
    $param["size"] = $request["size"];  
    
    $query = $this->db->query($sql , $param);
    return ($this->db->affected_rows() != 1) ? false : true;
  }
  
  
}
?>