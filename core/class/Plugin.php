<?php
abstract class Plugin extends PluginBase
{
	public abstract function register($eventHandler);
	
	public static function install($id)
	{
		
	}
	
	public static function uninstall($id)
	{
		
	}
}