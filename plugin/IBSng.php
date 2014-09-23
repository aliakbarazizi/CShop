<?php
class IBSng extends Plugin
{
	public static function getData()
	{
		return array(
			'name'=>'اکانت ساز خودکار IBSng',
			'note'=>'اکانت ساز خودکار IBSng',
			'author'=>array(
				'name'=>'Ir-prog',
				'url'=>'http://ir-prog.ir',
				'email'=>'admin@ir-prog.ir',
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
		self::saveMeta($id,array('usernameinput'=>Input::addInput('نام کاربری', 'text'),'passwordinput'=>Input::addInput('کلمه عبور', 'text')));
	}
	
	public static function uninstall($id)
	{
		$object = self::loadPlugin($id);
		Input::deleteInput($object->usernameinput);
		Input::deleteInput($object->passwordinput);
	}

	public function register($eventHandler)
	{
		$eventHandler->attach(Application::EVENT_BEFORE_PAYMENT, array(
			$this,
			'checkUser'
		));
		$eventHandler->attach(Application::EVENT_AFTER_PAYMENT, array(
			$this,
			'updateUser'
		));
		$eventHandler->attach(Application::EVENT_ITEM_TYPE, array(
			$this,
			'itemtype'
		));
	}
	
	public function itemtype()
	{
		Item::addType('ibsnggroup', 'گروه اکانت در IBSng', array('hiddden'=>true));
	}
	
	public function checkUser(&$payment,&$products)
	{
		$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()
				->select('field.name')
				->from('item')
				->leftJoin('product')
				->on('product.id = item.productid')
				->leftJoin('field')
				->on('field.productid = product.id')
				->where('paymentid = ? AND type="ibsnggroup"'));
		$sql->execute(array($payment['id']));
		if($sql->rowCount() !=1)
			throw new Exception('شما تنها یک محصول از این نوع می توانید انتخاب کنید');
		$ibs = new IBSngHelper($this->username, $this->password, $this->server);
		if($ibs->userExist($payment['intput'][$this->usernameinput]) !== false)
		{
			throw new Exception('نام کاربری انتخابی موجود هست اگر قصد تمدید اکانت را دارید تیک تمدید را انتخاب کنید');
		}
	}
	public function updateUser(&$payment,&$items)
	{
		$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()
						->select('field.name')
						->from('item')
						->leftJoin('product')
						->on('product.id = item.productid')
						->leftJoin('field')
						->on('field.productid = product.id')
						->where('paymentid = ? AND type="ibsnggroup"'));
		$sql->execute(array($payment['id']));
		$group = $sql->fetch();
		$group = $group['name'];
		
		$ibs = new IBSngHelper($this->username, $this->password, $this->server);
		$ibs->chargeUser($group, $payment['input'][$this->usernameinput], $payment['input'][$this->passwordinput]);
	}

}

class IBSngHelper
{
	public $error;
	public $username;
	public $password;
	public $ip;
	private $handler;
	private $cookie;

	public function __construct($username, $password, $ip)
	{
		$this->username = $username;
		$this->password = $password;
		$this->ip = $ip;
		
		$url = $this->ip . '/IBSng/admin/';
		$this->handler = curl_init();
		
		$post_data['username'] = $username;
		$post_data['password'] = $password;
		
		curl_setopt($this->handler, CURLOPT_URL, $url);
		curl_setopt($this->handler, CURLOPT_POST, true);
		curl_setopt($this->handler, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($this->handler, CURLOPT_HEADER, true);
		curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->handler, CURLOPT_COOKIEJAR, $this->cookie_file);
		curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, true);
		
		$output = curl_exec($this->handler);

