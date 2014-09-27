<?php
/**
 * @author Ali Akbar Azizi <aliakbar.azizi20@gmail.com>
 * @link http://cshop.irprog.com
 * @copyright 2014 CShop
 * @license http://cshop.irprog.com/licence.txt
 * @package cshop.cache
 */
abstract class Cache
{

	public $keyPrefix = 'CShopcache';

	public $hashKey = true;

	public function init()
	{
	}

	protected function generateUniqueKey($key)
	{
		return $this->hashKey ? md5($this->keyPrefix . $key) : $this->keyPrefix . $key;
	}

	public function get($id)
	{
		$value = $this->getValue($this->generateUniqueKey($id));
		if ($value === false)
			return $value;
		$value = unserialize($value);
		if (is_array($value) && ($value['hash'] == sha1($value['data'])))
		{
			return unserialize($value['data']);
		}
		else
			return false;
	}

	public function set($id, $value, $expire = 0)
	{
		$value = serialize($value);
		$value = serialize(array(
			'data'=>$value,
			'hash'=>sha1($value)
		));
		return $this->setValue($this->generateUniqueKey($id), $value, $expire);
	}

	public function add($id, $value, $expire = 0, $dependency = null)
	{
		$value = serialize($value);
		$value = serialize(array(
			'data'=>$value,
			'hash'=>sha1($value)
		));
		return $this->addValue($this->generateUniqueKey($id), $value, $expire);
	}

	public function delete($id)
	{
		return $this->deleteValue($this->generateUniqueKey($id));
	}

	public function flush()
	{
		return $this->flushValues();
	}

	protected abstract function getValue($key);

	protected abstract function setValue($key, $value, $expire);
	
	protected abstract function addValue($key, $value, $expire);
	
	protected abstract function deleteValue($key);
	
	protected abstract function flushValues();
}