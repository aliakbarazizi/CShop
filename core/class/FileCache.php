<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop.cache
 */
class FileCache extends Cache
{
	public $cachePath;
	
	public $cacheFileSuffix='.bin';
	/**
	 * @var integer the level of sub-directories to store cache files. Defaults to 0,
	 * meaning no sub-directories. If the system has huge number of cache files (e.g. 10K+),
	 * you may want to set this value to be 1 or 2 so that the file system is not over burdened.
	 * The value of this property should not exceed 16 (less than 3 is recommended).
	 */
	public $directoryLevel=0;
	
	public $embedExpiry=false;

	private $_gcProbability=100;
	private $_gced=false;

	public function __construct($cachePath = null)
	{
		$this->init($cachePath);
	}
	
	public function init($cachePath = null)
	{
		parent::init();
		if($cachePath)
			$this->cachePath = $cachePath;
		if($this->cachePath===null)
			$this->cachePath=CShop::$corepath.DIRECTORY_SEPARATOR.'cache';
		if(!is_dir($this->cachePath))
			mkdir($this->cachePath,0777,true);
	}

	public function getGCProbability()
	{
		return $this->_gcProbability;
	}

	public function setGCProbability($value)
	{
		$value=(int)$value;
		if($value<0)
			$value=0;
		if($value>1000000)
			$value=1000000;
		$this->_gcProbability=$value;
	}

	protected function flushValues()
	{
		$this->gc(false);
		return true;
	}

	protected function getValue($key)
	{
		$cacheFile=$this->getCacheFile($key);
		if(($time=$this->filemtime($cacheFile))>time())
			return @file_get_contents($cacheFile,false,null,$this->embedExpiry ? 10 : -1);
		elseif($time>0)
			@unlink($cacheFile);
		return false;
	}

	protected function setValue($key,$value,$expire)
	{
		if(!$this->_gced && mt_rand(0,1000000)<$this->_gcProbability)
		{
			$this->gc();
			$this->_gced=true;
		}

		if($expire<=0)
			$expire=31536000; // 1 year
		$expire+=time();
		$cacheFile=$this->getCacheFile($key);
		if($this->directoryLevel>0)
			@mkdir(dirname($cacheFile),0777,true);
		if(@file_put_contents($cacheFile,$this->embedExpiry ? $expire.$value : $value,LOCK_EX)!==false)
		{
			@chmod($cacheFile,0777);
			return $this->embedExpiry ? true : @touch($cacheFile,$expire);
		}
		else
			return false;
	}

	protected function addValue($key,$value,$expire)
	{
		$cacheFile=$this->getCacheFile($key);
		if($this->filemtime($cacheFile)>time())
			return false;
		return $this->setValue($key,$value,$expire);
	}

	protected function deleteValue($key)
	{
		$cacheFile=$this->getCacheFile($key);
		return @unlink($cacheFile);
	}

	protected function getCacheFile($key)
	{
		if($this->directoryLevel>0)
		{
			$base=$this->cachePath;
			for($i=0;$i<$this->directoryLevel;++$i)
			{
				if(($prefix=substr($key,$i+$i,2))!==false)
					$base.=DIRECTORY_SEPARATOR.$prefix;
			}
			return $base.DIRECTORY_SEPARATOR.$key.$this->cacheFileSuffix;
		}
		else
			return $this->cachePath.DIRECTORY_SEPARATOR.$key.$this->cacheFileSuffix;
	}

	public function gc($expiredOnly=true,$path=null)
	{
		if($path===null)
			$path=$this->cachePath;
		foreach (glob($path.DIRECTORY_SEPARATOR.'*') as $fullPath)
		{
			$file=basename($fullPath);
			if($file[0]==='.')
				continue;
			if(is_dir($fullPath))
				$this->gc($expiredOnly,$fullPath);
			elseif($expiredOnly && $this->filemtime($fullPath)<time() || !$expiredOnly)
				@unlink($fullPath);
		}
	}

	private function filemtime($path)
	{
		if($this->embedExpiry)
			return (int)@file_get_contents($path,false,null,0,10);
		else
			return @filemtime($path);
	}
}
