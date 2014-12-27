<?php 
$pages = array(
		CShop::app()->systemConfig()->sitetitle => CShop::$baseurl,
		
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

<title><?php echo CShop::app()->systemConfig()->sitetitle . ' - ' . $this->pageTitle?></title>
<link rel="stylesheet" href="<?php echo Cshop::$baseurl?>/static/css/style.css" type="text/css"/>
<script type="text/javascript" src="<?php echo Cshop::$baseurl?>/static/js/script.js"></script>
<link href="<?php echo Cshop::$baseurl?>/static/css/perfect-scrollbar.css" rel="stylesheet">

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