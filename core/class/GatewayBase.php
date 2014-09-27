<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop.base
 *
 * @property string name
 * @property string note
 * @property array author
 */
abstract class GatewayBase extends PluginBase
{
	/**
	 * @param array $payment
	 * @param string $callback
	 * @return array $error
	 */
	public abstract function sendToGateway($payment,$callback);
	
	/**
	 * @throws Exception string error
	 * @return array $payment
	 */
	public abstract function callbackGateway();
}