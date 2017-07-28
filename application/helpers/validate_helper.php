<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('validateNumeric'))
{
	function validateNumeric( $param){
		if($param != null && $param > 0){
			return true;
		}
		return false;
	}
}

if ( ! function_exists('validateString'))
{
	function validateString( $param ){
		if($param != null){
			return true;
		}
		return false;
	}
}


if ( ! function_exists('IsNullOrEmptyString'))
{
	function IsNullOrEmptyString($variable){
		return (!isset($variable) || trim($variable)==='');
	}
}