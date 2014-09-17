<?php 
class QueryBuilder
{
	private $_query;
	private $_prefix;

	public static function getInstance($prefix=null)
	{
		if ($prefix == null)
		{
			$prefix = CShop::app()->getConfig('database');
			$prefix = $prefix['prefix'];	
		}
		return new self($prefix);
	}
	
	public function __construct($prefix='')
	{
		$this->_prefix = $prefix;
	}
	
	public function getQuery()
	{
		return $this->_query;
	}
	
	public function count($alias='count')
	{
		$this->_query .= 'SELECT COUNT(*) AS '.$alias.' ';

		return $this;
	}
	
	public function select($fields='*')
	{
		$this->_query .= 'SELECT ';
		
		if (is_array($fields))
		{
			$this->_query .= implode(',', $fields);
		}
		else 
			$this->_query .= $fields;
		$this->_query .= ' ';
		return $this;
	}
	
	public function selectDistinct($fields)
	{
		$this->_query .= 'SELECT Distinct ';
	
		if (is_array($fields))
		{
			$this->_query .= implode(',', $fields);
		}
		else
			$this->_query .= $fields;
		$this->_query .= ' ';
		return $this;
	}
	
	public function from($tables)
	{
		$this->_query .= 'FROM ';
		
		if (is_array($tables))
		{
			foreach ($tables as $key=>$value)
			{
				$tables[$key] = $this->_prefix . $value  . ' AS '.$tables;
			}
			$this->_query .= implode(',', $tables);
		}
		else
			$this->_query .= $this->_prefix . $tables . ' AS '.$tables;

		$this->_query .= ' ';
		return $this;
	}
	
	public function join($tables)
	{
		$this->_query .= 'JOIN ';
	
		if (is_array($tables))
		{
			foreach ($tables as $key=>$value)
			{
				$tables[$key] = $this->_prefix . $value  . ' AS '.$tables;
			}
			$this->_query .= implode(',', $tables);
		}
		else
			$this->_query .= $this->_prefix . $tables  . ' AS '.$tables;
		$this->_query .= ' ';
		return $this;
	}
	
	public function leftJoin($tables)
	{
		$this->_query .= 'LEFT JOIN ';
	
		if (is_array($tables))
		{
			foreach ($tables as $key=>$value)
			{
				$tables[$key] = $this->_prefix . $value  . ' AS '.$tables;
			}
			$this->_query .= implode(',', $tables);
		}
		else
			$this->_query .= $this->_prefix . $tables  . ' AS '.$tables;
		$this->_query .= ' ';
		return $this;
	}
	
	public function rightJoin($tables)
	{
		$this->_query .= 'RIGHT JOIN ';
	
		if (is_array($tables))
		{
			foreach ($tables as $key=>$value)
			{
				$tables[$key] = $this->_prefix . $value  . ' AS '.$tables ;
			}
			$this->_query .= implode(',', $tables);
		}
		else
			$this->_query .= $this->_prefix . $tables  . ' AS '.$tables;
		$this->_query .= ' ';
		return $this;
	}
	
	public function outerJoin($tables)
	{
		$this->_query .= 'FULL OUTER JOIN ';
	
		if (is_array($tables))
		{
			foreach ($tables as $key=>$value)
			{
				$tables[$key] = $this->_prefix . $value . ' AS '.$tables;
			}
			$this->_query .= implode(',', $tables);
		}
		else
			$this->_query .= $this->_prefix . $tables . ' AS '.$tables;
		$this->_query .= ' ';
		return $this;
	}
	
	public function on($where)
	{
		$this->_query .= 'ON '.$where;
		$this->_query .= ' ';
		return $this;
	}
	
	public function where($where)
	{
		$this->_query .= 'WHERE '.$where;
		$this->_query .= ' ';
		return $this;
	}
	
	public function andWith($where)
	{
		$this->_query .= 'AND '.$where;
		$this->_query .= ' ';
		return $this;
	}
	
	public function orWith($where)
	{
		$this->_query .= 'OR '.$where;
		$this->_query .= ' ';
		return $this;
	}
	
	public function group($fields)
	{
		$this->_query .= 'GROUP BY ';
	
		if (is_array($fields))
		{
			$this->_query .= implode(',', $fields);
		}
		else
			$this->_query .= $fields;
		$this->_query .= ' ';
		return $this;
	}

	public function having($having)
	{
		$this->_query .= 'HAVING '.$having;
		$this->_query .= ' ';
		return $this;
	}
	
	public function order($fields)
	{
		$this->_query .= 'ORDER BY ';
	
		if (is_array($fields))
		{
			$this->_query .= implode(',', $fields);
		}
		else
			$this->_query .= $fields;
		$this->_query .= ' ';
		return $this;
	}
	
	public function limit($limit)
	{
		if ($limit instanceof Pagination)
		{
			$this->_query .= 'LIMIT '.$limit->offset().','.$limit->pageLimit.' ';
			return $this;
		}
		$this->_query .= 'LIMIT '.$limit;
		$this->_query .= ' ';
		return $this;
	}
	
	public function offset($offset)
	{
		$this->_query .= 'OFFSET '.$offset;
		$this->_query .= ' ';
		return $this;
	}
	
	public function union($sql)
	{
		$this->_query .= 'UNION '.$sql;
		$this->_query .= ' ';
		return $this;
	}
	
	public function delete($from)
	{
		$this->_query .= 'DELETE '.$from.' FROM '.$this->_prefix . $from . ' AS '.$from.' ';
		return $this;
	}
	
	public function insert($table)
	{
		$this->_query .= 'INSERT INTO '.$this->_prefix . $table.' ';
	
		return $this;
	}
	
	public function into($fields,$genrerateprepare=false,$named = true,$single=false)
	{
		if (!is_array($fields))
			$fields = $single == true ? array($fields) : explode(',', $fields);
		$this->_query .= '('.implode(',', $fields).') ';
	
		if ($genrerateprepare)
		{
			$this->_query .= 'VALUES (';
			if ($named)
			{
				foreach ($fields as $field)
					$this->_query .= ':'.trim($field,'` ').',';
			}
			else 
			{
				foreach ($fields as $field)
					$this->_query .= '?,';
			}
			$this->_query = rtrim($this->_query,',') . ')';
		}
		return $this;
	}
	
	public function values($values,$qoute="'")
	{
		if (!is_array($values))
			$this->_query .= 'VALUES ('.$values.') ';
		else
			$this->_query .= 'VALUES ('.$qoute.implode($qoute.','.$qoute, $values).$qoute.') ';
		return $this;
	}
	
	public function update($from)
	{
		$this->_query .= 'UPDATE '.$this->_prefix . $from . ' AS '.$from.' ';
		return $this;
	}
	
	public function set($fields)
	{
		$this->_query .= 'SET ';

		if (is_array($fields))
		{
			$this->_query .= implode(',', $fields);
		}
		else
			$this->_query .= $fields;
		$this->_query .= ' ';
		return $this;
	}
}