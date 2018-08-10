<?php

load_lib_file( 'webdefault/functions' );

class Base
{
	protected $model, $context;
	
	function __construct( $context )
	{
		$this->context = $context;
		$this->model = new stdClass();
	}
	
	function init()
	{
		
	}
	
	protected function loadModel( $modelName )
	{
		if( !isset( $this->context->model->{lcfirst( $modelName )} ) )
		{
			$file = Config::FULL_APP_PATH.$this->context->appDir.'/model/'.$modelName.'Model.php';
			
			if( file_exists( $file ) )
				require_once( $file );
			else
			{
				$file = INCLUDE_PATH.'base/model/'.$modelName.'Model.php';
				
				if( file_exists( $file ) )
					require_once( $file );
				else
				{
					debug_print_backtrace();
					throw new Exception("Model ".$modelName." not found", E_USER_ERROR);
					exit();
				}
			}
			
			$class = $modelName.'Model';
			
			$this->context->model->{lcfirst( $modelName )} = new $class( $this->context );
			$this->model->{lcfirst( $modelName )} = $this->context->model->{lcfirst( $modelName )};
			
			$this->model->{lcfirst( $modelName )}->init();
		}
		else if( !isset( $this->model->{lcfirst( $modelName )} ) )
		{
			$this->model->{lcfirst( $modelName )} = $this->context->model->{lcfirst( $modelName )};
		}
	}
}

class BaseModel extends Base
{
	protected $db;

	function __construct( $context )
	{
		parent:: __construct( $context );
		
		$this->db = $context->db;
	}
}

class BaseController extends Base
{
	protected $header, $footer, $content;
	
	function __construct( $context )
	{
		parent:: __construct( $context );
		
		$this->header = array();
		$this->footer = array();
		$this->content = array();
	}

	protected function loadView( $view, $vars = '', $header = false )
	{
		if( $header )
		{
			$vars['keywords'] = isset($vars['keywords']) ? $vars['keywords'] : Config::DEFAULT_KEYWORDS;
			$vars['meta_description'] = ( isset($vars['meta_description']) )
				? $vars['meta_description'] : Config::DEFAULT_META_DESCRIPTION;
			$vars['name_author'] = Config::DEFAULT_NAME_AUTHOR;
			$vars['page_title'] = Config::SITE_NAME;
			$vars['page_title'] .= (isset( $vars['pageTitle'] )) ? $vars['pageTitle'] : ' ';
		}
		
		if( is_array( $vars ) && count( $vars ) > 0 )
			extract( $vars, EXTR_PREFIX_SAME, 'wddx' );
		
		$file = Config::FULL_APP_PATH.$this->context->appDir.'/view/'.$view.'View.php';
		
		if( file_exists( $file ) )
			require( $file );
		else
		{
			$file = INCLUDE_PATH.'base/view/'.$view.'View.php';
			
			if( file_exists( $file ) )
				require_once( $file );
			else
			{
				debug_print_backtrace();
				trigger_error("View ".$file." not found", E_USER_ERROR);
				exit();
			}
		}
	}
}

class Application 
{
	protected $context;
	protected $uri;
	
	protected static $instance; 

	function __construct( $uri )
	{
		$this->uri = $uri;
		
		$this->context = new stdClass();
		$this->context->appDir = Config::DEFAULT_APPLICATION_DIR;
		$this->context->model = new stdClass();
		$this->context->uri = $uri;

		//define( 'ASSETS', HOME.'static/assets/' );
		//define( 'STORAGE_PATH', INCLUDE_PATH.Config::STORAGE_PATH );

		if( !isset( $_SESSION['ready'] ) )
		{
			session_start();
			$_SESSION['ready'] = TRUE; 
		}
		
		self::$instance = $this;
	}

	protected function _loadController( $className )
	{
		$name = parse_hyphen_name( $className );

		foreach( $this->uri['vars'] AS &$item )
			$item = addslashes( $item );
		
		$file = Config::FULL_APP_PATH.$this->context->appDir.'/controller/'.$name.'.php';
		
		if( file_exists( $file ) )
			require_once( $file );
		else
		{
			$file = INCLUDE_PATH.'base/controller/'.$name.'.php';
			if( file_exists( $file ) )
			require_once( $file );
			else
			{
				$name = 'Error404';
				$path = Config::FULL_APP_PATH.$this->context->appDir.'/controller/Error404.php';
				if( file_exists( $path ) )
				{
					require_once( $path );
				}
				else
				{
					$path = INCLUDE_PATH.'base/controller/Error404.php';
					if( file_exists( $path ) )
						require_once( $path );
					else
					{
						header("HTTP/1.0 404 Not Found");
						echo 'Not Found - 404';
						exit();
					}
				}
			}
		}
		
		return $name;
	}
	
	public static function loadController( $className )
	{
		return self::$instance->_loadController( $className );
	}
	
	protected function runController( $className )
	{
		$name = $this->loadController( $className );
		
		$controller = new $name( $this->context );
		
		$method = lcfirst( parse_hyphen_name( $this->uri['method'] ) );

		if( method_exists( $controller, $method ) )
			$controller->{$method}( $this->uri['vars'] );
		else
		{
			array_unshift( $this->uri['vars'], $this->uri['method'] );
			$controller->index( $this->uri['vars'] );
		}
	}
}
