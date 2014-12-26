<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop.base
 */
abstract class Plugin extends PluginBase
{
	/**
	 * 
	 * @param EventHandler $eventHandler
	 */
	public abstract function register($eventHandler);
	
}