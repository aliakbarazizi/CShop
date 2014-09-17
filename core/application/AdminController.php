<?php 
class AdminController extends BaseController
{
	public $layout = 'layout/admin';
	
	public $pageTitle = 'مدیریت';
	
	protected $userType = 'admin';
	
	/**
	 * 
	 * @var Pagination
	 */
	public $pagination;
	
	public function init()
	{
		parent::init();
		$this->pagination = new Pagination();
	}
	
	public function beforeAction($action)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
			CShop::app()->getCache()->flush();
		if ($action != 'login')
			$this->user->isLogin('login.php');
		elseif ($this->user->isLogin())
			CShop::app()->redirect('index.php');
		return parent::beforeAction($action);
	}
	
	public function actionIndex()
	{
		if (isset($_GET['logout']))
		{
			$this->user->logout();
			CShop::app()->redirect('login.php');
		}
		$this->render('admin/index');
	}
	
	public function actionLogin()
	{
		$message = '';
		if (isset($_POST['submit']))
		{
			$sql = $this->db->prepare(QueryBuilder::getInstance()->select()->from('admin')->where('username=?'));
			$sql->execute(array($_POST['username']));
			
			$row = $sql->fetch();
			
			if ($row && $row['password'] == crypt($_POST['password'],$row['password']))
			{
				$this->user->login($row['id']);
				CShop::app()->redirect('index.php');
			}
			else 
				$message = 'نام کاربری/کلمه عبور اشتباه است';
		}
		$this->layout = false;
		
		$this->render('admin/login',array('message'=>$message));
	}
	
	public function actionInput()
	{
		$message = $this->user->message();
	
		if (isset($_POST['update']))
		{
			foreach ($_POST['order'] as $key=>$value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('input')->set('`order` = ?')->where('id = ?'));
				$sql->execute(array($value,$key));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('input.php');
		}
		elseif(isset($_POST['remove']))
		{
			foreach ($_POST['delete'] as $value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('input')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('input.php');
		}
	
		$inputs = new Model($this->db->query(QueryBuilder::getInstance()->select()->from('input')->order('`order`')));
		$this->render('admin/input', array('message'=>$message,'items'=>$inputs));
	
	}
	
	public function actionEditInput()
	{
		$message = $this->user->message();
	
	
		if (!isset($_GET['id'])) {
			Cshop::app()->redirect('input.php');
		}
		$inputs = $this->db->prepare(QueryBuilder::getInstance()->select()->from('input')->where('id = ?'));
		$inputs->execute(array($_GET['id']));
		$inputs = $inputs->fetch();
	
		if (!$inputs) {
			Cshop::app()->redirect('input.php');
		}
	
		CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) { $menu['ورودی ها']['ویرایش فیلد'] =  'editinput.php?id='.$_GET['id']; });
	
		if (isset($_POST['save']))
		{
			$input = $_POST['input'];
			//validate
			try
			{
				if (empty($input['name']))
					throw new Exception('لطفا یک عنوان مناسب انتخاب کنید');
					
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('input')->set('name=?,type=?,data=?')->where('id=?'));
				$input['data'] = serialize($input['data']);
				$sql->execute(array($input['name'],$input['type'],$input['data'],$inputs['id']));
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$this->user->message($message);
				CShop::app()->redirect('editinput.php?id='.$inputs['id']);
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
				
		}
		else
			$input = $inputs;
		$input['data'] = unserialize($input['data']);
		$this->render('admin/inputform', array('message'=>$message,'item'=>$input));
	}
	
	public function actionCreateInput()
	{
		$message = $this->user->message();
	
		if (isset($_POST['save']))
		{
			$input= $_POST['input'];
				
			try
			{
				if (empty($input['name']))
					throw new Exception('لطفا یک عنوان مناسب انتخاب کنید');
				$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('input')->into(array('name','type','data'),true));
				$input['data'] = serialize($input['data']);
				$sql->execute($input);
	
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$this->user->message($message);
				CShop::app()->redirect('editinput.php?id='.$this->db->lastInsertId());
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
				
		}
		else
		{
			$input = array('name'=>'','description'=>'');
		}
	
		$this->render('admin/inputform', array('message'=>$message,'item'=>$input));
	}
	public function actionCategory()
	{
		$message = $this->user->message();
		
		if (isset($_POST['update']))
		{
			foreach ($_POST['order'] as $key=>$value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('category')->set('`order` = ?')->where('id = ?'));
				$sql->execute(array($value,$key));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('category.php');
		}
		elseif(isset($_POST['remove'])) 
		{
			foreach ($_POST['delete'] as $value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('category')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('category.php');
		}
		$this->pagination->getCount($this->db->query(QueryBuilder::getInstance()->count()->from('category'))->fetch());
		$categories = new Model($this->db->query(QueryBuilder::getInstance()->select()->from('category')->order('`order`')->limit($this->pagination)));
		$this->render('admin/category', array('message'=>$message,'items'=>$categories));
		
	}

	public function actionEditCategory()
	{
		$message = $this->user->message();
		
		
		if (!isset($_GET['id'])) {
			Cshop::app()->redirect('category.php');
		}
		$categorys = $this->db->prepare(QueryBuilder::getInstance()->select()->from('category')->where('id = ?'));
		$categorys->execute(array($_GET['id']));
		$categorys = $categorys->fetch();
		
		if (!$categorys) {
			Cshop::app()->redirect('category.php');
		}
		
		CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) { $menu['دسته ها']['ویرایش دسته'] =  'editcategory.php?id='.$_GET['id']; });
		
		if (isset($_POST['save']))
		{
			$category = $_POST['category'];
			//validate
			try
			{
				if (empty($category['name']))
					throw new Exception('لطفا یک عنوان مناسب انتخاب کنید');
			
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('category')->set('name=?,description=?')->where('id=?'));
				$sql->execute(array($category['name'],$category['description'],$categorys['id']));
			
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$this->user->message($message);
				CShop::app()->redirect('editcategory.php?id='.$categorys['id']);
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
			
		}
		else
			$category = $categorys;
		
		$this->render('admin/categoryform', array('message'=>$message,'item'=>$category));
	}
	
	public function actionCreateCategory()
	{
		$message = $this->user->message();
	
		if (isset($_POST['save']))
		{
			$category = $_POST['category'];
			
			try
			{
				if (empty($category['name']))
					throw new Exception('لطفا یک عنوان مناسب انتخاب کنید');
				
				$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('category')->into(array('name','description'),true));
					
				$sql->execute($category);
				
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$this->user->message($message);
				CShop::app()->redirect('editcategory.php?id='.$this->db->lastInsertId());
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
			
		}
		else
		{
			$category = array('name'=>'','description'=>'');
		}
	
		$this->render('admin/categoryform', array('message'=>$message,'item'=>$category));
	}
	
	public function actionProduct()
	{
		$message = $this->user->message();
	
		if (isset($_POST['update']))
		{
			foreach ($_POST['order'] as $key=>$value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('product')->set('`order` = ?')->where('id = ?'));
				$sql->execute(array($value,$key));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('product.php');
		}
		elseif(isset($_POST['remove']))
		{
			foreach ($_POST['delete'] as $value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('product')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('product.php');
		}
		$this->pagination->getCount($this->db->query(QueryBuilder::getInstance()->count()->from('product'))->fetch());
		$products = new Model($this->db->query(QueryBuilder::getInstance()->select('product.*,category.name as categoryname')->from('product')->leftJoin('category')->on('category.id = categoryid')->order('`order`')->limit($this->pagination)));
		$this->render('admin/product', array('message'=>$message,'items'=>$products));
	
	}
	
	public function actionEditProduct()
	{
		$message = $this->user->message();
	
		if (!isset($_GET['id'])) {
			Cshop::app()->redirect('product.php');
		}
		$products = $this->db->prepare(QueryBuilder::getInstance()->select('product.*,field.name AS fieldname,field.id as fieldid,type')->from('product')->leftJoin('field')->on('productid=product.id')->where('product.id = ?'));
		$products->execute(array($_GET['id']));
		$products = $products->fetchAll();
		if (!$products) {
			Cshop::app()->redirect('product.php');
		}
		CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) { $menu['محصولات']['ویرایش محصول'] =  'editproduct.php?id='.$_GET['id']; });
		
		if (isset($_POST['save']))
		{
			$product = $_POST['product'];
			//validate
			try
			{
				if (empty($product['name']))
					throw new Exception('لطفا یک عنوان مناسب انتخاب کنید');
			
				if (!isset($product['categoryid']))
					throw new Exception('لطفا یک دسته انتخاب کنید');
				if (!is_numeric($product['price']))
					throw new Exception('مبلغ را به عدد وارد کنید');
				
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('product')->set('name=?,description=?,categoryid=?,price=?,skipitem=?')->where('id=?'));
				$sql->execute(array($product['name'],$product['description'],$product['categoryid'],$product['price'],$product['skipitem'],$products[0]['id']));
				$pid = $products[0]['id'];
				if (is_array($_POST['field']))
				{
					foreach (@$_POST['field'] as $key=>$field)
					{
						
						if (!$field['fieldname']) {
							if (isset($field['fieldid']))
							{
								$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('field')->where('id=?'));
								$sql->execute(array($field['fieldid']));
							}	
							continue;
						}
						if (isset($field['fieldid']))
						{
							$sql = $this->db->prepare(QueryBuilder::getInstance()->update('field')->set('name=:fieldname,type=:type')->where('id=:fieldid'));
						}
						else 
						{
							$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('field')->into(array('productid','name','type'))->values(array(':productid',':fieldname',':type'),''));
							$field['productid'] = $pid;
						}
						
						$sql->execute($field);
					}
					
				}
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$this->user->message($message);
				Cshop::app()->redirect('editproduct.php?id='.$pid);
			
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
			
		}
		else
			$product = $products[0];
		
		if(is_array($_POST['field']))
			$products = $_POST['field'];
		$this->render('admin/productform', array('message'=>$message,'item'=>$product,'fields'=>$products,'category'=>new Model($this->db->query(QueryBuilder::getInstance()->select()->from('category')->order('`order`')))));
	}
	
	public function actionCreateProduct()
	{
		$message = $this->user->message();
	
		if (isset($_POST['save']))
		{
			$product = $_POST['product'];
			try
			{
				if (empty($product['name']))
					throw new Exception('لطفا یک عنوان مناسب انتخاب کنید');
				
				if (!isset($product['categoryid']))
					throw new Exception('لطفا یک دسته انتخاب کنید');
				if (!is_numeric($product['price']))
					throw new Exception('مبلغ را به عدد وارد کنید');
				
				$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('product')->into(array('name','description','categoryid','price','skipitem'),true));
				
				$sql->execute($product);
				$pid = $this->db->lastInsertId();
				if (is_array($_POST['field']))
				{
					foreach ($_POST['field'] as $key=>$field)
					{
						if (!$field['fieldname']) {
							continue;
						}
						$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('field')->into(array('productid','name','type'))->values(array(':productid',':fieldname',':type'),''));
						$field['productid'] = $pid;
						
						$sql->execute($field);
					}
					
				}
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$this->user->message($message);
				Cshop::app()->redirect('editproduct.php?id='.$pid);
				
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
				
		}
		else
		{
			$product = array('name'=>'','description'=>'','categoryid'=>'');
		}
		if(is_array($_POST['field']))
			$products = $_POST['field'];
	
		$this->render('admin/productform', array('message'=>$message,'item'=>$product,'fields'=>$products,'category'=>new Model($this->db->query(QueryBuilder::getInstance()->select()->from('category')->order('`order`')))));
	}
	
	public function actionItem()
	{
		$message = $this->user->message();
	
		if(isset($_POST['remove']))
		{
			foreach ($_POST['delete'] as $value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('item')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('item.php');
		}
		$this->db->exec(QueryBuilder::getInstance()
				->delete('item')
				->where('reservetime<'.time())->andWith('status='.Application::STATUS_SYSTEM_ADDED));
		$this->pagination->getCount($this->db->query(QueryBuilder::getInstance()->count()->from('item'))->fetch());
		$items = new Model($this->db->query(QueryBuilder::getInstance()->select('item.*,product.name')->from('item')->leftJoin('product')->on('productid = product.id')->where('status!='.Application::STATUS_SYSTEM_ADDED)->limit($this->pagination)));
		$this->render('admin/item', array('message'=>$message,'items'=>$items));
	
	}
	
	public function actionEditItem()
	{
		$message = $this->user->message();
	
		if (!isset($_GET['id'])) {
			Cshop::app()->redirect('item.php');
		}
		$items = $this->db->prepare(QueryBuilder::getInstance()->select('item.*,value,fieldid,field.name as fieldname')->from('item')->leftJoin('value')->on('itemid=item.id')->leftJoin('field')->on('field.id = fieldid')->where('item.id = ?'));
		$items->execute(array($_GET['id']));
		$items = $items->fetchAll();
		if (!$items) {
			Cshop::app()->redirect('item.php');
		}
		CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) { $menu['کارت ها']['ویرایش کارت'] =  'edititem.php?id='.$_GET['id']; });
		
		if (isset($_POST['save']))
		{
			$item = $_POST['item'];
			//validate
	
			try
			{
				if (empty($item['productid']))
					throw new Exception('لطفا یک محصول مناسب انتخاب کنید');

				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('item')->set('status=?,productid=?')->where('id=?'));
				$sql->execute(array($item['status'],$item['productid'],$items[0]['id']));
				
				$id = $items[0]['id'];
				
				if (is_array($_POST['value']))
				{
					foreach ($_POST['value'] as $key=>$value)
					{
						if (!$value['value']) {
							if (isset($value['fieldid']))
							{
								$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('value')->where('itemid=? AND fieldid=?'));
								$sql->execute(array($id,$value['fieldid']));
							}
							continue;
						}
						$sql = $this->db->prepare(QueryBuilder::getInstance()->select()->from('value')->where('fieldid=? AND itemid=?'));
						$sql->execute(array($value['fieldid'],$id));
						if ($sql->rowCount() == 1)
						{
							$sql = $this->db->prepare(QueryBuilder::getInstance()->update('value')->set('value=?')->where('fieldid=? AND itemid=?'));
						}
						else
						{
							$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('value')->into(array('value','fieldid','itemid'),true,false));
						}
						$sql->execute(array($value['value'],$value['fieldid'],$id));
					}
				}
				
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$this->user->message($message);
				Cshop::app()->redirect('edititem.php?id='.$id);
					
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
		}
		else
			$item = $items[0];
		
		if(is_array($_POST['field']))
			$products = $_POST['field'];
		$products = array();
		$sql = $this->db->query(QueryBuilder::getInstance()->select('product.*,category.name as categoryname')->from('product')->leftJoin('category')->on('categoryid = category.id')->where('skipitem =0')->order('`order`'));
		
		while($row = $sql->fetch() )
		{
			$products[$row['categoryname']][$row['id']] = $row['name'];
		}
		$this->render('admin/itemform', array('message'=>$message,'item'=>$item,'values'=>$items,'products'=>$products));
	}
	
	public function actionCreateItem()
	{
		if ($_GET['product'])
		{
			$sql = $this->db->prepare(QueryBuilder::getInstance()->select('id,name,type')->from('field')->where('productid=?'));
			$sql->execute(array($_GET['product']));
			$out = array();
			while($row=$sql->fetch())
			{
				if(Item::checkHidden($row['type']))
					continue;
				unset($row['type']);
				$out[] = $row;
			}
			echo json_encode($out);
			exit;
		}
		$message = $this->user->message();
	
		if (isset($_POST['save']))
		{
			$item = $_POST['item'];
			//validate
			
			
			try
			{
				if (empty($item['productid']))
					throw new Exception('لطفا یک محصول مناسب انتخاب کنید');
			
				$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('item')->into(array('productid','status','createtime'),true));
				$item['createtime'] = time();
				$item['status'] = Application::STATUS_PENDING;
				$sql->execute($item);
				$id = $this->db->lastInsertId();
				foreach ($_POST['value'] as $key=>$field)
				{
					if (!$field['value']) {
						continue;
					}
					$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('value')->into(array('fieldid','itemid','value'),true,false));
					
					$sql->execute(array($field['fieldid'],$id,$field['value']));
				}	
				
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$this->user->message($message);
				CShop::app()->redirect('edititem.php?id='.$id);
				
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
				
				
		}
		else
		{
			$item = array('productid'=>'');
		}
		
		if(is_array($_POST['value']))
			$values = $_POST['value'];
		
		$products = array();
		$sql = $this->db->query(QueryBuilder::getInstance()->select('product.*,category.name as categoryname')->from('product')->leftJoin('category')->on('categoryid = category.id')->where('skipitem =0')->order('`order`'));
		
		while($row = $sql->fetch() )
		{
			$products[$row['categoryname']][$row['id']] = $row['name'];
		}
		$this->render('admin/itemform', array('message'=>$message,'item'=>$item,'values'=>$values,'products'=>$products));
	}
	
	public function actionGateway()
	{
		$message = $this->user->message();
	
		if (isset($_POST['update']))
		{
			foreach ($_POST['order'] as $key=>$value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('gateway')->set('`order` = ?')->where('id = ?'));
				$sql->execute(array($value,$key));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('gateway.php');
		}
		elseif(isset($_POST['remove']))
		{
			foreach ($_POST['delete'] as $value)
			{
				$gateway= $this->db->prepare(QueryBuilder::getInstance()->select()->from('gateway')->where('id = ?'));
				$gateway->execute(array($value));
				$gateway = $gateway->fetch();
				$class = $gateway['class'];
				$file = Cshop::$gatewaypath . DIRECTORY_SEPARATOR . $class .'.php';
				CShop::import($file,true);
				$class::uninstall($value);
				
				$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('gateway')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('gateway.php');
		}
		elseif(isset($_GET['install']))
		{
			$class = str_replace(chr(0), '', basename($_GET['install']));
			$file = Cshop::$gatewaypath . DIRECTORY_SEPARATOR . $class .'.php';
			if (realpath(Cshop::$gatewaypath) != dirname($file))
				exit("You are very clever !");
			
			CShop::import($file,true);
			
			$data = $class::getData();
			
			$sql = $this->db->prepare(QueryBuilder::getInstance()
					->insert('gateway')
					->into(array('name','class','status'),true));
			$sql->execute(array('name'=>$data['name'],'class'=>$class,'status'=>Application::STATUS_ACTIVE));
			$id = $this->db->lastInsertId();
			foreach ($class::getParameters() as $key=>$value)
				$this->db->exec(QueryBuilder::getInstance()->insert('gateway_meta')->into('gatewayid,`key`,value')->values(array($id,$key,'')));
			$data = $class::install($id);
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			Cshop::app()->redirect("gateway.php");
			
		}
		$newgateways = array();
		foreach (glob(Cshop::$gatewaypath."/*.php") as $filename)
		{
			CShop::import($filename,true);
			$class = pathinfo($filename,PATHINFO_FILENAME);
			$data = $class::getData();
			$newgateways[$class] = array_merge($data,array('filename'=>$class));
		}
		$gateways = array();
		$sql = $this->db->query(QueryBuilder::getInstance()->select()->from('gateway')->order('`order`'));
		while ($gateway = $sql->fetch())
		{
			
			unset($newgateways[$gateway['class']]);
			$gateways[] = $gateway;
		}
		$this->render('admin/gateway', array('message'=>$message,'items'=>$gateways,'newgateways'=>$newgateways));
	
	}
	
	public function actionGatewayData()
	{
		$message = $this->user->message();
	
		if (!isset($_GET['id'])) {
			Cshop::app()->redirect('gateway.php');
		}
		$gateways= $this->db->prepare(QueryBuilder::getInstance()->select()->from('gateway')->join('gateway_meta')->on('gatewayid = gateway.id')->where('gateway.id = ?'));
		$gateways->execute(array($_GET['id']));
		$gateways = $gateways->fetchAll();
	
		if (!$gateways) {
			Cshop::app()->redirect('gateway.php');
		}
		$class = $gateways[0]['class'];
		
		foreach ($r=$class::getActions() as $key=>$value)
			$r[$key] = 'plugindata.php?id='.$gateways[0]['id'].'&action='.$value;
		
		$actions = array($gateways[0]['name']=>$r);
		if(!empty($actions))
		{
			CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) use ($actions) { $menu=array_merge($actions,$menu); });
		}
		
		if (isset($_GET['action']))
		{
			if(method_exists($class, 'action'.$_GET['action']))
			{
				call_user_func(array(new $class($gateways),'action'.$_GET['action']),$this);
				CShop::app()->end();
			}
		}
		CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) { $menu['تنظیمات']['ویرایش درگاه'] =  'gatewaydata.php?id='.$_GET['id']; });
		
		if (isset($_POST['save']))
		{
			foreach ($_POST['meta'] as $key=>$value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('gateway_meta')->set('value=?')->where('`key`=? AND gatewayid = ?'));
				$sql->execute(array($value,$key,$_GET['id']));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('gateway.php');
		}
		CShop::import(Cshop::$gatewaypath . DIRECTORY_SEPARATOR . $gateways[0]['class'] . '.php',true);
		$this->render('admin/gatewayform', array('message'=>$message,'items'=>$gateways));
	}
	
	public function actionPlugin()
	{
		$message = $this->user->message();
	
		if (isset($_POST['update']))
		{
			foreach ($_POST['order'] as $key=>$value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('plugin')->set('`order` = ?')->where('id = ?'));
				$sql->execute(array($value,$key));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('plugin.php');
		}
		elseif(isset($_POST['remove']))
		{
			foreach ($_POST['delete'] as $value)
			{
				$gateway= $this->db->prepare(QueryBuilder::getInstance()->select()->from('plugin')->where('id = ?'));
				$gateway->execute(array($value));
				$gateway = $gateway->fetch();
				$class = $gateway['class'];
				$file = Cshop::$pluginpath . DIRECTORY_SEPARATOR . $class .'.php';
				CShop::import($file,true);
				$class::uninstall($value);
				$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('plugin')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('plugin.php');
		}
		elseif(isset($_GET['install']))
		{
			$class = str_replace(chr(0), '', basename($_GET['install']));
			$file = Cshop::$pluginpath . DIRECTORY_SEPARATOR . $class . '.php';
			if (realpath(Cshop::$pluginpath) != dirname($file))
				exit("You are very clever !");
				
			CShop::import($file);
			$data = $class::getData();
				
			$sql = $this->db->prepare(QueryBuilder::getInstance()
					->insert('plugin')
					->into(array('name','class','status'),true));
			$sql->execute(array('name'=>$data['name'],'class'=>$class,'status'=>Application::STATUS_ACTIVE));
			$id = $this->db->lastInsertId();
			foreach ($class::getParameters() as $key=>$value)
				$this->db->exec(QueryBuilder::getInstance()->insert('plugin_meta')->into('pluginid,`key`,value')->values(array($id,$key,'')));
			$data = $class::install($id);
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('plugin.php');
		}
		$newplugins = array();
		foreach (glob(Cshop::$pluginpath."/*.php") as $filename)
		{
			CShop::import($filename);
			$class = pathinfo($filename,PATHINFO_FILENAME);
			$data = $class::getData();
			$newplugins[$class] = array_merge($data,array('filename'=>$class));
		}
		$plugins = array();
		$sql = $this->db->query(QueryBuilder::getInstance()->select()->from('plugin')->order('`order`'));
		while ($plugin = $sql->fetch())
		{
				
			unset($newplugins[$plugin['class']]);
			$plugins[] = $plugin;
		}
		$this->render('admin/plugin', array('message'=>$message,'items'=>$plugins,'newplugins'=>$newplugins));
	
	}
	
	public function actionPluginData()
	{
		$message = $this->user->message();
	
		if (!isset($_GET['id'])) {
			Cshop::app()->redirect('plugin.php');
		}
		
		$plugins= $this->db->prepare(QueryBuilder::getInstance()->select('*,plugin.id as id')->from('plugin')->join('plugin_meta')->on('pluginid = plugin.id')->where('plugin.id = ?'));
		$plugins->execute(array($_GET['id']));
		$plugins = $plugins->fetchAll();
	
		if (!$plugins) {
			Cshop::app()->redirect('plugin.php');
		}
		$class = $plugins[0]['class'];
		
		foreach ($r=$class::getActions() as $key=>$value)
			$r[$key] = 'plugindata.php?id='.$plugins[0]['id'].'&action='.$value;
		
		$actions = array($plugins[0]['name']=>$r);
		if(!empty($actions))
		{
			CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) use ($actions) { $menu=array_merge($actions,$menu); });
		}
		
		if (isset($_GET['action']))
		{
			if(method_exists($class, 'action'.$_GET['action']))
			{
				call_user_func(array(new $class($plugins),'action'.$_GET['action']),$this);
				CShop::app()->end();
			}
		}
		
		CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) { $menu['تنظیمات']['ویرایش پلاگین'] =  'plugindata.php?id='.$_GET['id']; });
		
		if (isset($_POST['save']))
		{
			foreach ($_POST['meta'] as $key=>$value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('plugin_meta')->set('value=?')->where('`key`=? AND pluginid = ?'));
				$sql->execute(array($value,$key,$_GET['id']));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('plugin.php');
		}
		$this->render('admin/pluginform', array('message'=>$message,'items'=>$plugins));
	}
	
	public function actionSetting()
	{
		$message = $this->user->message();
		
		if (isset($_POST['save']))
		{
			$config = $_POST['setting'];
				
			try
			{
				foreach ($config as $key=>$value)
				{
					$sql = $this->db->prepare(QueryBuilder::getInstance()->select()->from('config')->where("category=? AND `key`=?"));
					$sql->execute(array(Application::APPLICATON_CONFIG_CATEGORY,$key));
					if ($sql->rowCount() == 1)
					{
						$sql = $this->db->prepare(QueryBuilder::getInstance()->update('config')->set('value=?,description=?')->where('`key`=? AND category=?'));
						$sql->execute(array($value,CShop::app()->systemConfig()->description($key),$key,Application::APPLICATON_CONFIG_CATEGORY));
					}
					else 
					{
						$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('config')->into(array('`key`','value','description','category'),true,false));
						$sql->execute(array($key,$value,CShop::app()->systemConfig()->description($key),Application::APPLICATON_CONFIG_CATEGORY));	
					}
				}
				
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$this->user->message($message);
				CShop::app()->redirect('setting.php');
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
				
		}
		else
		{
			$config = CShop::app()->systemConfig();
		}
		
		$this->render('admin/setting', array('message'=>$message,'item'=>$config));
	}
	public function actionPayment()
	{
		$message = $this->user->message();
		
		if(isset($_POST['remove']))
		{
			foreach ($_POST['delete'] as $value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('payment')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('payment.php');
		}
		$this->pagination->getCount($this->db->query(QueryBuilder::getInstance()->count()->from('payment'))->fetch());
		$payments = new Model($this->db->query(QueryBuilder::getInstance()->select()->from('payment')->order('id DESC')->limit($this->pagination)));
		$this->render('admin/payment', array('message'=>$message,'items'=>$payments));
	}
	public function actionViewPayment()
	{
		$message = $this->user->message();
		
		
		if (!isset($_GET['id'])) {
			Cshop::app()->redirect('payment.php');
		}
		$sql = $this->db->prepare(QueryBuilder::getInstance()->select()->from('payment')->where('payment.id=?'));
		$sql->execute(array($_GET['id']));
		$payment = $sql->fetch();
		if (!$payment) {
			Cshop::app()->redirect('payment.php');
		}
		
		$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->select('input.*,value')->from('payment_meta')->leftJoin('input')->on('inputid = input.id')->where('paymentid = ?'));
		$sql->execute(array($payment['id']));
		$payment['input'] = array();
		while ($row=$sql->fetch())
		{
			$row['data'] = unserialize($row['data']);
			$payment['input'][$row['id']] = $row;
		}
		
		$sql = $this->db->prepare(QueryBuilder::getInstance()
				->select('item.*,value.fieldid,value,field.name AS fieldname,field.type,product.name,product.description')
				->from('item')
				->leftJoin('product')
				->on('product.id = item.productid')
				->leftJoin('value')
				->on('item.id = itemid')
				->leftJoin('field')
				->on('fieldid = field.id')
				->where('paymentid = ?'));
		$sql->execute(array($_GET['id']));
		$items = array();	
		while ($item = $sql->fetch())
		{
			$items[$item['id']][] = $item;
		}
		CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) { $menu['خرید ها']['مشاهده خرید'] =  'viewpayment.php?id='.$_GET['id']; });
		
		
		$this->render('admin/paymentview', array('message'=>$message,'payment'=>$payment,'items'=>$items));
	}
	public function actionStatistic()
	{
		$message = $this->user->message();
		
		if (isset($_POST['update']))
		{
			foreach ($_POST['order'] as $key=>$value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('category')->set('`order` = ?')->where('id = ?'));
				$sql->execute(array($value,$key));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('category.php');
		}
		elseif(isset($_POST['remove']))
		{
			foreach ($_POST['delete'] as $value)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->delete('category')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$this->user->message($message);
			CShop::app()->redirect('category.php');
		}
		
		$categories = new Model($this->db->query(QueryBuilder::getInstance()->select()->from('category')->order('`order`')));
		$this->render('admin/category', array('message'=>$message,'items'=>$categories));
		
	}
}