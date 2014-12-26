<?php 
$menus = array(
	'خرید ها'=>array(
		'لیست پرداخت ها'=>CShop::$baseurl.'/admin/'.'payment.php',
		//'آمار سایت'=>'statistic.php',
	),
	'دسته ها'=>array(
		'دسته جدید'=>CShop::$baseurl.'/admin/'.'createcategory.php',
		'لیست دسته ها'=>CShop::$baseurl.'/admin/'.'category.php',
	),
	'محصولات'=>array(
		'محصول جدید'=>CShop::$baseurl.'/admin/'.'createproduct.php',
		'لیست محصولات'=>CShop::$baseurl.'/admin/'.'product.php',
	),
	'کارت ها'=>array(
		'کارت جدید'=>CShop::$baseurl.'/admin/'.'createitem.php',
		'لیست کارت ها'=>CShop::$baseurl.'/admin/'.'item.php',
	),
	'ورودی ها'=>array(
		'فیلد جدید'=>CShop::$baseurl.'/admin/'.'createinput.php',
		'لیست فیلد ها'=>CShop::$baseurl.'/admin/'.'input.php',
	),
	'تنظیمات'=>array(
		'لیست درگاه ها'=>CShop::$baseurl.'/admin/'.'gateway.php',
		'لیست پلاگین ها'=>CShop::$baseurl.'/admin/'.'plugin.php',
		'تنظیمات'=>CShop::$baseurl.'/admin/'.'setting.php',
	),
);
CShop::app()->raise(Application::EVENT_MENU, array(&$menus));
?>
<div id="sidebar">
	<div class="top">
		<div class="title">
			<a href="index.php"><img alt="" src="<?php echo CShop::$baseurl?>/static/images/main.png"></a>
			<a href="index.php?logout"><img alt="" src="<?php echo CShop::$baseurl?>/static/images/logout.png"></a>
		</div>
	</div>
	<?php foreach ($menus as $title=>$items):?>
	<div class="menu">
		<div class="title"><?php echo $title?></div>
		<div class="content">
			<?php foreach ($items as $item=>$link):?>
			<a href="<?php echo $link?>"><div class="item <?php if($_SERVER['REQUEST_URI'] == $link) echo 'active'?>"><?php echo $item?></div></a>
			<?php endforeach;?>
		</div>
	</div>
	<?php endforeach;?>
</div>