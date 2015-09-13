<?php 

$pages = array(
		CShop::app()->systemOption()->sitetitle => CShop::$baseurl,
		
);
CShop::app()->raise(Application::EVENT_PAGE, array(&$pages));
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">

<meta content="no-cache" http-equiv="Pragma"></meta>
<meta content="no-cache, no-store, must-revalidate" http-equiv="Cache-Control"></meta>
<meta content="0" http-equiv="Expires"></meta>

<title><?php echo CShop::app()->systemOption()->sitetitle . ' - ' . $this->pageTitle?></title>
<?php 
/*		
 
  minify  js  for site 
  
 */ 

$base=Cshop::$rootpath ;
$filename=  $base."/static/js/final.js";
if(!file_exists($filename))
{


$filename=  $base."/static/js/jquery.js";
$js= file_get_contents($filename);
$filename=  $base."/static/js/jquery-ui.js";
$js.= file_get_contents($filename);
$filename=  $base."/static/js/jquery.mousewheel.js";
$js.= file_get_contents($filename);
$filename=  $base."/static/js/perfect-scrollbar.js";
$js.= file_get_contents($filename);
$filename=  $base."/static/js/jquery.placeholder.js";
$js.= file_get_contents($filename);
$filename=  $base."/static/js/jquery.noty.packaged.min.js";
$js.= file_get_contents($filename);
$filename=  $base."/static/js/script.js";
$js.= file_get_contents($filename);

$js=JSMin::minify($js);
$destination=$base;
$destination.='/static/js/';
$name='final.js';

file_put_contents($destination.$name,$js);
}
/*

 minify  css for site

*/

$base=Cshop::$rootpath ;
$filename=  $base."/static/css/final.css";
if(!file_exists($filename))
{


	$filename=  $base."/static/css/style.css";
	$css= file_get_contents($filename);
	$filename=  $base."/static/css/perfect-scrollbar.css";
	$css.= file_get_contents($filename);


	$minify = new CSSmin();
	$css=$minify->run($css,true);
	$destination=$base;
	$destination.='/static/css/';
	$name='final.css';

	file_put_contents($destination.$name,$css);
}
?>
<link rel="stylesheet" href="<?php echo Cshop::$baseurl?>/static/css/final.css" type="text/css"/>
<script type="text/javascript" src="<?php echo Cshop::$baseurl?>/static/js/final.js"></script>


</head>
<body>
<div class="menu">
	<div class="wrp">
		<div class="logo">
			ONLINE CSHOP<span>ONLINE PAYMENT STORE</span>
		</div>
		<a class="home" href="<?php echo CShop::$baseurl?>"></a>
		
		<?php foreach ($pages as $name=>$link):?>
		<a href="<?php echo $link?>" class="<?php if(rtrim($_SERVER['REQUEST_URI'],'/') == $link) echo 'active'?>"><?php echo $name?></a>
		<?php endforeach;?>
	</div>
</div>
	<?php echo $content?>
</body>
</html>