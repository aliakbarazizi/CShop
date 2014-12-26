<?php
/**
 * Plugin Plugin
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package components.plugin
 */
class Page extends Plugin
{
	const TYPE_LINK = 0;
	const TYPE_PAGE = 1;
	const TYPE_PAGE_CUSTOM = 2;

	
	public static function getActions()
	{
		return array('مدیریت صفحات'=>'page','صفحه جدید'=>'createpage');
	}
	
	public static function getData()
	{
		return array(
			'name'=>'مدیریت صفحه ها',
			'note'=>'مدیریت صفحه ها',
			'author'=>array(
				'name'=>'Irprog',
				'url'=>'http://irprog.com',
				'email'=>'irprog@gmail.com',
			)
		);
	}
	
	public static function getParameters()
	{
		$inputs = CShop::app()->getDb()->query(QueryBuilder::getInstance()->select()->from('input')->order('`order`'))->fetchAll();
		$range = array();
		foreach ($inputs as $input)
		{
			$range[$input['id']] = $input['name'];
		}
		
		return array(
			'server'=>array('name'=>'سرور'),
			'username'=>array('name'=>'نام کاربری'),
			'password'=>array('name'=>'کلمه عبور'),
			'usernameinput'=>array('name'=>'فیلد ورودی نام کاربری','type'=>'select','range'=>$range),
			'passwordinput'=>array('name'=>'فیلد ورودی کلمه عبور','type'=>'select','range'=>$range),
		);
	}
	
	public static function install($id)
	{
		$prefix = CShop::app()->getConfig('database');
		$prefix = $prefix['prefix'];
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'page` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(256) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  `content` text NOT NULL,
		  `order` int(11) NOT NULL,
		   PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		CShop::app()->getDb()->query($sql);
	}
	
	public static function uninstall($id)
	{
		$prefix = CShop::app()->getConfig('database');
		$prefix = $prefix['prefix'];
		$sql = 'DROP TABLE '.$prefix.'page';
		CShop::app()->getDb()->query($sql);
	}

	public function register($eventHandler)
	{
		$eventHandler->attach(Application::EVENT_PAGE, array(
			$this,
			'getPages'
		));
		CShop::app()->registerExternalAction('index', 'p', array(
			$this,
			'showPage'
		));
	}
	
	public function getPages(&$menu)
	{
		$pages = CShop::app()->getDb()->query(QueryBuilder::getInstance()->select()->from('page')->order('`order`'))->fetchAll();
		
		foreach ($pages as $page)
		{
			if ($page['type'] == self::TYPE_LINK)
				$menu[$page['name']] = $page['content'];
			else 
				$menu[$page['name']] = CShop::$baseurl . '/?p=' . $page['id'];
		}
		
	}
	
	/**
	 *
	 * @param Controller $controller
	 */
	public function actionCreatePage($controller)
	{
		$message = $controller->getUser()->message();

		if (isset($_POST['save']))
		{
			$page = $_POST['page'];
			//validate
			try
			{
				if (empty($page['name']))
					throw new Exception('لطفا یک عنوان مناسب انتخاب کنید');
					
				$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->insert('page')->into(array('name','type','`order`','content'),true));
				$sql->execute(array($page['name'],$page['type'],0,$page['content']));
					
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$controller->getUser()->message($message);
				CShop::app()->redirect(self::getActionLink('editpage', $this->id).'&pid='.CShop::app()->getDb()->lastInsertId());
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
		
		}
		else
			$page = array('name'=>'','type'=>'','order'=>'','content'=>'');
		
		
		$content = '<div class="title">صفحه جدید</div> مقادیر نوع می تواند به صورت زیر باشد <br> 0:مقدار محتوی به صورت لینک خواهد شد <br> 1:مقدار محتوی با قالب فروشگاه نمایش داده خواهد شد <br> 2:مقدار محتوی بدون قالب فروشگاه نمایش داده خواهد شد
			<div class="content">
			<form action="" method="post">
				<div class="formrow">
					<div class="label"><label for="name">نام</label></div>
					<div class="input"><input type="text" name="page[name]" id="name" value="'.$page['name'].'"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="name">نوع</label></div>
					<div class="input"><input type="text" name="page[type]" id="name" value="'.$page['type'].'"></div>
				</div>
				<div class="formrow wide">
					<div class="label"><label for="content">محتوی</label></div>
					<div class="input"><textarea name="page[content]" id="content">'.$page['content'].'</textarea></div>
				</div>
				<div class="formrow">
					<input type="submit" value="ذخیره" name="save">
				</div>
			</form>
			</div>';
		$controller->renderWithContent($content, array('message'=>$message));
		$controller->renderWithContent($content, array('message'=>$message));
	}
	
