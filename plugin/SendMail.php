<?php
class SendMail extends Plugin
{
	public static function getData()
	{
		return array(
			'name'=>'ارسال ایمیل',
			'note'=>'ارسال ایمیل به مشتری بعد از خرید',
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
		$range = array();
		foreach ($inputs as $input)
		{
			$range[$input['id']] = $input['name'];
		}
		
		return array(
			'input'=>array('name'=>'فیلد ورودی','type'=>'select','range'=>$range),
			'from'=>array('name'=>'ایمیل فرستنده'),
			'fromname'=>array('name'=>'نام فرستنده'),
			'subject'=>array('name'=>'موضوع ایمیل'),
			'signature'=>array('name'=>'امضا','type'=>'textarea'),
			'smtpauth'=>array('name'=>'ارسال با SMTP','type'=>'select','range'=>array(
				0=>'غیر فعال',
				1=>'فعال')
			),
			'smtpusername'=>array('name'=>'نام کاربری SMTP'),
			'smtppassword'=>array('name'=>'کلمه عبور SMTP','type'=>'password'),
			'smtphost'=>array('name'=>'سرور SMTP'),
			'smtpport'=>array('name'=>'پورت SMTP'),
			'smtpsecure'=>array('name'=>'امنیت SMTP'),
		);
	}
	
	public function register($eventHandler)
	{
		$eventHandler->attach(Application::EVENT_AFTER_PAYMENT, array(
			$this,
			'sendingMail'
		));
	}
	
	public function sendingMail(&$payment,&$items)
	{
		$body = '<meta charset="utf-8"><div style="direction: rtl; width: 500px; margin-top: 20px; margin-right: auto; margin-bottom: 20px; margin-left: auto;">';
		foreach ($items as $item)
		{
			$body .= '<div style="direction: rtl; font-family: tahoma; margin-top: 20px;">
				<div style="direction: rtl; height: 25px; line-height: 27px; text-align: center; color: #fff; font-size: 16px; border-top-color: #eaeaea; border-right-color: #eaeaea; border-bottom-color: #eaeaea; border-left-color: #eaeaea; border-top-style: solid; border-right-style: solid; border-bottom-style: none; border-left-style: solid; border-top-width: 3px; border-right-width: 3px; border-bottom-width: 3px; border-left-width: 3px; background-color: #10BBE6;" align="center">'.$item[0]['name'].'</div>
				<div style="direction: rtl; border-top-color: #eaeaea; border-right-color: #eaeaea; border-bottom-color: #eaeaea; border-left-color: #eaeaea; border-top-style: dashed; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-width: 3px; border-right-width: 3px; border-bottom-width: 3px; border-left-width: 3px;">
					<table style="width: 100%; border-collapse: collapse; border-top-style: hidden; border-left-style: hidden; border-right-style: hidden; border-bottom-style: hidden; table-layout: fixed;">';
			$td = $th = '';
			foreach($item as $i)
			{
				if (!isset($i['value']))
				{
					continue;
				}
				$th .= '<th style="height: 20px; text-align: center; white-space: nowrap; border-top-color: #b7b7b7; border-right-color: #b7b7b7; border-bottom-color: #b7b7b7; border-left-color: #b7b7b7; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px; background-color: #ccc;" align="center" bgcolor="#ccc">'.$i['fieldname'].'</th>';
				$td .= '<td style="height: 30px; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; vertical-align: middle; border-top-color: #b7b7b7; border-right-color: #b7b7b7; border-bottom-color: #b7b7b7; border-left-color: #b7b7b7; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px;" align="center" valign="middle">'.Item::proccess($i['type'], $i['value']).'</td>';
			}
			$body .=  '<tr>'.$th.'</tr>';
			$body .=  '<tr>'.$td.'</tr>';
		
			$body .= '</table>	
				</div>
				<div class="bottom" style="direction: rtl; font-size: 14px; font-style: italic; padding-top: 4px; padding-right: 4px; padding-bottom: 4px; padding-left: 4px;">'.$item[0]['description'].'</div>';
		}
		$body .= '</div>';
		CShop::import(CShop::$librarypath.'/PHPMailer.php',true);
		if($this->signature)
			$body = $body.'<br/><hr>'.$this->signature;
		$mail = new PHPMailer(true);
		try {
			if($this->smtpauth==1 )
				$mail->IsSMTP();
			$mail->SMTPAuth = $this->smtpauth==1 ? true : false;
			$mail->Username = $this->smtpusername;
			$mail->Password = $this->smtppassword;
			$mail->Host = $this->smtphost;
			$mail->Port = $this->smtpport;
			$mail->SMTPSecure = $this->smtpsecure;
			$mail->AddReplyTo($this->from, $this->fromname);
			$mail->SetFrom($this->from, $this->fromname);
			$mail->AddAddress($payment['input'][$this->input]['value'], $payment['input'][$this->input]['value']);
			$mail->CharSet = 'UTF-8';
			$mail->Subject = $this->subject;
			$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
			$mail->MsgHTML($body);
			$mail->Send();
			return true;
		} catch (phpmailerException $e) {
			$return =  $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
			$return =  $e->getMessage(); //Boring error messages from anything else!
		}
		return $return;
	}

}