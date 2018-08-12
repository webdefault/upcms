<?php

/**
 * @package controller
 * @classe home
 *
 */

load_lib_file( 'webdefault/db/mysql/matrixquery' );
load_lib_file( 'webdefault/util/sessionlogin' );
load_lib_file( 'webdefault/util/options' );
load_lib_file( 'cms/procedures' );
load_lib_file( 'cms/jsonconfig' );

class CMS extends BaseController
{
	protected $user, $front, $config, $viewVars, $mapName;
	private static $cms_instance;

	function __construct( $context )
	{
		self::$cms_instance = $this;
		parent::__construct( $context );
		
		header( 'Access-Control-Allow-Origin: '.CMSConfig::ALLOW_DOMAIN );
		header( 'Access-Control-Allow-Headers: '.CMSConfig::ALLOW_DOMAIN );
		header( 'Access-Control-Allow-Credentials: true' );
		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS' );
		
		if( @$_SERVER['REQUEST_METHOD'] === 'OPTIONS' )
		{
			header("HTTP/1.1 202 Accepted");
			exit;
		}

		SessionLogin::setup(
			$context->db,
			CMSConfig::CONNECTION_SESSION_VAR,
			CMSConfig::USERS_TABLE_NAME );
		SessionLogin::setColumns( CMSConfig::USERS_USERNAME_COLUMN, CMSConfig::USERS_PASSWORD_COLUMN );

		if( !isset($_SESSION['front']) ) $_SESSION['front'] = array();
		
		Options::setup( $context->db, Config::SYSTEM_TABLE_PREFIX.'options' );
		
		$this->user = SessionLogin::getLoggedUser();
		// $this->front = Options::get( 'cms', 'front' );

		//define( 'CMS_ASSETS', HOME.'static/cms-assets/' );
		//define( 'CMS_CUSTOM_ASSETS', HOME.'static/cms-custom-assets/' );
		//define( 'CMS_HOME', HOME );
		//define( 'STORAGE', HOME.'storage/' );
		//define( 'SERVICE', HOME.'service/' );

		//$this->config = json_decode( file_get_contents( INCLUDE_PATH.'boot/admin/config.json' ), true );
		$this->config = array();
		
		require_once( Config::FULL_APP_PATH.CMSConfig::CMS_DIR.'/init.php' );
		// $this->config['init'] = json_decode( file_get_contents(  ) );
		$this->config['map'] = new JSONConfig( 'map' );
		$this->config['pages'] = new JSONConfig( 'page' );
		$this->config['layout'] = new JSONConfig( 'layout' );
		$this->config['procedures'] = new JSONConfig( 'procedures', 2 );

		//foreach( $this->config['init']->{"load-lib-files"} AS $file )
		//	load_lib_file( $file );
		
		if( $this->user == NULL ) $this->noUserLogged( $context->uri['request_uri'] );

		$this->loadModel( 'Util' );
		global $CMSUtilModel;
		$CMSUtilModel = $this->model->util;
			
		//$this->loadMenu();
		$this->viewVars = array( 'url' => $context->uri['short_uri'], 'user' => $this->user );

		// $this->loadPlugins();
		
		define( 'USER_LIBRARY_PATH', STORAGE_PATH.'/library/user'.$this->user['id'].'/' );
		
		if( function_exists( '__custom_init') )
		{
			__custom_init( $context );
		}
	}

	private function loadPlugins()
	{
		$PATH = INCLUDE_PATH.'/plugins/';
		if( $handle = opendir( $PATH ) )
		{
			while( false !== ( $file = readdir( $handle ) ) )
			{
				if( substr( $file, 0, 1 ) != '.' )
					require_once $PATH.$file.( is_dir( $PATH.$file ) ? '/index.php' : '' );
			}
		}
	}

	public static function parseSlashGet( $vars, $defaults = array() )
	{
		$options = array();

		while( count( $vars ) > 0 )
		{
			$name = array_shift( $vars );
			$value = array_shift( $vars );

			$options[$name] = explode( ',', $value );
			if( count( $options[$name] ) == 1 ) $options[$name] = urldecode( $options[$name][0] );
		}

		foreach( $defaults AS $key => $item )
		{
			if( !isset( $options[$key] ) )
				$options[$key] = $item;
		}

		return $options;
	}
	
	public static function createSlashGet( $vars )
	{
		$str = $glue = '';
		foreach( $vars AS $key => $value )
		{
			if( is_array( $value ) ) $value = implode(',', $value );
			
			$str .= $glue.$key.'/'.$value;
			$glue = '/';
		}
		
		return $str;
	}
	