	/**
	 * 
	 * @param Controller $controller
	 */
	public function actionEditPage($controller)
	{
		
		$message = $controller->getUser()->message();
	
		if (!isset($_GET['pid'])) {
			Cshop::app()->redirect(self::getActionLink('page', $this->id));
		}
		
		$pages = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('page')->where('id = ?'));
		$pages->execute(array($_GET['pid']));
		$pages = $pages->fetch();
		
		if (!$pages) {
			Cshop::app()->redirect(self::getActionLink('page', $this->id));
		}
		
		CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) { $menu['مدیریت صفحه ها']['ویرایش صفحه'] = $_SERVER['REQUEST_URI']; });
		
		if (isset($_POST['save']))
		{
			$page = $_POST['page'];
			//validate
			try
			{
				if (empty($page['name']))
					throw new Exception('لطفا یک عنوان مناسب انتخاب کنید');
					
				$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('page')->set('name=?,type=?,content=?')->where('id=?'));
				$sql->execute(array($page['name'],$page['type'],$page['content'],$pages['id']));
					
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$controller->getUser()->message($message);
				CShop::app()->redirect($_SERVER['REQUEST_URI']);
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
				
		}
		else
			$page = $pages;
		
		$content = '<div class="title">ویراش صفحه</div> مقادیر نوع می تواند به صورت زیر باشد <br> 0:مقدار محتوی به صورت لینک خواهد شد <br> 1:مقدار محتوی با قالب فروشگاه نمایش داده خواهد شد <br> 2:مقدار محتوی بدون قالب فروشگاه نمایش داده خواهد شد
			<div class="content">
			<form action="" method="post">
				<div class="formrow">
					<div class="label"><label for="name">نام</label></div>
					<div class="input"><input type="text" name="page[name]" id="name" value="'.$page['name'].'"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="name">نوع</label></div>
					<div class="input"><input type="text" name="page[type]" id="name" value="'.$page['type'].'"></div>
				</div>
				<div class="formrow wide">
					<div class="label"><label for="content">محتوی</label></div>
					<div class="input"><textarea name="page[content]" id="content">'.$page['content'].'</textarea></div>
				</div>
				<div class="formrow">
					<input type="submit" value="ذخیره" name="save">
				</div>
			</form>
			</div>';
		$controller->renderWithContent($content, array('message'=>$message));
	}
	
	/**
	 *
	 * @param Controller $controller
	 */
	public function actionPage($controller)
	{
		$message = $controller->getUser()->message();
	
		if (isset($_POST['update']))
		{
			foreach ($_POST['order'] as $key=>$value)
			{
				$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('page')->set('`order` = ?')->where('id = ?'));
				$sql->execute(array($value,$key));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$controller->getUser()->message($message);
			CShop::app()->redirect($_SERVER['REQUEST_URI']);
		}
		elseif(isset($_POST['remove']))
		{
			foreach ($_POST['delete'] as $value)
			{
				$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->delete('page')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$controller->getUser()->message($message);
			CShop::app()->redirect($_SERVER['REQUEST_URI']);
		}
	
		$sql = CShop::app()->getDb()->query(QueryBuilder::getInstance()->select()->from('page')->order('`order`'));
	
		$content = '<div class="title">مدیریت صفحه ها</div>
						<div class="content">
						<form action="" method="post">
						<table>
						<tr>
							<th>ردیف</th>
							<th>نام</th>
							<th>ترتیب</th>
							<th>مدیریت</th>
							<th><a href="#" onclick="check(this)">انتخاب</a></th>
						</tr>';
		$i=1;
		while ($item = $sql->fetch())
		{
			$content .= '<tr>';
			$content .=  '<td>'.$i++.'</td>';
			$content .=  '<td>'.$item['name'].'</td>';
			$content .=  '<td><input type="text" name="order['.$item['id'].']" value="'.$item['order'].'"></td>';
			$content .=  '<td><a href="'.self::getActionLink('editpage', $this->id).'&pid='.$item['id'].'">ویرایش</a></td>';
			$content .=  '<td><input type="checkbox" name="delete[]" value="'.$item['id'].'"></td>';
			$content .=  '</tr>';
		}
		$content .= '</table><div style="text-align: left"><input type="submit" value="ذخیره" name="update"><input type="submit" value="حذف" name="remove"></div></form></div>';
	
		$controller->renderWithContent($content, array('message'=>$message));
	}
	
	public function showPage($pageid)
	{
		$page = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('page')->where('id = ? AND (type = ? OR type = ?)'));
		$page->execute(array($pageid,self::TYPE_PAGE,self::TYPE_PAGE_CUSTOM));
		if ($page->rowCount() != 1)
			CShop::app()->redirect(CShop::$baseurl);
		$page = $page->fetch();
		if ($page['type'] == self::TYPE_PAGE_CUSTOM)
			echo $page['content'];
		else 
		{
			CShop::app()->getController()->renderWithContent($page['content']);
		}
		CShop::app()->end();
	}
}
CShop::app()->getCache()->flush();
