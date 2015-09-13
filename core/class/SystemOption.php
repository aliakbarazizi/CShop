<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop.base
 * 
 * @property string $sitetitle
 * @property string $sitedescription
 * @property string $adminemail
 * @property string $applicationstatus
 * @property string $timeformat
 * @property string $pagelimit
 */
class SystemOption implements Iterator
{
	private $_config = array(
		'sitetitle'=>array('value'=>'فروشگاه سی شاپ','description'=>'عنوان سایت'),
		'sitedescription'=>array('value'=>'سیستم جامع فروشگاهی سی شاپ','description'=>'توضیحات سایت'),
		'adminemail'=>array('value'=>'admin@site.com','description'=>'ایمیل مدیر'),
		'reservetime'=>array('value'=>'20','description'=>'زمان رزو کارت به دقیقه'),
		'timeformat'=>array('value'=>'Y/m/d H:i:s','description'=>'فرمت زمان'),
		'pagelimit'=>array('value'=>'10','description'=>'محدودیت نمایش در در هر صفحه'),
	);
	
	public function __construct($config)
	{
		$this->_config = array_merge($this->_config,$config);
	}
	
	public function __get($key)
	{
		return $this->_config[$key]['value'];
	}
	
	public function description($key)
	{
		return $this->_config[$key]['description'];
	}

	public function current()
	{
		return current($this->_config);
	}
	
	public function key()
	{
		return key($this->_config);
	}
	
	public function next()
	{
		next($this->_config);
	}
	
	
	public function rewind()
	{
		reset($this->_config);
	}

	public function valid()
	{
		return current($this->_config)!==false ? true : false;
	}
}

