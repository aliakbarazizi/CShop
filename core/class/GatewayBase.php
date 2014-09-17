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
	public abstract function sendToGateway($payment,$callback);
	
	public abstract function callbackGateway();
}