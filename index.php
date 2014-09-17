<?php
$config = include 'config.php';
if(empty($config))
{
	header("location: install/");
	exit;
}
require 'core/cshop.php';
Cshop::create($config)->run('index');