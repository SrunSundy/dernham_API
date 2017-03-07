<?php
class UploadModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();		
	}
	
	function uploadUserPhoto( $request ){

		$file = $request["image_file"];
		$new_name_file = $request["new_name"];
		$response = array();
		if ( ! empty($file))
		{
				
			$new_name = $new_name_file.".jpg";
			$target_small_dir = "./uploadimages/user/small/";
			$target_medium_dir = "./uploadimages/user/medium/";
			$target_big_dir = "./uploadimages/user/big/";
		
			$checkdirectory_small = $this->checkDirectory($target_small_dir);
			$checkdirectory_medium = $this->checkDirectory($target_medium_dir);
			$checkdirectory_big = $this->checkDirectory($target_big_dir);
			$checkdirectory_bignocrop = $this->checkDirectory($target_big_nocrop_dir);
			$allowfiletype = $this->allowImageType(array("image/jpg","image/jpeg", "image/gif", "image/png"), $file['file']['type']);
			$allowsize = $this->allowImageSize(10240 , 20000000, $file["file"]["size"]);//20MB
			//$allowmindimension = $this->allowImageMinimumDimension(500, 300, $file["file"]["tmp_name"]);
			//$allowmaxdimension = $this->allowImageMaximumDimension(8000, 5000, $file["file"]["tmp_name"]);
		
			$permission = array();
			array_push($permission ,
			$checkdirectory_small,
			$checkdirectory_medium,
			$checkdirectory_big,
			$checkdirectory_bignocrop,
			$allowfiletype,
			$allowsize
			//$allowmindimension,
			//$allowmaxdimension
			);
			$check = $this->checkPermission($permission);
		
			$message = $check["message"];
			$uploadok =  $check["error"];
			if ($uploadok) {
				$message = " File can not be uploaded.".$message;
				$response['is_upload']= false;
				$response["message"] = $message;
			} else {
					
				$isuploadimg = array();
				
		
				
				$imgsize = 960;
				list($width, $height) = getimagesize( $file["file"]["tmp_name"]);
				if($width < 960){
					$imgsize = $width;
				}
				$big = $this->resizeImageFixpixel($target_big_dir.$new_name, $_FILES["file"]["tmp_name"][$i] , $imgsize, 80);
				$medium = $this->resizeImageFixpixel($target_medium_dir.$new_name, $_FILES["file"]["tmp_name"][$i] , 180 , 80);									
				$small = $this->resizeImageFixpixel($target_medium_dir.$new_name, $_FILES["file"]["tmp_name"][$i] , 50, 80);
				
				$errorupload = false;
				array_push($isuploadimg, $big, $medium, $small);
				for($i=0 ; $i<count($isuploadimg); $i++){
					if(!$isuploadimg[$i]){
						$errorupload = true;
						break;
					}
				}
				if($errorupload){
					$message = "There was an error uploading your file.";
					$response['is_upload']= false;
					$response["message"] = $message;
				}else{
					$response['is_upload'] = true;
					$response['message'] =" File upload successfully!";
					$response['filename'] = $new_name;
				}
		
			}
		}else{
			$response['is_upload']= false;
			$response["message"] = "No File!";
		}
		$json = json_encode($response);
		echo $json;
	}
	
	
	
	
	function generateRandomString($length) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString."_".time();
	}
	
	function checkDirectory( $path ){
	
		$response = array();
		if(!file_exists($path)){
			$response['message'] = "The uploaded path does not appear to be valid!";
			$response['is_allow'] = false;
		}else{
			$response['message'] = "";
			$response['is_allow'] = true;
		}
		return $response;
	
	}
	
	function allowImageType( $imagetypearr , $file ){
	
		$response = array();
		if(!in_array($file , $imagetypearr)) {
			$response['message'] = "The filetype you are attempting to upload is not allowed!";
			$response['is_allow'] = false;
		}else{
			$response['message'] = "";
			$response['is_allow'] = true;
		}
		return $response;
	
	}
	
	function allowImageSize( $minsize , $maxsize, $file ){
		$response = array();
		if ($file > $maxsize) {
			$show = $maxsize / 1024;
			$show = $show / 1024;
			$response['message'] = "The file you are attempting to upload is too large! (Maximum size: $show MB) ";
			$response['is_allow'] = false;
			return $response;
		}
		if ($file < $minsize) {
			$show = $minsize / 1024;
			$show = $show / 1024;
			$response['message'] = "The file you are attempting to upload is too small! (Minimum size: $show MB)";
			$response['is_allow'] = false;
			return $response;
		}
		$response['message'] = "";
		$response['is_allow'] = true;
		return $response;
	}
	
	function resizeImageFixpixel($targetfolder , $sourcefolder , $size , $quality){
	
		$source_img = $sourcefolder;
		$destination_img = $targetfolder;
		$info = getimagesize($source_img);
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
		}
			
		// Resample
		$image_p = imagecreatetruecolor($new_width, $new_height);
		if ($info['mime'] == 'image/jpeg')
			$image = imagecreatefromjpeg($source_img);
		elseif ($info['mime'] == 'image/gif')
		$image = imagecreatefromgif($source_img);
		elseif ($info['mime'] == 'image/png')
		$image = imagecreatefrompng($source_img);
		else
			return false;
		$white = imagecolorallocate($image_p,  255, 255, 255);
		imagefilledrectangle($image_p, 0, 0, $width, $height, $white);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		return imagejpeg($image_p, $destination_img, $quality);
	
	}
	
}
?>