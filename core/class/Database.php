<?php
class Database extends PDO
{
	public $last_query;

	private $db_host = "localhost"; // server name
	private $db_user = "root"; // user name
	private $db_pass = ""; // password
	private $db_dbname = "cshop"; // database name
	private $db_charset = "utf8"; // optional character set (i.e. utf8)
	
	function __construct($connect = true, $database = null, $server = null, $username = null, $password = null, $charset = null, $option = null)
	{
		if ($database !== null)
			$this->db_dbname = $database;
		if ($server !== null)
			$this->db_host = $server;
		if ($username !== null)
			$this->db_user = $username;
		if ($password !== null)
			$this->db_pass = $password;
		if ($charset !== null)
			$this->db_charset = $charset;
		
		if (strlen($this->db_host) > 0 && strlen($this->db_user) > 0)
		{
			if ($connect)
				$this->Open($option);
		}
	}

	public function query($statement)
	{
		if ($statement instanceof QueryBuilder)
			$statement = $statement->getQuery();
		$this->last_query = $statement;
		return parent::query($statement);
	}
	
	

	public function prepare($statement, $driver_options = array())
	{
		if ($statement instanceof QueryBuilder)
			$statement = $statement->getQuery();
		$this->last_query = $statement;
		return parent::prepare($statement,$driver_options);
	}

	public function exec($statement)
	{
		if ($statement instanceof QueryBuilder)
			$statement = $statement->getQuery();
		$this->last_query = $statement;
		return parent::exec($statement);
	}

	public function open($options)
	{
		parent::__construct("mysql:host={$this->db_host};dbname={$this->db_dbname};charset:{$this->db_charset}", $this->db_user, $this->db_pass);
		parent::query("SET NAMES ".$this->db_charset);
		parent::setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		parent::setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	}

	public function close()
	{
		unset($this->connection);
	}
}
?>