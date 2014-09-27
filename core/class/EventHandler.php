<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop.helper
 */
class EventHandler
{

	private $plugins = array();


	public function raise($name, $arguments=array())
	{
		$name = ( string ) $name;
		$return = null;
		foreach ( $this->getPlugins($name) as $hook )
		{
			$return = call_user_func_array($hook, $arguments);
		}
		return $return;
	}

	public function getPlugins($name)
	{
		return isset($this->plugins[$name]) ? $this->plugins[$name] : array();
	}

	public function attach($name, $callback)
	{
		$this->plugins[( string ) $name][] = $callback;
	}
}