<?php 
class BaseController
{
	private $_templatepath;
	public $layout = '';
	
	public $pageTitle;
	
	/**
	 * 
	 * @var Database
	 */
	protected $db;
	
	/**
	 * 
	 * @var User
	 */
	protected $user;
	
	protected $userType = 'user';
	
	protected $actions = array();
	
	public function init()
	{
		$this->_templatepath = Cshop::$templatepath;
		$this->db = Cshop::app()->getDb();
		$this->user = new User();
	}
	
	public function beforeAction($action)
	{
		if ($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if ($out = CShop::app()->getCache()->get(get_class($this).$action.serialize($_GET)))
			{
				echo $out;
				CShop::app()->end();
			}
			ob_start();
			ob_implicit_flush(false);
		}
		return true;
	}
	
	public function afterAction($action)
	{
		if ($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			$out = ob_get_clean();
			CShop::app()->getCache()->set(get_class($this).$action.serialize($_GET), $out);
			echo $out;
		}
		
	}
	
	public function registerAction($action,$callback)
	{
		$this->actions[$action] = $callback;
	}
	
	public function runAction($action,$param)
	{
		CShop::app()->raise(Application::EVENT_BEFORE_ACTION, array($this,&$action,&$param));
		if ($this->beforeAction($action))
		{
			call_user_func_array(array($this,'action'.$action),$param);
			$this->afterAction($action);
			CShop::app()->raise(Application::EVENT_AFTER_ACTION, $this);
		}
	}
	
	public function render($view,$data=array())
	{
		CShop::app()->raise(Application::EVENT_BEFORE_RENDER, array($this,&$view,&$data));
		$content = $this->renderInternal($view.'.php', $data,true);
		if($this->layout!==false)
		{
			$data['content'] = $content;
			$content = $this->renderInternal($this->layout.'.php', $data,true);
		}
		CShop::app()->raise(Application::EVENT_AFTER_RENDER, array($this,&$content,&$view,&$data));
		echo $content;
	}
	
	public function renderWithContent($content,$data=array())
	{
		CShop::app()->raise(Application::EVENT_BEFORE_RENDER, array($this,null,&$data));
		if($this->layout!==false)
		{
			$data['content'] = $content;
			$content = $this->renderInternal($this->layout.'.php', $data,true);
		}
		CShop::app()->raise(Application::EVENT_AFTER_RENDER, array($this,&$content,null,&$data));
		echo $content;
	}
	
	public function beginRender()
	{
		ob_start();
		ob_implicit_flush(false);
	}
	
	public function endRender($view,$data=array())
	{
		$data['content'] = ob_get_clean();
		$this->renderInternal($view.'.php', $data);
	}
	
	public function renderInternal($__view__,$__data__,$__return__=false)
	{
		$__view__ = $this->_templatepath . DIRECTORY_SEPARATOR . $__view__;
		if (!file_exists($__view__))
			return ;
		// we use special variable names here to avoid conflict when extracting data
		if(is_array($__data__))
			extract($__data__,EXTR_PREFIX_SAME,'data');
		else
			$data=$__data__;
		if($__return__)
		{
			ob_start();
			ob_implicit_flush(false);
			require($__view__);
			return ob_get_clean();
		}
	
		require($__view__);
		return;
	}
}