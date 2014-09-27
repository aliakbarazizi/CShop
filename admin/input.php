<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 */
$config = include '../config.php';

require '../core/CShop.php';
Cshop::create($config)->run(array('admin','input'));