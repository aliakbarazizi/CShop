<?php
abstract class PluginBase
{
	public function __get($name)
	{
		return $this->$name = self::getDataByName($name);
	}
	public static function getDataByName($name)
	{
		$r = static::getData();
		return $r[$name];
	}
	public static function getData()
	{
		return array(
				'name'=>'Plugin',
				'note'=>'This is abstract of Plugin',
				'author'=>array(
						'name'=>'Ir-prog',
						'url'=>'http://ir-prog.ir',
						'email'=>'admin@ir-prog.ir',
				)
		);
	}
	
	public static function getParameterByName($name)
	{
		$r = static::getParameters();
		return $r[$name]['name'];
	}
	
	public static function getParameters()
	{
		return array();
	}
	
	public static function getActions()
	{
		return array();
	}
	
	public static function saveMeta($id,$meta=array())
	{
		foreach ($meta as $key=>$value)
		{
			$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('plugin_meta')->set('value=?')->where('`key`=? AND pluginid = ?'));
			$sql->execute(array($value,$key,$id));
		}
	}
	
	public static function loadPlugin($id)
	{
		$sql = 	CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->select('*')->from('plugin')->leftJoin('plugin_meta')->on('pluginid = plugin.id')->where('plugin.id = ?'));
		$sql->execute(array($id));
		$sql = $sql->fetchAll();
		$class = $sql[0]['class'];
		return new $class($sql);
	}
	
	public static function proccessInput($parameter,$name,$value='',$htmloptions=array())
	{
		$r = static::getParameters();
		$type = isset($r[$parameter]['type']) ? $r[$parameter]['type'] : 'text';
		switch ($type)
		{
			case 'text':
				return Html::textField($name, $value,$htmloptions);
			case 'password':
				return Html::passwordField($name, $value,$htmloptions);
			case 'textarea':
				return Html::textArea($name, $value, $htmloptions);
			case 'select':
			{
					$content = '';
					if(is_array($r[$parameter]['range']))
					{
						foreach ($r[$parameter]['range'] as $range=>$key)
						{
							$content .= Html::optionList($key, $range, $value == $range);
						}
					}
					return Html::selectList($name, $content, $htmloptions);
				}
			case 'checkbox':
				return Html::checkBox($name, 1,1==$value);
			/* case 'radio':
				{
					$selected = isset($selected)? $selected : 'selected';
					$return = '';
					if(is_array($r[$parameter]['range']))
					{
						foreach ($r[$parameter]['range'] as $range=>$key)
						{
							if($value == $range)
								$return .= "$key <input type='$type' name='$name' value='$value' $htmloption $selected>";
							else
								$return .= "$key <input type='$type' name='$name' value='$value' $htmloption>";
								
						}
					}
					return $return;
				} */
		}
	}
	
	protected $id;
	
	public function __construct($setting=array())
	{
		$this->init($setting);
	}
	
	public function init($setting=array())
	{
		if(!is_array($setting))
			return;
		foreach ($setting as $name)
		{
			$this->{$name['key']} = $name['value'];
		}
	}
	
}