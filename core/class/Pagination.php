<?php
class Pagination
{

	public static $pageVar = 'page';

	public $current;

	public $pageLimit;

	public $total;

	public function __construct($total=null, $page = false, $pageLimit = false)
	{
		$this->total = $total;
		
		if ($page === false)
			$page = isset($_GET[self::$pageVar]) ? $_GET[self::$pageVar] : 1;
		$this->current = $page;
		
		if ($pageLimit === false)
			$pageLimit = CShop::app()->systemConfig()->pagelimit;
		$this->pageLimit = $pageLimit;
	}
	
	public function getCount($row,$alias='count')
	{
		$this->total = $row[$alias];
	}

	public function offset()
	{
		return ($this->current - 1) * $this->pageLimit;
	}

	public function totalPages()
	{
		return ceil($this->total / $this->pageLimit);
	}

	public function previousPage()
	{
		return $this->current - 1;
	}

	public function nextPage()
	{
		return $this->current + 1;
	}

	public function hasPrevious()
	{
		return $this->previousPage() >= 1 ? true : false;
	}

	public function hasNext()
	{
		return $this->nextPage() <= $this->totalPages() ? true : false;
	}

	public function getPagination($get = null)
	{
		$self = $_SERVER['PHP_SELF'];
		if ($this->totalPages() <= 1)
			return null;
		if ($get)
			$get = "&" . $get;
		$page = '<span>';
		if ($this->hasPrevious())
			$page .= '<a style="COLOR: #008000; TEXT-DECORATION: none" href="' . $self . '?page=' . $this->previousPage() . $get . '"><span class="item">&lt;</span></a>';
		if($this->current==1)
			$active=' active';
		else
			$active='';
		$page .= '<a style="COLOR: #008000; TEXT-DECORATION: none" href="' . $self . '?page=1' . $get . '"><span class="item'.$active.'">1</span></a>';
		
		if ($this->current > 4)
		{
			$page .= '...';
		}
		
		$i = $this->current - 2;
		for($i = $i > 1 ? $i : 2; $i < $this->current + 3 && $i <= $this->totalPages(); $i ++)
		{
			if($this->current==$i)
				$active=' active';
			else
				$active='';
			$page .= '<a style="COLOR: #008000; TEXT-DECORATION: none" href="' . $self . '?page=' . $i . $get . '"><span class="item'.$active.'">' . $i . '</span></a>';
		}
		if ($this->current + 2 < $this->totalPages())
		{
			if ($this->current + 3 != $this->totalPages())
				$page .= '...';
			$page .= '<a style="COLOR: #008000; TEXT-DECORATION: none" href="' . $self . '?page=' . $this->totalPages() . $get . '"><span class="item">' . $this->totalPages() . '</span></a>';
		}

		if ($this->hasNext())
			$page .= '<a style="COLOR: #008000; TEXT-DECORATION: none" href="' . $self . '?page=' . $this->nextPage() . $get . '"><span class="item">&gt;</span></a>';
		
		$page .= '</span>';
		return $page;
	}
}

?>