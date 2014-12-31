<?php
/**
 * Plugin Plugin
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package components.plugin
 */
class Voucher extends Plugin
{
	protected $vouchercode;
	
	const TYPE_VOUCHER = 'voucher';
	
	public static function getActions()
	{
		return array('مدیریت کد های تخفیف'=>'voucher','کد تخفیف جدید'=>'createvoucher');
	}
	
	public static function getData()
	{
		return array(
			'name'=>'کد تخفیف',
			'note'=>'کد تخفیف',
			'author'=>array(
				'name'=>'Irprog',
				'url'=>'http://irprog.com',
				'email'=>'irprog@gmail.com',
			)
		);
	}
	
	public static function getParameters()
	{
		$inputs = CShop::app()->getDb()->query(QueryBuilder::getInstance()->select()->from('input')->where('type = "'.self::TYPE_VOUCHER.'"')->order('`order`'))->fetchAll();
		$range = array();
		foreach ($inputs as $input)
		{
			$range[$input['id']] = $input['name'];
		}
		return array(
				'vouchercode'=>array('name'=>'فیلد ورودی کد تخفیف','type'=>'select','range'=>$range),
		);
	}
	
	public static function install($id)
	{
		self::saveMeta($id,array('vouchercode'=>Input::addInput('کد تخفیف', self::TYPE_VOUCHER)));
		$prefix = CShop::app()->getConfig('database');
		$prefix = $prefix['prefix'];
		$sql = 'CREATE TABLE `'.$prefix.'voucher` (
		 `id` int(11) NOT NULL AUTO_INCREMENT,
		 `code` varchar(50) NOT NULL,
		 `time` int(11) NOT NULL,
		 `maxuse` int(11) NOT NULL,
		 `value` float NOT NULL,
		 `productid` int(11) NOT NULL,
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8';
		CShop::app()->getDb()->query($sql);
		
		$sql = 'CREATE TABLE `'.$prefix.'voucher_meta` (
		 `id` int(11) NOT NULL AUTO_INCREMENT,
		 `voucherid` int(11) NOT NULL,
		 `itemid` int(11) NOT NULL,
		 `status` tinyint(4) NOT NULL,
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8';
		CShop::app()->getDb()->query($sql);
	}
	
	public static function uninstall($id)
	{
		$object = self::loadPlugin($id);
		Input::deleteInput($object->vouchercode);
		$prefix = CShop::app()->getConfig('database');
		$prefix = $prefix['prefix'];
		$sql = 'DROP TABLE '.$prefix.'voucher';
		CShop::app()->getDb()->query($sql);
		$sql = 'DROP TABLE '.$prefix.'voucher_meta';
		CShop::app()->getDb()->query($sql);
	}

	public function register($eventHandler)
	{
		Input::addType(self::TYPE_VOUCHER, 'کد تخفیف', array($this,'vocherHtml'));
		
		CShop::app()->registerExternalAction('index', 'v', array(
			$this,
			'checkVoucher'
		));
		
		$eventHandler->attach(Application::EVENT_BEFORE_PAYMENT, array($this,'addVoucher'));
		
		$eventHandler->attach(Application::EVENT_AFTER_PAYMENT, array($this,'updateVoucher'));
	}
	
	public static function vocherHtml($name,$input,$value,$htmloptions=array())
	{
		$htmloptions['id'] = 'vouchercode';
		
		$content = '<script type="text/javascript" >
					$(function(){$("#total").append("<p></p>")});
					function sendvoucher(){
					
					$.get("index.php?v="+$("#vouchercode").val()+"&productid="+$("#category li.active").attr("data-type"),function(data,status){
					    if(data)
						{
							$("#total p").html("با تخفیف : ").append(parseInt($("#total span").html()) - parseInt($("#total span").html()) * parseInt(data) / 100).append(" ریال");
				
							$("#total span").bind("DOMSubtreeModified",function(){
								$("#total p").html("");
								$("#total p").html("با تخفیف : ").append(parseInt($("#total span").html()) - parseInt($("#total span").html()) * parseInt(data) / 100).append(" ریال");
							});
						}
					  });
		
					}
					</script>';
		
		$htmloptions['placeholder'] = $input['data']['placeholder'];
		$content .= '<div class="voucher">'.Html::textField($name, $value,$htmloptions);
		$content .= '<input type="button" value="چک کردن" name="checkvoucher" onclick="sendvoucher()"></div>';
		
		
		return $content;
	}
	
