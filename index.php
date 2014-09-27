<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 */
$config = include 'config.php';
if(empty($config))
{
	header("location: install/");
	exit;
}
require 'core/CShop.php';
Cshop::create($config)->run('index');