	public static function parseMapId( $ids, $default )
	{
		$mapId = array();
		
		if( @$ids != NULL )
		{
			foreach( $ids AS $id )
			{
				$t = explode( '=', $id );
				
				if( count( $t ) == 1 )
					$mapId[$default] = $t[0];
				else
					$mapId[$t[0]] = $t[1];
			}
		}

		return $mapId;
	}
	
	public static function createMapId( $obj, $default )
	{
		$result = '';
		$glue = '';
		foreach( $obj AS $key => $value )
		{
			if( $key == $default )
			{
				$result .= $glue.$value;
			}
			else
			{
				$result .= $glue.$key.'='.$value;
			}
			
			$glue = '&';
		}
		
		return $result;
	}

	protected function noUserLogged( $requestUri )
	{
		$_SESSION['cms-login-redirect'] = $requestUri;

		header_redirect( '/'.Config::PATH_NAME.'session' );
		exit();
	}

	function index( $vars )
	{

	}

	protected function loadView( $view, $vars = '', $header = false )
	{
		parent::loadView( $this->front.'/'.$view, $vars, $header );
	}

	
	protected function parseTable( $target, $onlyQuickedit = false)
	{
		$table = array();

		foreach( $target AS $key => $attr )
		{
			if( (string)$key == 'field' )
			{
				$table['field'] = array();

				foreach( $attr AS $fkey => $field )
				{
					// Use only fields with quickedit
					if( @$field['quickedit'] != false || !$onlyQuickedit || @$field['type'] == 'where' )
					{
						// If it has options should be parsed
						if( @$field['option'] != NULL )
						{
							$tempField = array();

							// Make a copy of original $field because we don't 
							// want to change the config file
							foreach( $field AS $key => $fattr )
							{
								$tempField[$key] = $fattr;
							}

							$tempField['option'] = $this->model->util->parseOptions( $field['option'] );

							$table['field'][$fkey] = $tempField;
						}
						else
						{
							$table['field'][$fkey] = $field;
						}
					}
				}
			}
			else
			{
				$table[$key] = $attr;
			}
		}

		return $table;
	}

	public static function mapByName( $name )
	{
		$target = self::$cms_instance;
		return $target->config['map']->get($name);
	}

	public static function pageByName( $name )
	{
		$target = self::$cms_instance;
		return $target->config['pages']->get($name);
	}

	public static function procedures( $name )
	{
		$target = self::$cms_instance;
		return $target->config['procedures']->get($name);
	}

	public static function user()
	{
		$target = self::$cms_instance;
		return $target->user;
	}

	private static $globalValues = NULL;

	public static function globalValues()
	{
		$target = self::$cms_instance;
		
		if( self::$globalValues == NULL )
		{
			self::$globalValues = array();
			self::$globalValues['USER'] = $target->user;
			self::$globalValues['DATE'] = array( 'TIMESTAMP' => date('Y-m-d H:i:s'), 'TIME' => time() );
			self::$globalValues['CONTEXT'] = array( 
				'MAP_NAME' => $target->mapName, 
				'MAP_TABLE' => $target->mapName ? self::$cms_instance->config['map']->get($target->mapName)->table : NULL );
			self::$globalValues['POST'] = $_POST;
			self::$globalValues['CONFIG'] = array( 'FULL_APP_PATH' => Config::FULL_APP_PATH );
		}
		
		return self::$globalValues;
	}
	
	public static function globalValue( $name, $sub = NULL )
	{
		if( $name == 'OPTIONS' )
			return Options::get( 'system', $sub );
		else
		{
			self::globalValues();
			$temp = @self::$globalValues[$name];
			
			if( $sub !== NULL && $temp !== NULL )
				return @$temp[$sub];
			else
				return $temp;
				
		}
	}

	public static function addGlobalValue( $name, $list )
	{
		self::globalValues();
		
		self::$globalValues[$name] = $list;
	}
	
	public static function exitWithMessage( $type, $message, $debug = NULL )
	{
		if( Config::ENV == 'development' )
		{
			$target = self::$cms_instance;
			$target->loadView( 'Error', array( 'type' => $type, 'text' => $message, 'debug' => $debug ) );
			exit();
		}
		else
		{
			$target = self::$cms_instance;
			$target->loadView( 'Error', array( 'type' => $type, 'text' => 'Erro interno' ) );
			exit();
		}
	}

	/*protected function throwFatalError( $err )
	{
		$this->loadView( 'FatalError', array( 'error' => $err ) );
		exit();
	}*/
	
}

?>