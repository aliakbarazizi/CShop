<?php
/**
 * Freer Plugin
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package components.plugin
 */
class Freer extends Plugin
{
	public static function getActions()
	{
		return array('شروع انتفال'=>'import');		
	}

	public static function getData()
	{
		return array(
			'name'=>'ورود اطلاعات از فریر',
			'note'=>'ورود اطلاعات از فریر',
			'author'=>array(
				'name'=>'Ir-prog',
				'url'=>'http://ir-prog.ir',
				'email'=>'admin@ir-prog.ir',
			)
		);
	}
	public static function install($id)
	{

	}
	public static function getParameters()
	{
		$inputs = CShop::app()->getDb()->query(QueryBuilder::getInstance()->select()->from('input')->order('`order`'))->fetchAll();
		$range = array(''=>'میتوانید خالی باشد');
		foreach ($inputs as $input)
		{
			$range[$input['id']] = $input['name'];
		}
		return array(
			'dbserver'=>array('name'=>'سرور دیتابیس فریر'),
			'dbname'=>array('name'=>'نام دیتابیس فریر'),
			'dbusername'=>array('name'=>'نام کاربری دیتابیس'),
			'dbpassword'=>array('name'=>'کلمه عبور دیتابیس'),
			'salt'=>array('name'=>'کلید امنیتی فریر'),
			'email'=>array('name'=>'فیلد ایمیل','type'=>'select','range'=>$range),
			'mobile'=>array('name'=>'فیلد شماره تماس','type'=>'select','range'=>$range),
		);
	}
	
	public function register($eventHandler)
	{

	}

	/**
	 * @param Controller $controller
	 */
	public function actionImport($controller)
	{
		$content = '<div class="title">انتفال</div>';
		$db = CShop::app()->getDb();
		$lasterrormode = $db->getAttribute(PDO::ATTR_ERRMODE);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		if (isset($_POST['start']))
		{
			try {
				$freerdb = new Database(true,$this->dbname,$this->dbserver,$this->dbusername,$this->dbpassword);
				
				//Payment import
				$items = $freerdb->query(QueryBuilder::getInstance('')->select()->from('payment'));
				$paymentids = array();
				while ($row = $items->fetch())
				{
					$sql = $db->prepare(QueryBuilder::getInstance()->insert('payment')->into(array('amount','requesttime','status','clientip','paymenttime','reference','gatewayid'),true,false));
					$status = $row['payment_status'] == 1 ? Application::STATUS_PENDING : Application::STATUS_COMPLETE;
						
					$sql->execute(array($row['payment_amount'],$row['payment_time'],$status,$row['payment_ip'],$row['payment_time'],$row['payment_res_num'],$this->gatewayid));
					$paymentids[$row['payment_id']] = $db->lastInsertId();

					if($this->email)
					{
						$sql = $db->prepare(QueryBuilder::getInstance()->insert('payment_meta')->into(array('inputid','paymentid','value'),true,false));
						$sql->execute(array($this->email,$paymentids[$row['payment_id']],$row['payment_email']));
					}
					
					if($this->mobile)
					{
						$sql = $db->prepare(QueryBuilder::getInstance()->insert('payment_meta')->into(array('inputid','paymentid','value'),true,false));
						$sql->execute(array($this->mobile,$paymentids[$row['payment_id']],$row['payment_mobile']));
					}
				}
				
				//Category import
				$items = $freerdb->query(QueryBuilder::getInstance('')->select()->from('category'));
				$categoryids = array();
				while ($row = $items->fetch())
				{
					$sql = $db->prepare(QueryBuilder::getInstance()->insert('category')->into(array('name','description','`order`'),true,false));
					$sql->execute(array($row['category_title'],'',$row['category_order']));
					$categoryids[$row['category_id']] = $db->lastInsertId();
				}
				
				//Product AND item import
				$items = $freerdb->query(QueryBuilder::getInstance('')->select()->from('product'));
				while ($row = $items->fetch())
				{
					$sql = $db->prepare(QueryBuilder::getInstance()->insert('product')->into(array('name','description','price','`order`','categoryid'),true,false));
					$sql->execute(array($row['product_title'],$row['product_body'],$row['product_price'],0,$categoryids[$row['product_category']]));
					$productid = $db->lastInsertId();
					
					$sql = $db->prepare(QueryBuilder::getInstance()->insert('field')->into(array('productid','name','type'),true,false));
					$sql->execute(array($productid,$row['product_first_field_title'],'text'));
					$field1 = $db->lastInsertId();
					
					$sql = $db->prepare(QueryBuilder::getInstance()->insert('field')->into(array('productid','name','type'),true,false));
					$sql->execute(array($productid,$row['product_second_field_title'],'text'));
					$field2 = $db->lastInsertId();
					
					$sql = $db->prepare(QueryBuilder::getInstance()->insert('field')->into(array('productid','name','type'),true,false));
					$sql->execute(array($productid,$row['product_third_field_title'],'text'));
					$field3 = $db->lastInsertId();
					
					
					$cards = $freerdb->query(QueryBuilder::getInstance('')->select("*,DECODE(card_first_field,'{$this->salt}') AS card_first_field,DECODE(card_second_field,'{$this->salt}') AS card_second_field,DECODE(card_third_field,'{$this->salt}') AS card_third_field")->from('card')->where('card_product='.$row['product_id']));
					
					while ($card = $cards->fetch())
					{
						//pament id
						$sql = $db->prepare(QueryBuilder::getInstance()->insert('item')->into(array('productid','status','createtime','paymentid'),true,false));
						$status = $card['card_status'] == 1 ? Application::STATUS_PENDING : Application::STATUS_COMPLETE;
						$sql->execute(array($productid,$status,$card['card_time'],$paymentids[$card['card_payment_id']]));
						
						$itemid = $db->lastInsertId();
						
						$sql = $db->prepare(QueryBuilder::getInstance()->insert('value')->into(array('fieldid','itemid','value'),true,false));
						$sql->execute(array($field1,$itemid,$card['card_first_field']));
						
						$sql = $db->prepare(QueryBuilder::getInstance()->insert('value')->into(array('fieldid','itemid','value'),true,false));
						$sql->execute(array($field2,$itemid,$card['card_second_field']));
						
						$sql = $db->prepare(QueryBuilder::getInstance()->insert('value')->into(array('fieldid','itemid','value'),true,false));
						$sql->execute(array($field3,$itemid,$card['card_third_field']));
					}
					
				}
				$content .= 'انتقال انجام شد';
			}
			catch (Exception $e)
			{
				
				$content .= 'خطا! '.$e->getMessage();
			}
		}
		else
			$content .= '<form method="post">
				<input type="submit" value="شروع" name="start">
			</form>';

		$db->setAttribute(PDO::ATTR_ERRMODE, $lasterrormode);
		$controller->renderWithContent($content);
	}
}