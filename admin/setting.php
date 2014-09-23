<?php
$config = include '../config.php';

require '../core/CShop.php';
Cshop::create($config)->run(array('admin','setting'));