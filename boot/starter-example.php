<?php

$GLOBALS['Language'] = Config::LANGUAGE;
// Carrega a mvc do framework
load_lib_file( 'webdefault/mvc' );
load_lib_file( 'webdefault/languages' );

function load_custom_php( $name )
{
	require_once( CMSConfig::CMS_DIR.'/__custom_php/'.$name.'.php' );
}

class MainApplication extends Application 
{
	private static $_db;

	function __construct( $uri )
	{
		parent::__construct( $uri );
		
		if ( Config::ENV == 'development' )
		{
			define("MYSQL_LOG_FILE", "./log/mysql.log");
			define("MATRIX_QUERY_DEBUG", 1);
			
			if( !file_exists( 'users/'.$this->uri['controller'].'/config.php' ) )
			{
				echo "Error when loading user";
				return;
			}
			
			define( 'USER', $this->uri['controller'] );
			require_once( INCLUDE_PATH.'users/'.$this->uri['controller'].'/config.php' );
		}
		else
		{
			if( !file_exists( 'users/'.$this->uri['controller'].'/config.php' ) )
			{
				echo "Error when loading user";
				return;
			}
			
			define( 'USER', $this->uri['controller'] );
			require_once( 'users/'.USER.'/config.php' );
		}

		switch( UserConfig::DATABASE_TYPE )
		{
			case 'none':
				self::$_db = NULL;
				break;

			case 'mysql':
				load_lib_file( 'webdefault/db/MySQL' );
				self::$_db = new MySQL( 
					UserConfig::DATABASE_NAME, 
					UserConfig::DATABASE_USER, 
					UserConfig::DATABASE_PASS,
					Config::DATABASE_HOST, 
					Config::DATABASE_PORT );
				break;
		}

		define( 'ASSETS', HOME.'static/assets/' );
		define( 'STORAGE_PATH', INCLUDE_PATH.UserConfig::STORAGE_PATH );
		// define( 'SERVICE_PATH', HOME.USER.'/service' );

		$this->context->db = self::$_db;
		//print_r( $this->uri );exit;
		
		if ( Config::ENV == 'development' )
		{
			$this->uri['controller'] = $this->uri['method'] == 'void' ? 'home' : $this->uri['method'];
			$this->uri['method'] = count( $this->uri['vars'] ) > 0 ? array_shift( $this->uri['vars'] ) : 'void';

			$this->context->uri['short_uri'] = substr( $this->uri['short_uri'], strlen('/'.USER.'/') );
			// print_r( $this->uri );exit;
		}
		else
		{
			$this->uri['controller'] = $this->uri['method'] == 'void' ? 'home' : $this->uri['method'];
			$this->uri['method'] = count( $this->uri['vars'] ) > 0 ? array_shift( $this->uri['vars'] ) : 'void';

			$this->context->uri['short_uri'] = substr( $this->uri['short_uri'], strlen('/'.USER.'/') );
		}
		// print_r( $this->uri );exit;
		
		if( $this->uri['controller'] == 'wsc' )
		{
			$this->uri['controller'] = $this->uri['method'] == 'void' ? 'home' : $this->uri['method'];
			$this->uri['method'] = count( $this->uri['vars'] ) > 0 ? array_shift( $this->uri['vars'] ) : 'void';
			$this->context->appDir = 'wsc';

			$this->defines();
		}
		else
		{
			$this->context->appDir = 'app';
			$this->defines();
			
			require_once( CONTROLLER_PATH.'CMS.php' );
		}
		
		$this->loadController( $this->uri['controller'] );
	}

	private function defines()
	{
		define( 'CONTROLLER_PATH', INCLUDE_PATH.$this->context->appDir.'/controller/' );
		define( 'MODEL_PATH', INCLUDE_PATH.$this->context->appDir.'/model/' );
		define( 'VIEW_PATH', INCLUDE_PATH.$this->context->appDir.'/view/' );
		
		define( 'CMS_ASSETS', HOME.'static/cms-assets/' );
		define( 'CMS_CUSTOM_ASSETS', HOME.'static/cms-custom-assets/' );
		define( 'CMS_HOME', HOME.'admin/' );
		define( 'STORAGE', HOME.USER.'/storage/' );
		define( 'SERVICE', HOME.USER.'/service/' );
	}
}

?>