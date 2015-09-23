<?php
$config= include '../config.php';

require '../core/CShop.php';
if(cshop::VERSION=='1.1.2')
{
	header('location: ../');
	exit;
}
require '../core/class/database.php';
require '../core/class/QueryBuilder.php';

$success=false;
if(isset($_POST['upgrade']))	
{
try {
$db = new Database(true,$config[database][database],$config[database][host],$config[database][username],$config[database][password]);
}
catch (PDOException $e)
{
	throw new Exception('خطا در اتصال به دیتابیس، متن خطا : '.$e->getMessage());
}
$querybuilder = new QueryBuilder();
$querybuilder = QueryBuilder::getInstance($config[database][prefix]);
//$query ='select class,id from '.$config[database][prefix].'plugin';
$result=$db->query($querybuilder->select()->from('plugin'));
$querybuilder->clear();
while ($plugin = $result->fetch())
{
	//echo $plugin['class'];
	//echo $plugin['id'];
	//$query='select pluginid,`key`,`value` from '.$config[database][prefix].'plugin_meta'.' where pluginid='.$plugin['id'];
	;
    $result2=$db->query($querybuilder->select()->from('plugin_meta')->where('pluginid='.$plugin['id']));
	while ($plugin_meta= $result2->fetch())
  {

  //	var_dump($plugin_meta); 
  	$pluginadd="../plugin/".$plugin['class'].".php";
  	$subject= file_get_contents($pluginadd) ;
  	//      **************************************************************
  	$pattern='/public\s*static\s*function\s*getParameters\s*\(\s*\).*?(return\s*array\s*\(.*?\)\s*;)/s';
  	$matches=array();
  	preg_match($pattern,$subject,$matches);
  	$m=eval($matches[1]);
  	//var_dump($m);
   // echo $plugin['class'];
   $querybuilder->clear();
   $insert = $db->prepare($querybuilder->insert('option')->into(array('`key`','`category`','`value`','`description`'),true,false));
   $insert->execute(array($plugin_meta[key],$plugin['class'],$plugin_meta['value'],$m[$plugin_meta[key]]['name']));
  //	$query="INSERT INTO `option`(`key`,`category`,`value`, `description`) VALUES ('$plugin_meta[key]','$plugin[class]','$plugin_meta[value]','".$m[$plugin_meta[key]]['name']."' )";		
	
 // 	$db->query($query);
  }}
    /*
     * 
     * 
     *             gateway upgrade
     * 
     * 
     * 
     */

  $QueryBuilder->clear();
    $query='select class,id from '.$config[database][prefix].'gateway';
    $result=$db->query($querybuilder->selcet()->from('gateway'));
    //$qb = QueryBuilder::getInstance($config[database][prefix]);
    while ($gateway = $result->fetch())
    {
    	//echo $plugin['class'];
    	//echo $plugin['id'];
    	$querybuilder->clear();
    //	$query='select gatewayid,`key`,`value` from '.$config[database][prefix].'gateway_meta'.' where gatewayid='.$gateway['id'];
    	$result2=$db->query($querybuilder->select()->from('gateway_meta')->where('gatewayid='.$gateway['id']));
    	while ($gateway_meta= $result2->fetch())
    	{
    
    		//	var_dump($plugin_meta);
    		$gatewayadd="../plugin/".$gateway['class'].".php";
    		$subject= file_get_contents($gatewayadd);
    		//      **************************************************************
    		$pattern='/public\s*static\s*function\s*getParameters\s*\(\s*\).*?(return\s*array\s*\(.*?\)\s*;)/s';
    		$matches=array();
    		preg_match($pattern,$subject,$matches);
    		$m=eval($matches[1]);
    		//var_dump($m);
    		// echo $plugin['class'];
    		 // $query="INSERT INTO `option`(`key`,`category`,`value`, `description`) VALUES ('$gateway_meta[key]','$gateway[class]','$gateway_meta[value]','".$m[$gateway_meta[key]]['name']."' )";
				$insert=$db->prepare($querybuilder->insert(`option`)->into(array('`key`','`category`','`value`','`description`'),true,false));
				$insert->execute(array($gateway_meta[key],$gateway['class'],$gateway_meta[value],$m[$gateway_meta[key]]['name']));	
    		  $db->query($query);
    		 
    		
  } 
}


$success=true;


}

?>

<html>
<head>
<meta charset="utf-8"> 
<title>ارتقا cshop</title>
<link rel="stylesheet" href="../static/css/admin.css">
</head>
<body>
<div id="header">
	<div class="inner-header">
		<div class="toplogo" onclick="" style="cursor:pointer;">
		</div>
		<div id ="topmenu">
			<div class="menu">
				<div class="menu-main-container">
					<ul id="menu-main" class="menu">
						<li class="menu-item "><a href="#">ارتقا cshop</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div> 
</div>
<?php 
if(!$success):

?>
<form method='POST' action=''>
<center>
<p> جهت ارتقا cshop بر روی دکمه ارتقا کلیک کنید </p>
<input type='submit' value='ارتقا' name='upgrade'>
</center>

</form>
</body>
</html>
<?php 
endif ;
?>