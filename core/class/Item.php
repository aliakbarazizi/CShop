<?php

class Item
{
	private static $_types = array(
		'text'=>array('description'=>'متن','callback'=>array(self,'text')),
		'image'=>array('description'=>'عکس','callback'=>array(self,'image')),
		'qrcode'=>array('description'=>'QR code','callback'=>array(self,'qrcode')),
		'link'=>array('description'=>'لینک','callback'=>array(self,'link')),
		'base64image'=>array('description'=>'عکس کد شده','callback'=>array(self,'base64image')),
	);
	public static function checkHidden($type)
	{
		return isset(self::$_types[$type]['hidden']);
	}
	public static function types()
	{
		return self::$_types;
	}
	public static function description($type)
	{
		return self::$_types[$type]['description'];
	}
	
	public static function addType($type,$description,$params=array())
	{
		self::$_types[$type] = array_merge(array('description'=>$description),$params);
	}
	
	public static function proccess($type,$value)
	{
		
		if (is_callable(self::$_types[$type]['callback']))
		{
			return call_user_func_array(self::$_types[$type]['callback'],array($value));
		}
		elseif(self::checkHidden($type))
			return false;
		else
			return $value;
		
	}

	public static function text($value)
	{
		return $value;
	}
	public static function image($value)
	{
		return '<img src="'.$value.'">';
	}
	public static function qrcode($value)
	{
		return '<img src="https://chart.googleapis.com/chart?cht=qr&chs=100x100&chld=|0&chl='.$value.'">';
	}
	public static function link($value)
	{
		return '<a href="'.$value.'">'.$value.'</a>';
	}
	public static function base64image($value)
	{
		return '<img src="data:image/png;base64,'.$value.'">';
	}
}