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
		
			$allowfiletype = $this->allowImageType(array("image/jpg","image/jpeg", "image/gif", "image/png"), $file['file']['type']);
			$allowsize = $this->allowImageSize(10240 , 20000000, $file["file"]["size"]);//20MB
			//$allowmindimension = $this->allowImageMinimumDimension(500, 300, $file["file"]["tmp_name"]);
			//$allowmaxdimension = $this->allowImageMaximumDimension(8000, 5000, $file["file"]["tmp_name"]);
		
			$permission = array();
			array_push($permission ,
			$checkdirectory_small,
			$checkdirectory_medium,
			$checkdirectory_big,			
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
				$big = $this->resizeImageFixpixel($target_big_dir.$new_name, $file ["file"]["tmp_name"], $imgsize, 80);
				$medium = $this->resizeImageFixpixel($target_medium_dir.$new_name, $file ["file"]["tmp_name"] , 180 , 80);									
				$small = $this->resizeImageFixpixel($target_small_dir.$new_name, $file ["file"]["tmp_name"], 50, 80);
				
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
		//$json = json_encode($response);
		return $response;
	}
	
	function uploadMutilplePostImages( $request , $mainfolder){
		
		$response = array();
		
		$file = $request["image_file"];
		if ( ! empty($file))
		{
				
			/* $target_extreme_small_dir = "./uploadimages/shopimages/extreme_small/"; */
			$target_small_dir = $mainfolder."post/small/";
			$target_medium_dir = $mainfolder."post/medium/";
			$target_big_dir = $mainfolder."post/big/";
				
			$reportwrapper = array();
			
			$number_of_files = count($file['file']['name']);
			
			for ($i = 0; $i < $number_of_files; $i++){
		
				$report = array();
				$this->load->helper('dernhamutils');
				$new_name = generateRandomString(10).".jpg";
		
				/* $checkdirectory_extreme_small = $this->checkDirectory($target_extreme_small_dir); */
				$checkdirectory_small = $this->checkDirectory($target_small_dir);
				$checkdirectory_medium = $this->checkDirectory($target_medium_dir);
				$checkdirectory_big = $this->checkDirectory($target_big_dir);
				$allowfiletype = $this->allowImageType(array("image/jpeg", "image/gif", "image/png"), $file['file']['type'][$i]);
				$allowsize = $this->allowImageSize(5120 , 20000000, $file["file"]["size"][$i]);//20MB
				$allowmindimension = $this->allowImageMinimumDimension(200, 200, $file["file"]["tmp_name"][$i]);
				$allowmaxdimension = $this->allowImageMaximumDimension(8000, 5000, $file["file"]["tmp_name"][$i]);
		
				$permission = array();
				array_push($permission ,
				/* $checkdirectory_extreme_small, */
				$checkdirectory_small,
				$checkdirectory_medium,
				$checkdirectory_big,
				$allowfiletype,
				$allowsize,
				$allowmindimension,
				$allowmaxdimension
				);
				$check = $this->checkPermission($permission);
		
				$message = $check["message"];
				$uploadok =  $check["error"];
				if ($uploadok) {
						
					$report['is_upload']= false;
					$report["message"] = "File(s) cannot be uploaded.".$message;
					$report["filename"] = $file['file']['name'][$i];
					array_push($reportwrapper , $report);
						
				} else {
		
					$isuploadimg = array();
					/* $big = $this->resizeImage($target_big_dir.$new_name,$_FILES["file"]["tmp_name"][$i],0.4,50);
					 $small = $this->resizeImage($target_small_dir.$new_name,$_FILES["file"]["tmp_name"][$i],0.2,50); */
						
						
					$info = getimagesize($file["file"]["tmp_name"][$i]);
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
						
						
					$big = $this->resizeImageFixpixel($target_big_dir.$new_name, $file["file"]["tmp_name"][$i] , $my_img_size, 80);
					$medium = $this->resizeImageFixpixel($target_medium_dir.$new_name, $file["file"]["tmp_name"][$i] , $my_img_medium_size, 80);		
					$small = $this->resizeImageFixpixelAndScaleCenter($target_small_dir.$new_name, $file["file"]["tmp_name"][$i] , 180, 80);
					//	$small = $this->resizeImageFixpixel($target_small_dir.$new_name, $_FILES["file"]["tmp_name"][$i] , 180, 80);
					//	$extreme_small = $this->resizeImageFixpixel($target_extreme_small_dir.$new_name, $_FILES["file"]["tmp_name"][$i] , 160, 80);
						
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
	
	function allowImageMinimumDimension( $minwidth , $minheight , $file){
	
		$response = array();
		$info = getimagesize($file);
		list($width, $height) = $info;
		if($width < $minwidth || $height < $minheight){
			$response['message'] = "The file you are attempting to upload doesn't fit into the allowed dimension!";
			$response['is_allow'] = false;
			return $response;
		}
		$response['message'] = "";
		$response['is_allow'] = true;
		return $response;
	
	}
	
	function allowImageMaximumDimension( $maxwidth , $maxheight , $file){
	
		$response = array();
		$info = getimagesize($file);
		list($width, $height) = $info;
		if($width > $maxwidth || $height > $maxheight){
			$response['message'] = "The file you are attempting to upload doesn't fit into the allowed dimension!";
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
	
	function resizeImageFixpixelAndScaleCenter($targetfolder , $sourcefolder , $size , $quality){
	
	
		$source_img = $sourcefolder;
		$destination_img = $targetfolder;
		$info = getimagesize($source_img);
		list($width, $height) = $info;
	
		$img_x = 0;
		$img_y = 0;
		$img_w = $width;
		$img_h = $height;
	
		$new_width = $size;
		$new_height = $size;
		if($width > $height){
	
			/* // the lowest will have size as value
			 $percentzoomheight = ($size * 100)/$height;
			$convertwidthpx = ($width * $percentzoomheight)/100;
			$new_width = $convertwidthpx;
			$new_height = $size; */
	
			$scale_x = ($width - $height);
			$img_x = $scale_x/2;
			$img_w = $width - $scale_x;
	
		}else{
	
			/* $percentzoomwidth = ($size * 100)/$width;
			 $convertheightpx = ($height * $percentzoomwidth)/100;
			$new_height = $convertheightpx;
			$new_width = $size; */
			$scale_y = ($height - $width);
			$img_y = $scale_y/2;
			$img_h = $height - $scale_y;
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
		imagecopyresampled($image_p, $image, 0, 0, $img_x, $img_y, $new_width, $new_height, $img_w, $img_h);
		return imagejpeg($image_p, $destination_img, $quality);
	}
	
}
?>