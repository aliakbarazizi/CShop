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
	
	public static function getData()
	{
		return array (
				'name' => 'پرداخت آنلاین با Jahanpay',
				'note' => 'payline',
				'author' => array (
						'name' => 'Ir-prog',
						'url' => 'http://ir-prog.ir',
						'email' => 'admin@ir-prog.ir' 
				) 
		);
	}

	public static function getParameters()
	{
		return array (
				'merchant' => array('name'=>'شناسه درگاه' ),
				'pass' => array('name'=>'رمز' ),
				'title' => array('name'=>'عنوان خرید' ),
		);
	}

	/**
	 * @param Payment $payment
	 * @see GatewayBase::sendToGateway()
	 */
	public function sendToGateway($payment, $callback)
	{
		$ParspalPin 	= trim($this->merchant);
		$pass			= trim($this->pass);
		$amount 		= round($payment['amount']/10);
		$invoice_id		= $payment['id'];
		$callBackUrl 	= $callback;
		
		$params = array(
				"MerchantID" =>$ParspalPin,
				"Password"=>$pass,
				"Price" => $amount,
				"ReturnPath" => $callBackUrl,
				"ResNumber" => $invoice_id,
				"Description"=>urlencode($this->title),
				"Paymenter"=>urlencode('کاربر'),
				"Email"=>$payment[payment_email],
				"Mobile"=>$payment[payment_mobile]
		);
			
		$link = file_get_contents('http://www.gold-host.ir/payment.php?'.http_build_query($params));
		$link = json_decode($link,true);
		$PayPath = $link['PaymentPath'];
		$Status = $link['ResultStatus'];
		if(strtolower($Status) == 'succeed')
		{
			$update[payment_rand]		= $invoice_id;
			$sql = $db->prepare("UPDATE `payment` SET `payment_rand` = ? WHERE `payment_rand` = ? LIMIT 1");
			$sql->execute(array($update[payment_rand],$invoice_id));
			redirect_to($PayPath);
		
		}
		else
		{
				
			$data[message] = '<font color="red">در اتصال به درگاه پارس پال مشکلی پیش آمد دوباره امتحان کنید و یا به پشتیبانی خبر دهید</font>'.$Status.'<br /><a href="index.php" class="button">بازگشت</a>';
			throw new Exception($data[message] );
				
		}
		
		
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
		global $db,$get;
		$Status = $_POST['status'];
		$Refnumber = $_POST['refnumber'];
		$Resnumber = $_POST['resnumber'];
		
		if ($Status == 100)
		{
			include_once('lib/nusoap.php');
			$ParspalPin 	= trim($data[merchant]);
			$pass		= $data[pass];
			$sql 		= 'SELECT * FROM `payment` WHERE `payment_rand` = ? LIMIT 1;';
			$sql = $db->prepare($sql);
			$sql->execute(array($Resnumber));
			$payment 	= $sql->fetch();
			$amount		= round($payment[payment_amount]/10);
			$params = array(
					'MerchantID' => $ParspalPin,
					'Password' =>$pass,
					'Price' => $amount,
					'RefNum' =>$Refnumber
			) ;
		
			$link = file_get_contents('http://www.gold-host.ir/payment.php?'.http_build_query($params));
			$link = json_decode($link,true);
			$Status =$link['ResultStatus'];
			echo $link['ResultStatus'];
			var_dump($link);
			if (strtolower($Status)=='success')//-- موفقیت آمیز
			{
				var_dump('test');
				//-- آماده کردن خروجی
				$output[status]		= 1;
				$output[res_num]	= $Resnumber;
				$output[ref_num]	= $Refnumber;
				$output[payment_id]	= $payment[payment_id];
				var_dump($output);
			}
			else
			{
				//-- در تایید پرداخت مشکلی به‌وجود آمده است‌
				$output[status]	= 0;
				$output[message]= 'پرداخت ناموفق است. خطا';
			}
		
		}
		else
		{
			$output[status]	= 0;
			$output[message]= 'پرداخت ناموفق است. خطا';
		}
		return $output;
		
		
		
		
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
