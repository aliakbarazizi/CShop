<?php
$success = false;
$config = include '../config.php';
if(!empty($config))
{
	header('location: ../');
	exit;
}
if (isset($_POST['save']))
{
	try {
		error_reporting(E_ALL & ~E_NOTICE);
		if (!$_POST['siteurl'] || !$_POST['username'] || !$_POST['password'] || !$_POST['email'] || !$_POST['dbserver'] || !$_POST['dbusername'] || !$_POST['dbdatabase'] )
			throw new Exception("تمام فیلد ها را کامل کنید");
		$_POST['dbprefix'] = trim($_POST['dbprefix']);
		if (!is_writable('../config.php'))
		{
			throw new Exception('فایل config.php قابل نوشتن نیست');
		}
		$url = parse_url($_POST['siteurl']);
		require '../core/class/Database.php';
		try {
			$db = new Database(true,$_POST['dbdatabase'],$_POST['dbserver'],$_POST['dbusername'],$_POST['dbpassword']);
		}
		catch (PDOException $e)
		{
			throw new Exception('خطا در اتصال به دیتابیس، متن خطا : '.$e->getMessage());
		}
		
		$params = array(
			'sitepath'=>rtrim($url['path'],'/'),
			'dbserver'=>$_POST['dbserver'],
			'dbdatabase'=>$_POST['dbdatabase'],
			'dbusername'=>$_POST['dbusername'],
			'dbpassword'=>$_POST['dbpassword'],
			'dbprefix'=>$_POST['dbprefix'],
		);
		$config = include 'config.php';
		foreach ($params as $key=>$value)
		{
			$config = str_replace('{'.$key.'}', $value, $config);
		}
		file_put_contents('../config.php', $config);
		$success = true;
		$message = 'اسکریپت با موفقیت نصب شد. میتوانید پوشه install را پاک کنید<br>لینک مدیریت : <a href="../admin">کلیک کنید</a>';
		
		try {
			$sqls = file_get_contents('database.sql');
			$sqls = str_replace('@{prefix}@', $_POST['dbprefix'], $sqls);
			foreach (explode(';', $sqls) as $sql)
				if($sql = trim($sql))
					$db->exec($sql);
			$sql = "INSERT INTO `{$_POST['dbprefix']}admin` ( `username`, `password`, `email`) VALUES (?, ?,?);";
			$sql = $db->prepare($sql);
			$sql->execute(array($_POST['username'],crypt($_POST['password']),$_POST['email']));
			
		}
		catch (PDOException $e)
		{
			throw new Exception('خطا در ساخت دیتابیس، متن خطا : '.$e->getMessage());
		}
	}
	catch (Exception $e)
	{
		$message = $e->getMessage();
	}
}
else
{
	$_POST['dbserver'] = 'localhost';
	$_POST['dbprefix'] = 'cshop_';
	$_POST['siteurl'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://"  . $_SERVER['HTTP_HOST'] . reset((explode('?', $_SERVER['REQUEST_URI'])));
	$_POST['siteurl'] = str_replace('install', '', trim($_POST['siteurl'],'/'));
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>نصب CShop</title>
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
						<li class="menu-item "><a href="#">نصب CShop</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div> 
</div>
<div id="content">
	<?php if (isset($message)):?>
	<div id="message"><font color="red"><?php echo $message?></font></div>
	<?php endif;?>
	<?php if(!$success):?>
	<div id="wrapper">
		<div id="main" class="admin">
			<div class="title">نصب اسکریپت</div>
			<div class="content">
			<form action="" method="post">
				<div style="font-weight: bold;">فروشگاه</div>
				<hr>
				<div class="formrow">
					<div class="label"><label for="siteurl">آدرس فروشگاه</label></div>
					<div class="input"><input dir="ltr" type="text" name="siteurl" id="siteurl" value="<?php echo $_POST['siteurl']?>"></div>
					<div class="formrow">
						<div class="label"><label for="username">نام کاربری مدیر</label></div>
						<div class="input"><input dir="ltr" type="text" name="username" id="username" value="<?php echo $_POST['username']?>"></div>
					</div>
					<div class="formrow">
						<div class="label"><label for="password">کلمه عبور مدیر</label></div>
						<div class="input"><input dir="ltr" type="text" name="password" id="password" value="<?php echo $_POST['password']?>"></div>
					</div>
					<div class="formrow">
						<div class="label"><label for="email">ایمیل مدیر</label></div>
						<div class="input"><input dir="ltr" type="text" name="email" id="email" value="<?php echo $_POST['email']?>"></div>
					</div>	
				</div>
				<div style="font-weight: bold;">پایگاه داده</div>
				<hr>
				<div class="formrow">
					<div class="label"><label for="dbserver">سرور پایگاه داده</label></div>
					<div class="input"><input dir="ltr" type="text" name="dbserver" id="dbserver" value="<?php echo $_POST['dbserver']?>"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="dbdatabase">نام پایگاه داده</label></div>
					<div class="input"><input dir="ltr" type="text" name="dbdatabase" id="dbdatabase" value="<?php echo $_POST['dbdatabase']?>"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="dbusername">نام کاربری پایگاه داده</label></div>
					<div class="input"><input dir="ltr" type="text" name="dbusername" id="dbusername" value="<?php echo $_POST['dbusername']?>"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="dbpassword">کلمه عبور پایگاه داده</label></div>
					<div class="input"><input dir="ltr" type="text" name="dbpassword" id="dbpassword" value="<?php echo $_POST['dbpassword']?>"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="dbprefix">پیشوند جدول ها</label></div>
					<div class="input"><input dir="ltr" type="text" name="dbprefix" id="dbprefix" value="<?php echo $_POST['dbprefix']?>"></div>
				</div>
				<div class="formrow">
					<input type="submit" value="ذخیره" name="save">
				</div>
			</form>
			</div>
		</div>
	</div>
	<?php endif;?>
	<div class="clear"></div>
</div>
<div id="footer"><a href="http://ir-prog.ir" target="_blank">Cshop</a></div>
</body>
</html>
