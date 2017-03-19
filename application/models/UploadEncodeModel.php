<?php
class UploadEncodeModel extends CI_Model{
	
	function __construct()
	{
		parent::__construct();		
	}
	
	function uploadSingleImage( $request){
	
		$response = array();	
		$files = $request["image_data"];
		
		if ( ! empty($files))
		{
	
			$this->load->helper('dernhamutils');
			$new_name = generateRandomString(10).".jpg";			
		 	$data = explode(',',$files);
		 	$image_size = strlen($data[1]);
			$decode_image = base64_decode($data[1]);
		
		    $image_info = getimagesizefromstring($decode_image);
			
			$target_small_dir = $request["target_file"]."post/small/";
			$target_medium_dir = $request["target_file"]."post/medium/";
			$target_big_dir = $request["target_file"]."post/big/";
			
			$checkdirectory_small = $this->checkDirectory($target_small_dir);
			$checkdirectory_medium = $this->checkDirectory($target_medium_dir);
			$checkdirectory_big = $this->checkDirectory($target_big_dir);
			$allowfiletype = $this->allowImageType(array("image/jpg","image/jpeg", "image/gif", "image/png"), $image_info["mime"]);
			$allowsize = $this->allowImageSize(10240 , 20000000, $image_size);//20MB
			
			$permission = array();
			array_push($permission ,
				$checkdirectory_small,
				$checkdirectory_medium,
				$checkdirectory_big,			
				$allowfiletype,
				$allowsize 
			);
			$check = $this->checkPermission($permission);
			
			$message = $check["message"];
			$uploadok =  $check["error"];
			if ($uploadok) {
				$message = " File can not be uploaded.".$message;
				$response['is_upload']= false;
				$response["message"] = $message;
				$response['fileupload'] = null;
			}else{
				
			
				$isuploadimg = array();
								
				$imgsize = 960;
				list($width, $height) = $image_info;
				if($width < 960){
					if($width > $height)
						$imgsize = $width;
					else 
						$imgsize = $height;
				}
				$big = $this->resizeImageFixpixel($target_big_dir.$new_name, $decode_image, $imgsize, 80);
				$medium = $this->resizeImageFixpixelAndScaleCenter($target_medium_dir.$new_name, $decode_image , 180 , 80);
				$small = $this->resizeImageFixpixel($target_small_dir.$new_name, $decode_image, 50, 80);
				
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
					$response['fileupload'] = null;
				}else{
					$response['is_upload'] = true;
					$response['message'] =" File upload successfully!";
					$response['fileupload'] = $new_name;
				}
				
			}
	
		
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
	
	function uploadMultipleImages( $request){
	
		$response = array();
		$files = $request["image_data"];
	
		if ( ! empty($files))
		{
	
			$target_small_dir = $request["target_file"]."post/small/";
			$target_medium_dir = $request["target_file"]."post/medium/";
			$target_big_dir = $request["target_file"]."post/big/";
			$reportwrapper = array();
			
			
			for($i=0 ; $i<count($files) ; $i++){
				$report = array();
				$this->load->helper('dernhamutils');
				$new_name = generateRandomString(10).".jpg";
				$data = explode(',',$files[$i]);
				$image_size = strlen($data[1]);
				$decode_image = base64_decode($data[1]);
				
				$image_info = getimagesizefromstring($decode_image);
							
				$checkdirectory_small = $this->checkDirectory($target_small_dir);
				$checkdirectory_medium = $this->checkDirectory($target_medium_dir);
				$checkdirectory_big = $this->checkDirectory($target_big_dir);
				$allowfiletype = $this->allowImageType(array("image/jpg","image/jpeg", "image/gif", "image/png"), $image_info["mime"]);
				$allowsize = $this->allowImageSize(10240 , 20000000, $image_size);//20MB
				
				$permission = array();
				array_push($permission ,
				$checkdirectory_small,
				$checkdirectory_medium,
				$checkdirectory_big,
				$allowfiletype,
				$allowsize
				);
				$check = $this->checkPermission($permission);
				
				$message = $check["message"];
				$uploadok =  $check["error"];
				if ($uploadok) {
					$message = " File can not be uploaded.".$message;
					$response['is_upload']= false;
					$response["message"] = $message;
					$response['fileupload'] = null;
				}else{
				
					$isuploadimg = array();
					
					$info = $image_info;
					list($width, $height) = $info;
					
					
					$my_img_size = 0;
					$my_img_medium_size = 0;
					if($width > $height){
						$my_img_size = $width;
						$my_img_medium_size = $width;
					}else{
						$my_img_size = $height;
						$my_img_medium_size = $height;
					}
					
					if($my_img_size > 960){
						$my_img_size = 960;
					}
					
					if($my_img_medium_size > 520){
						$my_img_medium_size = 520;
					}
					
					
					$big = $this->resizeImageFixpixel($target_big_dir.$new_name, $decode_image , $my_img_size, 80);
					$medium = $this->resizeImageFixpixel($target_medium_dir.$new_name, $decode_image , $my_img_medium_size, 80);
					$small = $this->resizeImageFixpixelAndScaleCenter($target_small_dir.$new_name, $decode_image , 180, 80);
					
					$errorupload = false;
					array_push($isuploadimg, $big, $medium, $small);
					for($j=0 ; $j<count($isuploadimg); $j++){
						if(!$isuploadimg[$j]){
							$errorupload = true;
							break;
						}
					}
					if($errorupload){
						$report['is_upload']= false;
						$report["message"] = "File(s) (small/big) cannot be uploaded!";
						$report["filename"] = $file['file']['name'][$i];
					}else{
						$report['is_upload']= true;
						$report["message"] = "File(s) upload successfully!";
						$report["filename"] =$new_name;
					}
					
					
					array_push($reportwrapper , $report);
				
				}
			}
			
			$response["is_upload"] = true;
			$response["message"] = "success";
			$response["fileupload"] = $reportwrapper;
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
	
	function resizeImageFixpixel($targetfolder , $sourcefolder , $size , $quality){
	
		//$source_img = @imagecreatefromstring($sourcefolder);
		$source_img = $sourcefolder;
		$destination_img = $targetfolder;
		
		$info = getimagesizefromstring($source_img);
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
		$image = imagecreatefromstring($source_img);
		
		$white = imagecolorallocate($image_p,  255, 255, 255);
		imagefilledrectangle($image_p, 0, 0, $width, $height, $white);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		return imagejpeg($image_p, $destination_img, $quality);
	
	}
	
	function resizeImageFixpixelAndScaleCenter($targetfolder , $sourcefolder , $size , $quality){
			
		$source_img = $sourcefolder;
		$destination_img = $targetfolder;
		$info = getimagesizefromstring($source_img);
		list($width, $height) = $info;
	
		$img_x = 0;
		$img_y = 0;
		$img_w = $width;
		$img_h = $height;
	
		$new_width = $size;
		$new_height = $size;
		if($width > $height){
	
			$scale_x = ($width - $height);
			$img_x = $scale_x/2;
			$img_w = $width - $scale_x;
	
		}else{
	
			$scale_y = ($height - $width);
			$img_y = $scale_y/2;
			$img_h = $height - $scale_y;
		}
			
		// Resample
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image = imagecreatefromstring($source_img);
		
		$white = imagecolorallocate($image_p,  255, 255, 255);
		imagefilledrectangle($image_p, 0, 0, $width, $height, $white);
		imagecopyresampled($image_p, $image, 0, 0, $img_x, $img_y, $new_width, $new_height, $img_w, $img_h);
		return imagejpeg($image_p, $destination_img, $quality);
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
	
	function checkPermission( $permission ){
	
		$crash = false;
		$response = array();
		for($i=0 ; $i<count($permission) ; $i++){
			if(!$permission[$i]["is_allow"]){
				$crash = true;
				$response["error"] = true;
				$response["message"] = $permission[$i]["message"];
				break;
			}
		}
	
		if(!$crash){
			$response["error"] = false;
			$response["message"] = "Nice";
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
	

	
}
?>