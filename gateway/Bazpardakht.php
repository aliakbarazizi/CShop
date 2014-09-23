<?php
class Bazpardakht extends GatewayBase
{

	protected $merchantID;

	public static function getData()
	{
		return array (
				'name' => 'پرداخت آنلاین با Bazpardakht',
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
				'merchantID' => array('name'=>'کد پذیرنده' ),
		);
	}

	private function senddata_bazpardakht($url,$id,$order_id,$amount,$redirect){
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POSTFIELDS,"id=$id&resnum=$order_id&amount=$amount&callback=$redirect");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}
	
	/**
	 * @param Payment $payment
	 * @see GatewayBase::sendToGateway()
	 */
	public function sendToGateway($payment, $callback)
	{
		$id = $this->merchantID;
		$amount = $payment[amount]/10;//rial be toman
		$redirect = $callback;
		$order_id= $payment['id'];
		$url = 'http://bazpardakht.com/webservice/index.php';
		$result = $this->senddata_bazpardakht($url,$id,$order_id,$amount,$redirect);
		if ($result > 0 && is_numeric($result))
		{
			$go = "http://bazpardakht.com/webservice/go.php?id=$result";
			Cshop::app()->redirect($go);
		}
		else
		{
			$data = array();
			$data['status'] = 'error';
			$data['content'] = 'در ارتباط با درگاه bazpardakht.com مشکلی به وجود آمده است.';
			$data['message'] = '<font color="red">در ارتباط با درگاه bazpardakht.com مشکلی به وجود آمده است. لطفا مطمئن شوید کد MerchantID خود را به درستی در قسمت مدیریت وارد کرده اید.</font> شماره خطا: '.$result;
			return $data;
		
		}
	}

	public function callbackGateway()
	{
		$api = $this->pin;
		$url = 'http://payline.ir/payment-test/gateway-result-second';
		$trans_id = $_POST['trans_id'];
		$id_get = $_POST['id_get'];
		$result = $this->get($url, $api, $trans_id, $id_get);
		if ($result == 1)
		{
			$payment = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('payment')->where('reference = ?'));
			$payment->execute(array($id_get));
			$payment = $payment->fetch();
			if ($payment)
			{
				return $payment;
			}
			else
			{
				$message= 'اطلاعات پرداخت کامل نیست.';
			}
		}
		else
		{
			$message = 'پرداخت موفقيت آميز نبود';
		}
		throw new Exception($message);
		
		
		if($_POST['status']!='1'){
			$message = 'پرداخت با موفقيت انجام نشده است.';
			throw new Exception($message);
		}
		$refID = $_POST['refnum'];
		$resCode = $_POST['resnum'];
		$id = $this->merchantID;
		
		$payment = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->select()->from('payment')->where('id = ?'));
		$payment->execute(array($resCode));
		$payment = $payment->fetch();
		
		if ($payment['status'] == Application::STATUS_PENDING)
		{
			$amount = $payment['amount'];
			$url = 'http://bazpardakht.com/webservice/verify.php';
			$fields = array(
					'id'=>urlencode($id),
					'resnum'=>urlencode($resCode),
					'refnum'=>urlencode($refID),
					'amount'=>urlencode($amount/10),//rial be toman
			);
		
			//url-ify the data for the POST
			$fields_string = "";
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string,'&');
		
			//open connection
			$ch = curl_init($url);
		
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_POST,count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			//execute post
			$result = curl_exec($ch);
			curl_close($ch);
			$result=intval($result);
			$pay = false;
			if( $result <= 0 ) {
				$pay = false;
			} elseif($result == '1') {
				$pay = true;
			}
			///////////////////
				
			if($pay)
			{
				return $payment;
			}
			else
			{
				$message = 'خطا در پرداخت';
			}
		}
		else
		{
			$message= 'این سفارش قبلا پرداخت شده است.';
		}
		
		throw new Exception($message);
	}

}
