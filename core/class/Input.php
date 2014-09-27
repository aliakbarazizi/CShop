<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop.model
 */
class Input
{
	private static $_types = array(
		'text'=>array('description'=>'متن','callback'=>array(self,'text')),
		'mobile'=>array('description'=>'تلفن همراه','callback'=>array(self,'text')),
		'email'=>array('description'=>'ایمیل','validate'=>array(self,'email'),'callback'=>array(self,'text')),
		'link'=>array('description'=>'لینک','callback'=>array(self,'text')),
		'textarea'=>array('description'=>'توضیح','callback'=>array(self,'textarea')),
	);
	
	
	public static function types()
	{
		return self::$_types;
	}
	public static function description($type)
	{
		return self::$_types[$type]['description'];
	}
	
	public static function addType($type,$description,$callback)
	{
		self::$_types[$type] = array('description'=>$description,'callback'=>$callback);
	}
	public static function proccess($name,$input,$value,$htmloptions=array())
	{
		if (is_callable(self::$_types[$input['type']]['callback']))
		{
			return call_user_func_array(self::$_types[$input['type']]['callback'],array($name,$input,$value,$htmloptions));
		}
		else
			return $value;
	
	}
	
	public static function addInput($name,$type,$data=array())
	{
		$input = array();
		$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->insert('input')->into(array('name','type','data'),true));
		$input['data'] = serialize($data);
		$input['name'] = $name;
		$input['type'] = $type;
		$sql->execute($input);
		return CShop::app()->getDb()->lastInsertId();
	}
	
	public static function deleteInput($id)
	{
		$input = array();
		$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->delete('input')->where('id=?'));
		$sql->execute(array($id));
	}
	
	public static function text($name,$input,$value,$htmloptions=array())
	{
		$htmloptions['placeholder'] = $input['data']['placeholder'];
		return Html::textField($name, $value,$htmloptions);
	}
	
	public static function textarea($name,$input,$value,$htmloptions=array())
	{
		$htmloptions['placeholder'] = $input['data']['placeholder'];
		return Html::textArea($name, $value,$htmloptions);
	}
	
	public static function email($input,$value)
	{
		return Validate::email($value,$input['name']);
	}
	
	public static function validate($input,$value)
	{
		$message = array();
		$name = $input['name'];
		if (isset(self::$_types[$input['type']]['validate']) && ($temp=call_user_func_array(self::$_types[$input['type']]['validate'],array($input,$value))) !==true )
		{
			$message[] = $temp;
		}
		if (isset($input['data']['validate']) && ($temp=call_user_func_array($input['data']['validate'],array($input,$value))) !==true )
		{
			$message[] = $temp;
		}
		if ($input['data']['minLenght'] && ($temp = Validate::minlength($value,$name, $input['data']['minLenght'])) !== true)
		{
			$message[] = $temp;
		}
		if ($input['data']['maxLenght'] && ($temp = Validate::maxlength($value,$name, $input['data']['maxLenght'])) !== true)
		{
			$message[] = $temp;
		}
		if ($input['data']['require'] && ($temp = Validate::notEmpty($value,$name)) !== true)
		{
			$message[] = $temp;
		}
		
		if (empty($message))
		{
			return true;
		}
		
		return $message;
	}
	
}