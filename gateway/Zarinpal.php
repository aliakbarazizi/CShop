<?php

/**
 * Parspal Gateway
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package components.gateway
 */
class Parspal extends GatewayBase
{

	protected $merchant;

	protected $title;

	public static function getData()
	{
		return array(
				'name' => 'پرداخت آنلاین با Zarinpal',
				'note' => 'payline',
				'author' => array(
						'name' => 'Ir-prog',
						'url' => 'http://irprog.com',
						'email' => 'admin@ir-prog.ir'
				)
		);
	}

	public static function getParameters()
	{
		
		return array(
				'merchant' => array(
						'name' => 'شناسه درگاه'
				),
				'pass' => array(
						'name' => 'رمز'
				),
				'title' => array(
						'name' => 'عنوان خرید'
				),
		);
	}

	/**
	 *
	 * @param Payment $payment        	
	 * @see GatewayBase::sendToGateway()
	 */
	public function sendToGateway($payment, $callback)
	{
	global $config,$db,$smarty;
		include_once('include/libs/nusoap.php');
		$merchantID 	= trim($this->merchant);
		$amount 		= round($payment['amount']/10);
		$invoice_id		= $payment['id'];
		$callBackUrl 	= $callback;
		
		$client = new nusoap_client('https://de.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
		$client->soap_defencoding = 'UTF-8';
		$res = $client->call("PaymentRequest", array(
													array(
															'MerchantID' 	=> $merchantID,
															'Amount' 		=> $amount,
															'Description' 	=> $this->title.' - '.$payment['id'],
															'Email' 		=> '',
															'Mobile' 		=> '',
															'CallbackURL' 	=> $callBackUrl
														)
													)
		);
	
		if ($res['Status'] == 100)
		{
			$sql = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('payment')->set('reference = ?')->where('id = ?'));
				
			$sql->execute(array($res[Authority],$payment['id']));
				
			Cshop::app()->redirect('https://www.zarinpal.com/pg/StartPay/'.$res['Authority']);
		}
		else
		{
			$data = array();
			$data['status'] = 'error';
			$data['content'] = '<font color="red">در اتصال به درگاه زرین‌پال مشکلی به وجود آمد٬ لطفا از درگاه سایر بانک‌ها استفاده نمایید.</font>'.$res['Status'].'<br /><a href="index.php" class="button">بازگشت</a>';
			return $data;
		}
	}
	
	
	public function callbackGateway()
	{
		$Authority 	= $_GET['Authority'];
		$ref_id = $_GET['refID'];
		if ($_GET['Status'] == 'OK')
		{
			$payment = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('payment')->where('reference = ?'));
			$payment->execute(array($Authority));
			
			$amount		= round($payment['amout']/10);
			$client = new nusoap_client('https://de.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
			$res = $client->call("PaymentVerification", array(
															array(
																	'MerchantID'	 => $this->merchant,
																	'Authority' 	 => $Authority,
																	'Amount'	 	 => $amount
																)
															)
		);
			if ($payment[payment_status] == Application::STATUS_PENDING)
			{
				if ($res['Status'] == 100)
				{
					return $payment;
				}
				else
				{
					$message= 'پرداخت توسط زرین‌پال تایید نشد‌.'.$res['Status'];
				}
			}
			else
			{
				$message= 'سفارش قبلا پرداخت شده است.';
			}
		}
		else
		{
				$message= 'شماره یکتا اشتباه است.';
		}
		throw new Exception($message);
	}
}
