<?php
/**
 * Sibapal Gateway
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package components.gateway
 */
class Sibapal extends GatewayBase
{

	protected $merchantID;
	protected $title;
	protected $bank;
	
	public static function getData()
	{
		return array (
				'name' => 'پرداخت آنلاین با سیبا پال',
				'note' => 'سیبا پال',
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
				'merchantID' => array('name'=>'کد پذیرنده' ),
				'title' => array('name'=>'عنوان خرید' ),
				'bank' => array('name'=>'بانک','type'=>'select','range'=>array(
						''=>'خودکار',
						'mellat'=>'ملت',
						'melli'=>'ملی',
						'saman'=>'سامان',
						'fanava'=>'فن آوا',
						'mabna'=>'مبنا',
						'novin'=>'نوین',
				)
			),
		);
	}
	
	/**
	 * @param Payment $payment
	 * @see GatewayBase::sendToGateway()
	 */
	public function sendToGateway($payment, $callback)
	{
		
		$merchantID 	= trim($this->merchantID);
		$amount 		= round($payment[amount]/10);
		$invoice_id		= $payment['id'];
		$callBackUrl 	= $callback;
		
		if ($this->bank == '')
		{
			$bank = NULL;
		}
		else
		{
			$bank = $this->bank;
		}
		
		$client = new nusoap_client('https://www.sibapal.com/payment/wsdl?wsdl', 'wsdl');
		$res = $client->call('request', array($merchantID, $amount, $callBackUrl,$invoice_id, urlencode($this->title),$bank,'0'));
		if (strlen($res) > 4)
		{
			$sql = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('payment')->set('reference = ?')->where('id = ?'));
				
			$sql->execute(array($res,$payment['id']));
			
			Cshop::app()->redirect('https://www.sibapal.com/pay?au='.$res);
		}
		else
		{
			$data = array();
			$data['status'] = 'error';
			$data['content'] = 'در ارتباط با درگاه Sibapal مشکلی به وجود آمده است.';
			$data['message'] = '<font color="red">خطا در اتصال به سیباپال کد خطا</font>'.$res;
			return $data;
		}
	}

	public function callbackGateway()
	{
		$merchantID = $this->merchantID;
		$au 	= preg_replace('/[^a-z0-9]/','',$_GET['au']);
		$ref_id = $_GET['order_id'];
		if (strlen($au)>4)
		{
			$payment = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('payment')->where('id = ?'));
			$payment->execute(array($au));
			$payment = $payment->fetch();
			
			$amount		= round($payment['amount']/10);
			$client = new nusoap_client('https://www.sibapal.com/payment/wsdl?wsdl', 'wsdl');
			$res = $client->call("verify", array($merchantID,  $amount,$au));
			if ($payment['status'] == Application::STATUS_PENDING)
			{
				if ( ! empty($res) and $res == 1)
				{
					return $payment;
				}
				else
				{
					$message= 'پرداخت توسط سیباپال انجام نشده است .';
				}
			}
			else
			{
				$message= 'سفارش قبلا پرداخت شده است.';
			}
		}
		else
		{
			$message = 'شماره یکتا اشتباه است.';
		}
		throw new Exception($message);
	}

}