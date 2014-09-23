<?php
/**
 * 
 * @property string name
 * @property string note
 * @property array author
 *
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