	public function checkVoucher($vouchercode)
	{
		$productid = $_GET['productid'];
		if(!$vouchercode || !$productid)
			CShop::app()->end();
		$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('voucher')->where('code=?')->andWith('(time > ? OR time = -1)')->andWith('(maxuse > 0 OR maxuse=-1)')->andWith('(productid=? OR productid = -1)'));
		$sql->execute(array($vouchercode,time(),$productid));
		if ($sql->rowCount() == 1)
		{
			$sql = $sql->fetch();
			echo $sql['value'];
		}
		CShop::app()->end();
	}
	
	function addVoucher(&$param,&$products)
	{
		if (isset($param['input'][$this->vouchercode]))
		{
			$vouchercode = $param['input'][$this->vouchercode]['value'];
			$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('voucher')->where('code=?')->andWith('(time > ? OR time = -1)')->andWith('(maxuse > 0 OR maxuse=-1)'));
			$sql->execute(array($vouchercode,time()));
			
			if ($sql->rowCount() != 1)
			{
				throw new Exception('کد تخفیف معتبر نیست.');
			}
			
			$sql = $sql->fetch();
			if ($sql['productid'] != -1)
			{
				foreach ($products as $productid => $value)
				{
					if ($sql['productid'] != $productid)
					{
						throw new Exception('کد تخفیف برای محصول '.$value['name'].' نمی باشد.');
					}
				}
			}
			$insert = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->insert('voucher_meta')->into(array('voucherid','paymentid','status'),true));
			$insert->execute(array($sql['id'],$param['id'],Application::STATUS_PENDING));
			$param['amount'] -= $param['amount'] * $sql['value'] / 100;
		}
		
	}
	function updateVoucher(&$payment,&$items)
	{
		$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('voucher_meta')->set('status='.Application::STATUS_COMPLETE)->where('paymentid=?'));
		$sql->execute(array($payment['id']));
		
		$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('voucher')->leftJoin('voucher_meta')->on('voucher.id = voucherid')->set('maxuse = maxuse-1')->where('paymentid=? AND maxuse > 0'));
		$sql->execute(array($payment['id']));
	}
	/**
	 *
	 * @param Controller $controller
	 */
	public function actionCreateVoucher($controller)
	{
		$message = $controller->getUser()->message();

		if (isset($_POST['save']))
		{
			$voucher = $_POST['voucher'];
			//validate
			try
			{
				if (empty($voucher['code']))
					throw new Exception('لطفا یک کد انتخاب کنید');
				$time = time() + 24 * 60 * 60 * $voucher['time'];
				$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->insert('voucher')->into(array('code','time','maxuse','value','productid'),true));
				$sql->execute(array($voucher['code'],$time,$voucher['maxuse'],$voucher['value'],$voucher['productid']));
					
				$message['content'] = 'تغییرات با موفقیت ذخیره شد';
				$message['type'] = 'success';
				$controller->getUser()->message($message);
				CShop::app()->redirect(self::getActionLink('editvoucher', $this->id).'&vid='.CShop::app()->getDb()->lastInsertId());
			}
			catch (Exception $e)
			{
				$message['content'] = $e->getMessage();
			}
		
		}
		else
			$voucher = array('code'=>'','time'=>'','maxuse'=>'','value'=>'','productid'=>'');
		
		
		$content = '<div class="title">کد تخفیف جدید</div>
			<div class="content">
			<form action="" method="post">
				<div class="formrow">
					<div class="label"><label for="code">کد</label></div>
					<div class="input"><input type="text" name="voucher[code]" id="code" value="'.$voucher['code'].'"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="time">تعداد روز استفاده ( -1 برای بینهایت )</label></div>
					<div class="input"><input type="text" name="voucher[time]" id="time" value="'.$voucher['time'].'"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="maxuse">تعداد استفاده( -1 برای بینهایت )</label></div>
					<div class="input"><input type="text" name="voucher[maxuse]" id="maxuse" value="'.$voucher['maxuse'].'"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="value">ارزش ( بدون % )</label></div>
					<div class="input"><input type="text" name="voucher[value]" id="value" value="'.$voucher['value'].'"></div>
				</div>
				<div class="formrow wide">
					<div class="label"><label for="productid">محصول (-1 برای همه)</label></div>
					<div class="input"><input type="text" name="voucher[productid]" id="productid" value="'.$voucher['productid'].'"></div>
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
	public function actionEditVoucher($controller)
	{
		$message = $controller->getUser()->message();
	
		if (!isset($_GET['vid'])) {
			Cshop::app()->redirect(self::getActionLink('voucher', $this->id));
		}
		
		$vouchers = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('page')->where('id = ?'));
		$vouchers->execute(array($_GET['vid']));
		$vouchers = $vouchers->fetch();
		
		if (!$vouchers) {
			Cshop::app()->redirect(self::getActionLink('voucher', $this->id));
		}
		
		CShop::app()->getEventHandler()->attach(Application::EVENT_MENU, function (&$menu) { $menu['مدیریت کدهای تخفیف']['ویرایش کد تخفیف'] = $_SERVER['REQUEST_URI']; });
		
		if (isset($_POST['save']))
		{
			$voucher = $_POST['page'];
			//validate
			try
			{
				if (empty($voucher['name']))
					throw new Exception('لطفا یک عنوان مناسب انتخاب کنید');
				$time = time() + 24 * 60 * 60 * $voucher['time'];
				$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('voucher')->set('code=?,time=?,maxuse=?,value=?,productid=?')->where('id=?'));
				$sql->execute(array($voucher['code'],$time,$voucher['maxuse'],$voucher['value'],$voucher['productid'],$_GET['vid']));
				
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
		{
			$voucher = $vouchers;
			$voucher['time'] = round(($voucher['time']-time())/(60*60*24),2);
			$voucher['time'] = $voucher['time'] > 0 ? $voucher['time'] : 0;
		}
		
		
		$content = '<div class="title">ویرایش کد تخفیف</div>
			<div class="content">
			<form action="" method="post">
				<div class="formrow">
					<div class="label"><label for="code">کد</label></div>
					<div class="input"><input type="text" name="voucher[code]" id="code" value="'.$voucher['code'].'"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="time">تعداد روز استفاده ( -1 برای بینهایت )</label></div>
					<div class="input"><input type="text" name="voucher[natimeme]" id="time" value="'.$voucher['time'].'"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="maxuse">تعداد استفاده( -1 برای بینهایت )</label></div>
					<div class="input"><input type="text" name="voucher[maxuse]" id="maxuse" value="'.$voucher['maxuse'].'"></div>
				</div>
				<div class="formrow">
					<div class="label"><label for="value">ارزش ( بدون % )</label></div>
					<div class="input"><input type="text" name="voucher[value]" id="value" value="'.$voucher['value'].'"></div>
				</div>
				<div class="formrow wide">
					<div class="label"><label for="productid">محصول (-1 برای همه)</label></div>
					<div class="input"><input type="text" name="voucher[productid]" id="productid" value="'.$voucher['productid'].'"></div>
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
	public function actionVoucher($controller)
	{
		$message = $controller->getUser()->message();
		if(isset($_POST['remove']))
		{
			foreach ($_POST['delete'] as $value)
			{
				$sql = CShop::app()->getDb()->prepare(QueryBuilder::getInstance()->delete('voucher')->where('id = ?'));
				$sql->execute(array($value));
			}
			$message['content'] = 'تغییرات با موفقیت ذخیره شد';
			$message['type'] = 'success';
			$controller->getUser()->message($message);
			CShop::app()->redirect($_SERVER['REQUEST_URI']);
		}
	
		$sql = CShop::app()->getDb()->query(QueryBuilder::getInstance()->select()->from('voucher'));
	
		$content = '<div class="title">مدیریت کد های تخفیف</div>
						<div class="content">
						<form action="" method="post">
						<table>
						<tr>
							<th>ردیف</th>
							<th>کد</th>
							<th>ارزش</th>
							<th>زمان پایان</th>
							<th>تعداد باقی مانده</th>
							<th>مدیریت</th>
							<th><a href="#" onclick="check(this)">انتخاب</a></th>
						</tr>';
		$i=1;
		while ($item = $sql->fetch())
		{
			$content .= '<tr>';
			$content .=  '<td>'.$i++.'</td>';
			$content .=  '<td>'.$item['code'].'</td>';
			$content .=  '<td>'.$item['value'].'%</td>';
			$content .=  '<td>'.jDateTime::date(CShop::app()->systemConfig()->timeformat,$item['paymenttime'] ? $item['paymenttime'] : $item['time']).'</td>';
			$content .=  '<td>'.$item['maxuse'].'</td>';
			$content .=  '<td><a href="'.self::getActionLink('editvoucher', $this->id).'&vid='.$item['id'].'">ویرایش</a></td>';
			$content .=  '<td><input type="checkbox" name="delete[]" value="'.$item['id'].'"></td>';
			$content .=  '</tr>';
		}
		$content .= '</table><div style="text-align: left"><input type="submit" value="ذخیره" name="update"><input type="submit" value="حذف" name="remove"></div></form></div>';
	
		$controller->renderWithContent($content, array('message'=>$message));
	}
	
}
