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

	protected $AccountNumber;

	public static function getData()
	{
		return array(
				'name' => 'پرداخت آنلاین با Sharjiran',
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
						'AccountNumber' => 'شماره حساب شارژ ايران'
				)
		);
	}

	/**
	 *
	 * @param Payment $payment        	
	 * @see GatewayBase::sendToGateway()
	 */
	public function sendToGateway($payment, $callback)
	{
		$AccountNumber = trim($this->AccountNumber);
		$Amount = round($payment['amout']);
		$RequestId = $payment['id'];
		$BackUrl = $callback;
		
		echo "<form name='myform' method='post' action='http://www.sharjiran.net/sharjiran/PayInfo.php' >
	         <input type='hidden' name='AccountNumber' value=$AccountNumber >
	         <input type='hidden' name='Amount' value=$Amount >
	         <input type='hidden' name='RequestId' value=$RequestId >
	         <input type='hidden' name='BackUrl' value=$BackUrl >
             </form>
			 <script language='javascript'>document.myform.submit();</script>";
		CShop::app()->end();
	}

	public function callbackGateway()
	{
		$result = $_POST['Result'];
		$requestId = $_POST['RequestId'];
		$followCode = $_POST['FollowCode'];
		
		$payment = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('payment')->where('reference = ?'));
		$payment->execute(array($requestId));
		
		$amount = round($payment['amout']);
		
		if($payment['status'] == Application::STATUS_PENDING)
		{
			if($result == 0) // -- موفقیت آمیز
			{
				if($_POST[Amount] != $payment['amout'])
				{
					$message = 'در اطلاعات پرداختي مغايرت وجود دارد';
				} else
				{
					$backResult = file("http://www.sharjiran.net/asan_pardakht/CheckPay2.php?FollowCode=$followCode&RequestId=$requestId");
					if($backResult[0] == 0 &&
							 (int) $backResult[1] ==
							 (int) $payment['amout'] &&
							 $backResult[2] == trim($this->AccountNumber))
					{
						return $payment;
					} else
					{
						$message = 'تراكنش موفقيت آميز نبود';
					}
				}
			} 

			else 
				if($result == - 1)
				{
					$message = 'در اتصال به درگاه بانك مشكلي پيش آمده يا اينكه اطلاعات پرداختي شما نامعتبر بوده است. ';
				}
		} else
		{
			$message = 'سفارش قبلا پرداخت شده است.';
		}
		throw new Exception($message);
	}
}
