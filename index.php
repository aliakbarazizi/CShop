<?php
$config = include 'config.php';
if(empty($config))
{
	header("location: install/");
	exit;
}
require 'core/CShop.php';
Cshop::create($config)->run('index');