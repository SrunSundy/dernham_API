<?php
class UploadEncodeModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();		
	}
	
	function test( $request , $mainfolder){
	
		$response = array();
	
		$file = $request["image_file"];
		if ( ! empty($file))
		{
	
			/* $target_extreme_small_dir = "./uploadimages/shopimages/extreme_small/"; */
			$target_small_dir = $mainfolder."post/small/";
			$target_medium_dir = $mainfolder."post/medium/";
			$target_big_dir = $mainfolder."post/big/mylovsdfe.jpg";
	
			$reportwrapper = array();
				
			$number_of_files = $file;
			
			$source_img = $file;
			$destination_img = $target_big_dir;
			/* $info = getimagesize($source_img);
			list($width, $height) = $info;
			$new_width = $size;
			$new_height = $size;
			if($width > $height){
				$widthbigger = $width/$height;
				$new_width = $size;
				$new_height = $size/$widthbigger;
			}else{
				$heightbigger = $height/$width;
				$new_height = $size;
				$new_width = $size/$heightbigger;
			} */
				
		//	$image_p = imagecreatetruecolor(500, 500);
			//			$white = imagecolorallocate($image_p,  255, 255, 255);
		//	imagefilledrectangle($image_p, 0, 0, 500, 500, $white);
		//	imagecopyresampled($image_p, $source_img, 0, 0, 0, 0, 500, 500, 500, 500);
			imagejpeg($source_img, $destination_img);
				
			
			
	
			$response["is_upload"] = true;
			$response["message"] = "success";
			$response["fileupload"] =$number_of_files;
		}
		else{
			$response['is_upload']= false;
			$response["message"] = "No File!";
			$response["fileupload"] = null;
		}
	
		return $response;
		//$json = json_encode($response);
		//echo $json;
	
	}
	

	
}
?>