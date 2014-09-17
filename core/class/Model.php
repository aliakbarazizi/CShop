<?php 
class Model implements Iterator
{
	public function __construct($pdostatement,$pk = 'id')
	{
		$this->pdostatement = $pdostatement;
		$this->pk = $pk;
	}
	/**
	 * 
	 * @var PDOStatement
	 */
	private $pdostatement;
	
	private $pk = false;
	
	private $_current;
	private $_key;
	
	public function count()
	{
		return $this->pdostatement->rowCount();
	}
	
	public function current()
	{
		return $this->_current;
	}

	public function key()
	{
		if ($this->pk && isset($this->_current[$this->pk]))
		{
			return $this->_current[$this->pk];
		}
		return $this->_key;
	}

	public function next()
	{
		return $this->_current = $this->pdostatement->fetch();
	}


	public function rewind()
	{
		$this->_current = $this->pdostatement->fetch();
		//throw new Exception("Can not dublicate");
	}

	/* (non-PHPdoc)
	 * @see Iterator::valid()
	 */
	public function valid()
	{
		return $this->_current ? true : false;
	}

	
}
