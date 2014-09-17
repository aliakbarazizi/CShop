<?php
class User
{
	private $user_type;
	
	private $prefix;
	
	private $loggedin ;
	
	private $userid;
	
	private $message;
	
	function __construct ($prefix='user')
	{
		$this->prefix = $prefix;
		$this->checkMessage();
		
	}
	/**
	 * Be Carefull, if $redirect be null, you must check return value
	 * @param string $redirect
	 * @return boolean
	 *
	 */
	public function isLogin($redirect=NULL)
	{
		if (!isset($this->logged_in))
		{
			$this->checkLogin();
		}
		if($this->loggedin == false)
		{
			if($redirect)
			{
				CShop::app()->redirect($redirect);
			}
		}
		return $this->loggedin;
	}
	
	/**
	 *
	 * @param admin $admin
	 * @param string $type
	 */
	public function login($userid,$param=array())
	{
		$_SESSION[$this->prefix.'login'] = true;
		$this->userid = $_SESSION[$this->prefix.'userid'] = $userid;
		$_SESSION[$this->prefix.'param'] = $param;
		$this->loggedin = true;
		$_SESSION[$this->prefix.'timeout'] = time() + 20*60*60;
		$_SESSION[$this->prefix.'userhash'] = sha1($_SERVER['HTTP_USER_AGENT']);
	}
	
	public function logout()
	{
		unset($_SESSION[$this->prefix.'login']);
		unset($_SESSION[$this->prefix.'userid']);
		unset($_SESSION[$this->prefix.'param']);
		unset($_SESSION[$this->prefix.'timeout']);
		unset($_SESSION[$this->prefix.'userhash']);
	}
	
	public function message($msg = "")
	{
		if (! empty($msg))
		{
			$_SESSION[$this->prefix.'message'] = $msg;
		}
		else
		{
			return $this->message;
		}
	}
	
	private function checkLogin()
	{
	
		$this->userid = $_SESSION[$this->prefix.'userid'];

		if ($this->userid && isset($_SESSION[$this->prefix.'timeout']) && $_SESSION[$this->prefix.'timeout'] > time() && $_SESSION[$this->prefix.'userhash'] == sha1($_SERVER['HTTP_USER_AGENT']))
		{
			$this->loggedin = true;
		}
		else
		{
			$this->loggedin = false;
		}
	}
	
	private function checkMessage()
	{
		if (isset($_SESSION[$this->prefix.'message']))
		{
			$this->message = $_SESSION[$this->prefix.'message'];
			unset($_SESSION[$this->prefix.'message']);
		}
		else
		{
			$this->message = "";
		}
	}
}