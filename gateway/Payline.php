<?php
class Payline extends GatewayBase
{

	protected $pin;

	public static function getData()
	{
		return array (
				'name' => 'پرداخت آنلاین با Payline',
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
				'pin' => array('name'=>'لطفا API خود را در فیلد زیر وارد نمایید' ),
		);
	}

	/**
	 * @param Payment $payment
	 * @see GatewayBase::sendToGateway()
	 */
	public function sendToGateway($payment, $callback)
	{
		$api = $this->pin;
		$amount = $payment['amount'];
		$redirect = $callback;
		$order_id = $payment['id'];
		$url = 'http://payline.ir/payment-test/gateway-send';
		$result = $this->send($url, $api, $amount, $redirect);
		
		if ($result > 0 && is_numeric($result))
		{
			$sql = Cshop::app()->getDb()->prepare(QueryBuilder::getInstance()->update('payment')->set('reference = ?')->where('id = ?'));
			
			$sql->execute(array($result,$payment['id']));
			
			$go = "http://payline.ir/payment-test/gateway-$result";
			Cshop::app()->redirect($go);
		}
		else
		{
			$data = array();
			$data['status'] = 'error';
			$data['content'] = 'در ارتباط با درگاه Payline مشکلی به وجود آمده است. لطفا مطمئن شوید کد API خود را به درستی در قسمت مدیریت وارد کرده اید.شماره خطا: ' . $result ;
			$data['message'] = '<font color="red">در ارتباط با درگاه Payline مشکلی به وجود آمده است. لطفا مطمئن شوید کد API خود را به درستی در قسمت مدیریت وارد کرده اید.</font> شماره خطا: ' . $result ;
			return $data;
		}
	}

	public function callbackGateway()
	{
		global $db, $get, $smarty;
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
	}

	private function send($url, $api, $amount, $redirect)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "api=$api&amount=$amount&redirect=$redirect");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}

	private function get($url, $api, $trans_id, $id_get)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "api=$api&id_get=$id_get&trans_id=$trans_id");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}
}
