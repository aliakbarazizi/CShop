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

	protected $pass;

	protected $title;

	protected $email;

	protected $mobile;

	public static function getData()
	{
		return array(
				'name' => 'پرداخت آنلاین با Parspal',
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
		$inputs = CShop::app()->getDb()
			->query(
				QueryBuilder::getInstance()->select()
					->from('input')
					->order('`order`'))
			->fetchAll();
		$range = array(
				'' => 'میتوانید خالی باشد'
		);
		foreach($inputs as $input)
		{
			$range[$input['id']] = $input['name'];
		}
		
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
				'email' => array(
						'name' => 'فیلد ایمیل',
						'type' => 'select',
						'range' => $range
				),
				'mobile' => array(
						'name' => 'فیلد شماره تماس',
						'type' => 'select',
						'range' => $range
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
		$ParspalPin = trim($this->merchant);
		$pass = trim($this->pass);
		$amount = round($payment['amount'] / 10);
		$invoice_id = $payment['id'];
		$callBackUrl = $callback;
		
		$soapclient = new nusoap_client(
				'http://merchant.parspal.com/WebService.asmx?wsdl', 'wsdl');
		$params = array(
				"MerchantID" => $ParspalPin,
				"Password" => $pass,
				"Price" => $amount,
				"ReturnPath" => $callBackUrl,
				"ResNumber" => $invoice_id,
				"Description" => urlencode($this->title),
				"Paymenter" => urlencode('کاربر'),
				"Email" => $payment[payment_email],
				"Mobile" => $payment[payment_mobile]
		);
		$res = $soapclient->call('RequestPayment', $params);
		$PayPath = $res['RequestPaymentResult']['PaymentPath'];
		$Status = $res['RequestPaymentResult']['ResultStatus'];
		if(strtolower($Status) == 'succeed')
		{
			Cshop::app()->redirect($PayPath);
		} else
		{
			$data = array();
			$data['status'] = 'error';
			$data['message'] = '<font color="red">در اتصال به درگاه پارس پال مشکلی پیش آمد دوباره امتحان کنید و یا به پشتیبانی خبر دهید</font>' .$Status ;
			return $data;
		}
	}
	
	
	public function callbackGateway()
	{
		global $db, $get;
		$Status = $_POST['status'];
		$Refnumber = $_POST['refnumber'];
		$Resnumber = $_POST['resnumber'];
		if($Status == 100)
		{
			
			$ParspalPin = trim($this->merchant);
			$pass = $this->pass;
			
			
			$payment = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('payment')->where('reference = ?'));
			$payment->execute(array($Resnumber));
			$payment = $payment->fetch();

			$amount = round($payment['amount'] / 10);
			$soapclient = new nusoap_client(
					'http://merchant.parspal.com/WebService.asmx?wsdl', 'wsdl');
			$params = array(
					'MerchantID' => $ParspalPin,
					'Password' => $pass,
					'Price' => $amount,
					'RefNum' => $Refnumber
			);
			$res = $soapclient->call('verifyPayment', $params);
			$Status = $res['verifyPaymentResult']['ResultStatus'];
			if(strtolower($Status) == 'success') // -- موفقیت آمیز
			{
				return $payment;
				
			}
			else
			{
				$message = 'پرداخت ناموفق است. خطا';
			}
		} else
		{
			$message = 'پرداخت ناموفق است. خطا';
		}
		throw new Exception($message);
	}
}
