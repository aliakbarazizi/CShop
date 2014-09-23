<?php
class Jahanpay extends GatewayBase
{

	protected $merchant;
	protected $title;

	public static function getData()
	{
		return array (
				'name' => 'پرداخت آنلاین با Jahanpay',
				'note' => 'payline',
				'author' => array (
						'name' => 'Ir-prog',
						'url' => 'http://irprog.com',
						'email' => 'cshop@irprog.com' 
				)
		);
	}

	public static function getParameters()
	{
		return array (
				'merchant' => array('name'=>'API' ),
				'title' => array('name'=>'عنوان خرید' ),
		);
	}

	/**
	 * @param Payment $payment
	 * @see GatewayBase::sendToGateway()
	 */
	public function sendToGateway($payment, $callback)
	{
		
		$merchantID 	= trim($this->merchant);
		$amount 		= round($payment['amount']/10);
		$invoice_id		= $payment['id'];
		$callBackUrl 	= $callback;
		
		$client = new nusoap_client('http://jahanpay.com/webservice?wsdl', 'wsdl');
		$res = $client->call('requestpayment', array($merchantID, $amount, $callBackUrl,$invoice_id, urlencode($this->title)));
		if ($res > 0)
		{
			$sql = CShop::app()->getDb()->prepare("UPDATE `payment` SET `reference` = ? WHERE `reference` = ? LIMIT 1");
			$sql->execute(array (
					$res,
					$invoice_id
			));
			Cshop::app()->redirect('http://jahanpay.com/pay_invoice/' . $res);
		
		}
		else
		{
			$data = array();
			$data['status'] = 'error';
			$data['content'] = 'خطا در اتصال به جهان پی کد خطا' ;
			$data['message'] = '<font color="red">خطا در اتصال به جهان پی کد خطا</font>' . $res ;
			return $data;
		}
	}

	public function callbackGateway()
	{
		$au =  $_GET['au'];
		$ref_id = $_GET['order_id'];
		if (strlen($au)>4)
		{
			$payment = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('payment')->where('reference = ?'));
			$payment->execute(array($au));
			$payment = $payment->fetch();
			
			$merchantID = $this->merchant;
		
			$amount		= round($payment[payment_amount]/10);
			$client = new nusoap_client('http://jahanpay.com/webservice?wsdl', 'wsdl');
			$res = $client->call("verification", array($merchantID,  $amount,$au));
			if ($payment['status'] == Application::STATUS_PENDING)
			{
				if ( ! empty($res) and $res == 1)
				{
					return $payment;
				}
				else
				{
					$message = 'پرداخت توسط جهان پی انجام نشده است .';
				}
			}
			else
			{
				$message = 'سفارش قبلا پرداخت شده است.';
			}
		}
		else
		{
			$message = 'شماره یکتا اشتباه است.';
		}
		throw new Exception($message);
	}
}
