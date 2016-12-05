<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if ( ! function_exists('distanceFormat'))
{
	function distanceFormat( $distance ){
	
		$value_string = "";
		
		$distance = number_format((float)$distance, 2, '.', '');
		if($distance <= 1){
			
			$distance = $distance * 1000;
			$value_string = $distance." m";
		}else{
			
			$value_string = $distance." km";
		}
		return $value_string;
	
	}
}
