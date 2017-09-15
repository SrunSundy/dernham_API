<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if ( ! function_exists('substractCurrentTime'))
{
	function substractCurrentTime($timezone , $value){
	
		$now =new DateTime($timezone);
		$now =  $now->format('H:i:s');
		$now = new DateTime($now);
		
		$shoptime = new DateTime($value);
		$interval = $shoptime->diff($now);
		return $interval->format('%H:%I:%S');
	
	}
}
if ( ! function_exists('substractTime'))
{
	function substractTime($value1 , $value2){
	
		$shoptime1 = new DateTime($value1);
		$shoptime2 = new DateTime($value2);
		$interval = $shoptime2->diff($shoptime1);
		return $interval->format('%H:%I:%S');
	
	}
}

if ( ! function_exists('addTime'))
{
	function addTime($time1, $time2) {
		$times = array($time1, $time2);
		$seconds = 0;
		foreach ($times as $time)
		{
			list($hour,$minute,$second) = explode(':', $time);
			$seconds += $hour*3600;
			$seconds += $minute*60;
			$seconds += $second;
		}
		return $seconds * 1000;
	}
}

if ( ! function_exists('covertToMilisecond'))
{
	function covertToMilisecond($time){
	
		$seconds = 0;
		list($hour,$minute,$second) = explode(':', $time);
		$seconds += $hour*3600;
		$seconds += $minute*60;
		$seconds += $second;
		return $seconds * 1000;
	
	}
}

if ( ! function_exists('tz'))
{
    function tz($time , $zonename ){
        
        if(!$zonename || !isset($zonename)) $zonename = "Asia/Phnom_Penh";
        
        $created_date = new DateTime($time);
        $created_date->setTimezone(new DateTimeZone($zonename));
        $created_date = $created_date->format('Y-m-d H:i:s'); ;
        return $created_date;
        
    }
}

	
	
	
	
	