		preg_match_all('|Set-Cookie: (.*);|U', $output, $matches);
		$this->cookie = implode('; ', $matches[1]);
		
	}

	public function userExist($username)
	{
		$url = $this->ip . '/IBSng/admin/user/user_info.php?normal_username_multi=' . $username;
		$this->handler = curl_init();
		curl_setopt($this->handler, CURLOPT_URL, $url);
		curl_setopt($this->handler, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($this->handler, CURLOPT_HEADER, TRUE);
		curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($this->handler);
		if (strpos($output, 'does not exists') !== false)
		{
			return false;
		}
		else
		{
			$pattern1 = 'change_credit.php?user_id=';
			$pos1 = strpos($output, $pattern1);
			$sub1 = substr($output, $pos1 + strlen($pattern1), 100);
			$pattern2 = '"';
			$pos2 = strpos($sub1, $pattern2);
			$sub2 = substr($sub1, 0, $pos2);
			return $sub2;
		}
	}

	public function addUser($group_name, $username, $password)
	{
		$owner = 'system';
		$id = $this->addUid($group_name);
		$url = $this->ip . '/IBSng/admin/plugins/edit.php?edit_user=1&user_id=' . $id . '&submit_form=1&add=1&count=1&credit=1&owner_name=' . $owner . '&group_name=' . $group_name . '&x=35&y=1&edit__normal_username=normal_username&edit__voip_username=voip_username';
		$post_data['target'] = 'user';
		$post_data['target_id'] = $id;
		$post_data['update'] = 1;
		$post_data['edit_tpl_cs'] = 'normal_username';
		$post_data['attr_update_method_0'] = 'normalAttrs';
		$post_data['has_normal_username'] = 't';
		$post_data['current_normal_username'] = '';
		$post_data['normal_username'] = $username; // username
		$post_data['password'] = $password; // password
		$post_data['normal_save_user_add'] = 't';
		$post_data['credit'] = 1;

		$this->handler = curl_init();
		curl_setopt($this->handler, CURLOPT_URL, $url);
		curl_setopt($this->handler, CURLOPT_POST, true);
		curl_setopt($this->handler, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($this->handler, CURLOPT_HEADER, TRUE);
		curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->handler, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($this->handler);

		return true; 
	}

	private function addUid($group_name)
	{
	
		$url = $this->ip . '/IBSng/admin/user/add_new_users.php';
		$post_data['submit_form'] = 1;
		$post_data['add'] = 1;
		$post_data['count'] = 1;
		$post_data['credit'] = 1;
		$post_data['owner_name'] = "system";
		$post_data['group_name'] = $group_name; // $group_name;
		$post_data['edit__normal_username'] = 'normal_username';
		
		$this->handler = curl_init();
		curl_setopt($this->handler, CURLOPT_URL, $url);
		curl_setopt($this->handler, CURLOPT_POST, true);
		curl_setopt($this->handler, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($this->handler, CURLOPT_HEADER, TRUE);
		curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->handler, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($this->handler);
		$pattern1 = '<input type=hidden name="user_id" value="';
		$pos1 = strpos($output, $pattern1);
		$sub1 = substr($output, $pos1 + strlen($pattern1), 100);
		$pattern2 = '">';
		$pos2 = strpos($sub1, $pattern2);
		$sub2 = substr($sub1, 0, $pos2);
		return $sub2;
	}
	
	public function chargeUser($group_name,$username,$password)
	{
		$id = $this->userExist($username);
		
		if($id === false)
			return $this->addUser($group_name, $username, $password);
		
		$url = $this->ip . '/IBSng/admin/plugins/edit.php';
		
		$post_data['target'] = 'user';
		$post_data['target_id'] = $id;
		$post_data['update'] = 1;
		$post_data['edit_tpl_cs'] = 'group_name';
		$post_data['tab1_selected'] = 'Main';
		$post_data['attr_update_method_0'] = 'groupName';
		$post_data['group_name'] = $group_name;
		
		$this->handler = curl_init();
		curl_setopt($this->handler, CURLOPT_URL, $url);
		curl_setopt($this->handler, CURLOPT_POST, true);
		curl_setopt($this->handler, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($this->handler, CURLOPT_HEADER, TRUE);
		curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->handler, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($this->handler);
		
		unset($post_data);
		
		$url = $this->ip . '/IBSng/admin/plugins/edit.php';
		
		$post_data['target'] = 'user';
		$post_data['target_id'] = $id;
		$post_data['update'] = 1;
		$post_data['edit_tpl_cs'] = 'rel_exp_date,abs_exp_date,first_login';
		$post_data['tab1_selected'] = 'Exp_Dates';
		$post_data['attr_update_method_0'] = 'relExpDate';
		$post_data['attr_update_method_1'] = 'absExpDate';
		$post_data['attr_update_method_2'] = 'firstLogin';
		$post_data['reset_first_login'] = 't';
		
		$this->handler = curl_init();
		curl_setopt($this->handler, CURLOPT_URL, $url);
		curl_setopt($this->handler, CURLOPT_POST, true);
		curl_setopt($this->handler, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($this->handler, CURLOPT_HEADER, TRUE);
		curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->handler, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($this->handler);
		
		return $output;
	}
}
