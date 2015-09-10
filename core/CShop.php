<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop
 */
class CShop
{
	public static $rootpath;
	public static $corepath;
	public static $pluginpath;
	public static $gatewaypath;
	public static $templatepath;
	public static $classpath;
	public static $librarypath;
	
	public static $baseurl;
	/**
	 * @var Application
	 */
	private static $_application;
	
	private static $_classMap = array();
	private static $_coreclass = array(
		'Application' => '/Application.php',
		
		'BaseController' => '/class/BaseController.php',
		'Cache' => '/class/Cache.php',
		'Database' => '/class/Database.php',
		'EventHandler' => '/class/EventHandler.php',
		'FileCache' => '/class/FileCache.php',
		'GatewayBase' => '/class/GatewayBase.php',
		'Html' => '/class/Html.php',
		'Input' => '/class/Input.php',
		'Item' => '/class/Item.php',
		'Model' => '/class/Model.php',
		'Pagination'=>'/class/Pagination.php',
		'PluginBase' => '/class/PluginBase.php',
		'Plugin' => '/class/Plugin.php',
		'QueryBuilder' => '/class/QueryBuilder.php',
		'SystemConfig' => '/class/SystemConfig.php',
		'User' => '/class/User.php',
		'Validate' => '/class/Validate.php',
		
		'jDateTime' => '/library/jDateTime.php',
		'nusoap_client' => '/library/nusoap.php',
		'PHPMailder' => '/library/PHPMailder.php',
	);
	private static $_includePath = array(
		
	);
	
	private static $_useIncludePath = false;
	
	public static function initialise($config)
	{
		self::$rootpath = dirname(__DIR__);
		self::$corepath = __DIR__;
		self::$pluginpath = self::$rootpath . DIRECTORY_SEPARATOR . 'plugin';
		self::$gatewaypath = self::$rootpath . DIRECTORY_SEPARATOR . 'gateway';
		self::$templatepath = self::$rootpath . DIRECTORY_SEPARATOR . 'template';
		self::$classpath = self::$corepath . DIRECTORY_SEPARATOR . 'class';
		self::$librarypath = self::$corepath . DIRECTORY_SEPARATOR . 'library';
		
		self::import(self::$corepath.'/class/*');
		self::import(self::$corepath.'/library/*');
		self::import(self::$gatewaypath.'/*');
		self::import(self::$pluginpath .'/*');
		
		self::$baseurl = $config['site']['path'];
		
		spl_autoload_register(array(self,'autoload'));
		
	}
	
	/**
	 * 
	 * @return Applicaton
	 */
	public static function create($config)
	{
		self::initialise($config);
		
		return self::$_application = new Application($config);

	}
	/**
	 * 
	 * @return Application
	 */
	public static function app()
	{
		return self::$_application;
	}
	
	public static function siteURL()
	{
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$domainName = $_SERVER['HTTP_HOST'];
		return $protocol.$domainName;
	}
	
	public static function autoload($className)
	{
		if (isset(self::$_coreclass[$className]))
		{
			include CShop::$corepath.self::$_coreclass[$className];
		}
		elseif (isset(self::$_classMap[$className]))
		{
			include self::$_classMap[$className];
		}
		else 
		{
			if (self::$_useIncludePath)
			{
				include_once $className . '.php';
			}
			else
			{
				foreach(self::$_includePath as $path)
				{
					$classFile=$path.DIRECTORY_SEPARATOR.$className.'.php';
					if(is_file($classFile))
					{
						include_once $classFile;
						break;
					}
				}
			}
		}
		
	}
	
	public static function import($className,$force=false)
	{
		if (substr($className,-1) == '*')
		{
			if (self::$_useIncludePath)
				set_include_path(get_include_path() . PATH_SEPARATOR . substr($className, 0,strlen($className)-1));
			else 
				self::$_includePath[] = substr($className, 0,strlen($className)-2);
		}
		else
		{
			if ($force)
				include_once $className;
			else
				self::$_classMap[basename($className,'.php')] = $className;
		}
	}
	
	public static function t($category,$message,$params=array(),$language=null)
	{
		if(self::$_app!==null)
		{
			$message=self::$_application->translate($category,$message,$language);
		}
		if($params===array())
			return $message;
		if(!is_array($params))
			$params=array($params);
		
		return strtr($message,$params);
	}
}