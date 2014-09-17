<?php
$config = include '../config.php';

require '../core/cshop.php';
Cshop::create($config)->run(array('admin','createinput'));