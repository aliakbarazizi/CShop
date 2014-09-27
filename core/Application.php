<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop.base
 */ 
class Application
{
	const STATUS_DISABLE = -1;
	const STATUS_PENDING = 0;
	const STATUS_COMPLETE = 1;
	const STATUS_ACTIVE = 1;
	const STATUS_DEACTIVE = 0;
	const STATUS_SYSTEM_ADDED = 2;
	
	const EVENT_BEFORE_ACTION = 'beforeaction';
	const EVENT_AFTER_ACTION = 'afteraction';
	const EVENT_BEFORE_RENDER = 'beforerender';
	const EVENT_AFTER_RENDER = 'afterrender';
	const EVENT_BEFORE_PAYMENT = 'beforepayment';
	const EVENT_AFTER_PAYMENT = 'afterpayment';
	const EVENT_END = 'end';
	const EVENT_MENU = 'menu';
	const EVENT_ITEM_TYPE = 'itemtype';
	
	const APPLICATON_CONFIG_CATEGORY = 'system';
	
	/**
	 *
	 * @var Database
	 */
	private $_db;
	private $_user;
	private $_cache;
	/**
	 * 
	 * @var SystemConfig
	 */
	private $_systemconfig;
	/**
	 * 
	 * @var EventHandler
	 */
	private $_eventHandler;
	
	private $_config;
	
	private $_action;
	
	private $_controller;
	
	private $_language = array();
	
	
	public function __construct($config)
	{
		$this->_config = $config;
	}
	
	public function __destruct()
	{
		$this->end();
	}
	
	public function getDb()
	{
		return $this->_db;
	}
	/**
	 * @return FileCache
	 */
	public function getCache()
	{
		return $this->_cache;
	}
	
	public function getConfig($name=null)
	{
		if ($name == null)
			return $this->_config;
		
		return $this->_config[$name];
	}
	
	public function getUser()
	{
		return $this->_user;
	}
	
	public function getEventHandler()
	{
		return $this->_eventHandler;
	}
	
	public function raise($name, $arguments=array())
	{
		return $this->_eventHandler->raise($name, $arguments);
	}
	
	public function initialise()
	{
		session_name('cshop');
		session_start();
		session_regenerate_id();

		
		$this->_db = new Database(true,$this->_config['database']['database'],$this->_config['database']['host'],$this->_config['database']['username'],$this->_config['database']['password']);
		$this->_eventHandler = new EventHandler();
		$this->_cache = new FileCache();
		
		if (! $plugins = $this->_cache->get('system__plugin'))
		{
			$sql = 	$this->_db->query(QueryBuilder::getInstance()->select('*')->from('plugin')->leftJoin('plugin_meta')->on('pluginid = plugin.id')->where('status = 1'));
			$plugins = array();
			while ($row = $sql->fetch())
			{
				$plugins[$row['class']][] = array('key'=>$row['key'],'value'=>$row['value']);
			}
			$this->_cache->set('system__plugin', $plugins);
		}
		
		foreach ($plugins as $key=>$value)
		{
			CShop::import(CShop::$pluginpath . DIRECTORY_SEPARATOR . $key . '.php',true);
			$event = new $key($value);
			$event->register($this->_eventHandler);
		}
	}
	
	public function run($action,$param = array())
	{
		$this->initialise();
		
		if (is_array($action))
		{
			$this->_action = $action[1];
			$action[0] .= 'Controller';
			CShop::import(Cshop::$corepath . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . $action[0] .'.php',true);
			$this->_controller = new $action[0]();
			$this->_controller->init();
			if (method_exists($this->_controller, 'action'.$action[1]))
			{
				
				call_user_func_array(array($this->_controller,'runAction'),array('action'=>$action[1],$param));
			}
		}
		else 
		{
			$this->_action = $action;
			CShop::import(Cshop::$corepath . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'Controller.php',true);
			$this->_controller = new Controller();
			$this->_controller->init();
			if (method_exists($this->_controller, 'action'.$action))
			{
				call_user_func_array(array($this->_controller,'runAction'),array($action,$param));
			}
		}
		
		
	}
	/**
	 * 
	 * @return SystemConfig
	 */
	public function systemConfig()
	{
		if (!$this->_systemconfig)
		{
			if(!$this->_systemconfig = $this->_cache->get('system__config'))
			{
				$this->_systemconfig = new SystemConfig($this->loadConfig());
				$this->_cache->set('system__config',$this->_systemconfig);
			}
		}
		return $this->_systemconfig;
	}
	
	public function loadConfig($category = self::APPLICATON_CONFIG_CATEGORY)
	{
		
		$sql = $this->_db->query(QueryBuilder::getInstance()->select()->from('config')->where('category="'.$category.'"'));
		$config = array();
		while ($row = $sql->fetch())
		{
			$r = $row;
			unset($r['key']);
			$config[$row['key']] = $r;
		}
		return $config;
	}
	
	public function redirect($url)
	{
		header("location: ".$url);
		$this->end();
	}
	
	public function end()
	{
		$this->raise(Application::EVENT_END);
		exit;
	}
	
	public function getLanguage($category)
	{
		if(!isset($this->_language[$category]))
		{
			$file = CShop::$corepath.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.$this->_config['language'].DIRECTORY_SEPARATOR.$category.'.php';
			if (file_exists($file))
				$this->_language[$category] = include $file;
		}
	
		return $this->_language[$category];
	}
	public function translate($category,$message,$language=null)
	{
		$this->getLanguage($category);
		return isset($this->_language[$category][$message]) ? $this->_language[$category][$message] : $message;
	}
}