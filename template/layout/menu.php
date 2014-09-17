<?php 
$menus = array(
	'خرید ها'=>array(
		'لیست پرداخت ها'=>'payment.php',
		//'آمار سایت'=>'statistic.php',
	),
	'دسته ها'=>array(
		'دسته جدید'=>'createcategory.php',
		'لیست دسته ها'=>'category.php',
	),
	'محصولات'=>array(
		'محصول جدید'=>'createproduct.php',
		'لیست محصولات'=>'product.php',
	),
	'کارت ها'=>array(
		'کارت جدید'=>'createitem.php',
		'لیست کارت ها'=>'item.php',
	),
	'ورودی ها'=>array(
		'فیلد جدید'=>'createinput.php',
		'لیست فیلد ها'=>'input.php',
	),
	'تنظیمات'=>array(
		'لیست درگاه ها'=>'gateway.php',
		'لیست پلاگین ها'=>'plugin.php',
		'تنظیمات'=>'setting.php',
	),
);
CShop::app()->raise(Application::EVENT_MENU, array(&$menus));
?>
<div id="sidebar">
	<div class="top">
		<div class="title">
			<a href="index.php"><img alt="" src="<?php echo CShop::$baseurl?>/static/images/home.png"></a>
			<a href="index.php?logout"><img alt="" src="<?php echo CShop::$baseurl?>/static/images/logout.png"></a>
		</div>
	</div>
	<?php foreach ($menus as $title=>$items):?>
	<div class="menu">
		<div class="title"><?php echo $title?></div>
		<div class="content">
			<?php foreach ($items as $item=>$link):?>
			<a href="<?php echo $link?>"><div class="item <?php if($_SERVER['REQUEST_URI'] == CShop::$baseurl.'/admin/'.$link) echo 'active'?>"><?php echo $item?></div></a>
			<?php endforeach;?>
		</div>
	</div>
	<?php endforeach;?>
</div>