<?php

/**
 * Sibapal Gateway
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package components.gateway
 */
class Sibapalp extends GatewayBase
{

	protected $merchantID;

	public static function getData()
	{
		return array(
				'name' => 'پرداخت آنلاین با سیبا پال - اختصاصی',
				'note' => 'سیبا پال',
				'author' => array(
						'name' => 'Ir-prog',
						'url' => 'http://irprog.com',
						'email' => 'cshop@irprog.com'
				)
		);
	}

	public static function getParameters()
	{
		return array(
				'merchantID' => array(
						'name' => 'کد پذیرنده'
				)
		);
	}

	function request($pin = '', $amount = '', $callback = '', $order_id = 0)
	{
		$params = array(
				'pin' => $pin,
				'amount' => $amount,
				'callback' => urlencode($callback),
				'order_id' => (int) $order_id
		);
		
		$params = json_encode($params);
		$do = curl_init();
		curl_setopt($do, CURLOPT_URL, 
				"https://sibapal.com/Eserviceapi/request?params={$params}");
		curl_setopt($do, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($do, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($do, CURLOPT_CONNECTTIMEOUT, 20);
		$response = curl_exec($do);
		curl_close($do);
		return json_decode($response, true);
	}

	function verify($pin = '', $amount = '', $au = '', $order_id = 0, 
			$bank_return = array())
	{
		$params = array(
				'pin' => $pin,
				'amount' => $amount,
				'au' => $au,
				'order_id' => (int) $order_id,
				'bank_return' => ! empty($bank_return) ? $bank_return : ($_POST +
						 $_GET)
		);
		
		$params['bank_return'] = base64_encode(
				json_encode($params['bank_return']));
		$params = json_encode($params);
		
		$do = curl_init();
		curl_setopt($do, CURLOPT_URL, 
				"https://sibapal.com/Eserviceapi/verify?params={$params}");
		curl_setopt($do, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($do, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($do, CURLOPT_CONNECTTIMEOUT, 20);
		$response = curl_exec($do);
		curl_close($do);
		return json_decode($response, true);
	}

	/**
	 *
	 * @param Payment $payment        	
	 * @see GatewayBase::sendToGateway()
	 */
	public function sendToGateway($payment, $callback)
	{
		$pin = $this->merchantID;
		$amount = round($payment['amount'] / 10);
		$order_id = $payment['id'];
		$callback = $callback;
		
		$res = $this->request($pin, $amount, $callback, $order_id);
		if($res['result'] == 1)
		{
			$sql = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('payment')->set('reference = ?')->where('id = ?'));
			$sql->execute(array($res['au'],$payment['id']));
			
			$_SESSION['siba_au'] = $res['au'];
			$_SESSION['invoice_id'] = $order_id;
			echo "<div style='display:none'>{$res['form']}</div><br>Please wait ... <script language='javascript'>document.siba.submit(); </script>";
			exit();
		} else
		{
			$data = array();
			$data['status'] = 'error';
			$data['message'] = '<font color="red">خطا در اتصال به سیبا پال <br /> شرح خطا : ' .
					 urldecode($res['msg']) .
					 '</font><br /><a href="index.php" class="button">بازگشت</a>';
			return $data;
		}
	}

	public function callbackGateway()
	{
		
		$au = $_SESSION['siba_au'];
		$order_id = $_SESSION['invoice_id'];
		$pin = $this->merchantID;
		
		$payment = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('payment')->where('reference = ?'));
		$payment->execute(array($au));
		$payment = $payment->fetch();
		
		$amount = round($payment['amount'] / 10);
		$bank_return = $_POST + $_GET;
		
		$res = $this->verify($pin, $amount, $au, $order_id, $bank_return);
		
		if($payment['status'] == Application::STATUS_PENDING)
		{
			if(empty($res))
			{
				$message = 'خطا در اتصال به سرور !';
			} elseif($res['result'] == 1)
			{
				return $payment;
				
			} else
			{
				$message = 'پرداخت  انجام نشده است . <br /> شرح خطا : ' .
						 urldecode($res['msg']);
			}
		} else
		{
			$message = 'سفارش قبلا پرداخت شده است.';
		}
		
		throw new Exception($message);
	}
}