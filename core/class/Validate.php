<?php

class Validate
{
	public static function valid($value,$name,$type)
	{
	}
	
	public static function email($value,$name)
	{
		return empty($value) || filter_var($value,FILTER_VALIDATE_EMAIL) ? true : $name.'یک ایمیل معتبر نیست ';
	}
	
	public static function number($value,$name)
	{
		return is_numeric($value);
	}
	
	public static function ip($value,$name)
	{
		return filter_var($value,FILTER_VALIDATE_IP);
	}
	
	public static function notEmpty($value,$name)
	{
		return !empty($value) ? true : $name.' نمیتواند خالی باشد';
	}
	
	public static function length($value,$name,$min=false,$max=false)
	{
		$len = strlen($value);
		if (($min===false || $len>=$min) && ($max===false || $len <= $max))
		{
			return true;
		}
		return 'طول وارد شده نامناسب است';
	}
	public static function minlength($value,$name,$min)
	{
		$len = strlen($value);
		if (empty($value) || ($min===false || $len>=$min))
		{
			return true;
		}
		return 'طول وارد شده کمتر از حدمجاز است';
	}
	public static function maxlength($value,$name,$max)
	{
		$len = strlen($value);
		if (empty($value) || ($max===false || $len <= $max))
		{
			return true;
		}
		return 'طول وارد شده بیشتر از حدمجاز است';
	}
}