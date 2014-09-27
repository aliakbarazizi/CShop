<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop.controller
 */
class Controller extends BaseController
{
	public $layout = 'layout/site';
	
	public $pageTitle = 'صفحه اصلی';
	
	protected $cache = array('index');
	
	public function actionIndex()
	{
		$message['content']  = '';
		$save=false;
		if (isset($_POST['submit']))
		{
			try {
				$inputs = array();
				foreach (new Model($this->db->query(QueryBuilder::getInstance()->select()->from('input'))) as $key=>$value)
				{
					$value['data'] = unserialize($value['data']);
					$messages = Input::validate($value, $_POST['input'][$key]);
					if ($messages !== true)
					{
						throw new Exception(implode('<br>', $messages));
					}
					$inputs[$key] = $value;
					$inputs[$key]['value'] = $_POST['input'][$key];
				}
				if(!is_array($_POST['product']))
					throw new Exception("لطفا یک محصول انتخاب کنید");
				if(empty($_POST['product']))
					throw new Exception("لطفا یک محصول انتخاب کنید");
				$product = array_keys($_POST['product']);
				
				$products = array();
				$sql = $this->db->prepare(QueryBuilder::getInstance()
						->select()
						->from('product')
						->where('product.id IN ('.implode(',', array_fill(0, count($product), '?')).')'));
				$sql->execute($product);
				while($row = $sql->fetch())
				{
					$products[$row['id']] = $row;
				}
				
				$items = $this->db->prepare(QueryBuilder::getInstance()
						->select('item.*,price')
						->from('item')
						->leftJoin('product')
						->on('product.id = productid')
						->where('productid IN ('.implode(',', array_fill(0, count($product), '?')).')')
						->andWith('reservetime < ' . time())->andWith('status ='.Application::STATUS_PENDING));
				
				$items->execute($product);
				
				$itemids = array();
				while($item = $items->fetch())
				{
					if ($item['id'])
						$itemids[$item['productid']][] = $item;
				}
				$buyids = array();
				$additems = array();
				$price = 0;
				foreach ($products as $key=>$p)
				{
					$value = $_POST['product'][$key];
					if (!$value)
						$value = 1;
					if($p['skipitem'] == 1)
					{
						$price = $p['price']*$value + $price;
						$additems[$key] = $value;
						continue;
					}
					if ($value > count($itemids[$key]))
						throw new Exception("محصول انتخاب شده موجود نیست");
					else 
					{
						for ($i = 0; $i < $value; $i++)
						{
							$item = array_shift($itemids[$key]);
							$price = $item['price'] + $price;
							$buyids[] = $item['id'];
						}
					}
				}
				
				$gateway = $this->db->prepare(QueryBuilder::getInstance()
						->select()
						->from('gateway')
						->leftJoin('gateway_meta')
						->on('gatewayid = gateway.id')
						->where('gateway.id = ?'));
				
				$gateway->execute(array($_POST['gatewayid']));
				$gateway = $gateway->fetchAll();
				
				if (!$gateway)
				{
					throw new Exception("درگاه وارد شده معتبر نیست");
				}
				
				$sql = $this->db->prepare(QueryBuilder::getInstance()
						->insert('payment')
						->into('`requesttime`, `status`, `clientip`, `gatewayid`, `amount`',true));
				$param = array(
					'requesttime'=>time(),
					'status'=>Application::STATUS_PENDING,
					'clientip'=>$_SERVER['REMOTE_ADDR'],
					'gatewayid'=>$_POST['gatewayid'],
					'amount'=>$price,
				);
				$sql->execute($param);
				$param['id'] = $this->db->lastInsertId();
				foreach ($inputs as $key=>$value)
				{
					$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('payment_meta')->into(array('paymentid','inputid','value'),true,false));
					$sql->execute(array($param['id'],$key,$value['value']));
				}
				
				if(!empty($buyids))
					$this->db->exec(QueryBuilder::getInstance()
							->update('item')
							->set('paymentid='.$param['id'].',reservetime='.(time()+CShop::app()->systemConfig()->reservetime*60*60))
							->where('id IN ('.implode(',', $buyids).')'));
				$additemids = array();
				if(!empty($additems))
				{
					foreach ($additems as $key=>$value)
					{
						for ($i=0;$i<$value;$i++)
						{
							$sql = $this->db->prepare(QueryBuilder::getInstance()->insert('item')->into(array('productid','status','createtime','paymentid','reservetime'),true,false));
							$sql->execute(array($key,Application::STATUS_SYSTEM_ADDED,time(),$param['id'],time()+CShop::app()->systemConfig()->reservetime*60*60,));
							$additemids[] = $this->db->lastInsertId();
						}
					}
				}
				$save = true;
				$param['input'] = $inputs;
				CShop::app()->raise(Application::EVENT_BEFORE_PAYMENT,array(&$param,&$products));
				
				CShop::import(Cshop::$gatewaypath . DIRECTORY_SEPARATOR . $gateway[0]['class'] . '.php');
				
				/* @var $plugin GatewayBase */
				$plugin = new $gateway[0]['class']($gateway);
				
				$message = $plugin->sendToGateway($param, Cshop::siteURL() . Cshop::$baseurl . '/payment.php?gateway='.$_POST['gatewayid']);
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();;
			}
			if (isset($message) && $save)
			{
				if(!empty($buyids))
					$this->db->exec(QueryBuilder::getInstance()
							->update('item')
							->set('reservetime=0')
							->where('id IN ('.implode(',', $buyids).')'));
					
				if(!empty($additemids))
					$this->db->exec(QueryBuilder::getInstance()
							->delete('item')
							->where('id IN ('.implode(',', $additemids).')'));
			}
		}
		$product = new Model($this->db->query(QueryBuilder::getInstance()->select()->from('product')->order('`order`')));
		$category = new Model($this->db->query(QueryBuilder::getInstance()->select()->from('category')->order('`order`')));
		$gateway = new Model($this->db->query(QueryBuilder::getInstance()->select()->from('gateway')));
		$input = new Model($this->db->query(QueryBuilder::getInstance()->select()->from('input')->order('`order`')));
		$this->render('site/index', array('input'=>$input,'product'=>$product,'category'=>$category,'gateway'=>$gateway,'message'=>$message));
	}
	
	public function actionPayment()
	{
		$message['content']  = '';
		$this->pageTitle = 'پرداخت';
		$items = array();
		try {
			
			if (!isset($_GET['gateway']))
			{
				throw new Exception('اطلاعات پرداخت کامل نمی باشد');
			}
			
			$gateway = $this->db->prepare(QueryBuilder::getInstance()
					->select()
					->from('gateway')
					->leftJoin('gateway_meta')
					->on('gatewayid = gateway.id')
					->where('gateway.id = ?'));
			$gateway->execute(array($_GET['gateway']));
			$gateway = $gateway->fetchAll();
			
			if (!$gateway[0])
			{
				throw new Exception('اطلاعات پرداخت کامل نمی باشد');
			}
			
			CShop::import(Cshop::$gatewaypath . DIRECTORY_SEPARATOR . $gateway[0]['class'] . '.php');
			
			/* @var $plugin GatewayBase */
			$plugin = new $gateway[0]['class']($gateway);
			
			$payment = $plugin->callbackGateway();
			
			if (!$payment)
			{
				throw new Exception('اطلاعات پرداخت کامل نمی باشد');
			}
			
			if ($payment['status'] == Application::STATUS_PENDING)
			{
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('payment')->set('status = ?,paymenttime=?')->where('id = ?'));
				$sql->execute(array($payment['status']=Application::STATUS_COMPLETE,time(),$payment['id']));
				
				$sql = $this->db->prepare(QueryBuilder::getInstance()->update('item')->set('status = ?')->where('paymentid = ?'));
				$sql->execute(array(Application::STATUS_COMPLETE,$payment['id']));
				
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
				$sql->execute(array($payment['id']));
				while ($item = $sql->fetch())
				{
					$items[$item['id']][] = $item;
				}
				
				$message['content'] = 'پرداخت با موفقیت انجام شد';
				$message['type'] = 'success';
				$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->select('input.*,value')->from('payment_meta')->leftJoin('input')->on('inputid = input.id')->where('paymentid = ?'));
				$sql->execute(array($payment['id']));
				$payment['input'] = array();
				while ($row=$sql->fetch())
				{
					$row['data'] = unserialize($row['data']);
					$payment['input'][$row['id']] = $row;
				}
				CShop::app()->raise(Application::EVENT_AFTER_PAYMENT,array(&$payment,&$items));
			}
			else
				throw new Exception('این سفارش قبلا پرداخت شده است.');
		} catch (Exception $e) {
			$message['content'] = $e->getMessage();
		}
		$this->layout = 'layout/payment';
		CShop::app()->raise(Application::EVENT_ITEM_TYPE);
		$this->render('site/payment', array('message'=>$message,'items'=>$items));
		
	}

}