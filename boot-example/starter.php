<?php

$GLOBALS['Language'] = Config::LANGUAGE;
// Carrega a mvc do framework
load_lib_file( 'webdefault/mvc' );
load_lib_file( 'cms/i18n' );

function load_custom_php( $name )
{
	require_once( Config::FULL_APP_PATH.CMSConfig::CMS_DIR.'/__custom_php/'.$name.'.php' );
}

class MainApplication extends Application
{
	private static $_db;

	function __construct( $uri )
	{
		parent::__construct( $uri );

		if( Config::ENV == 'development' )
		{
			touch( INCLUDE_PATH."../log/mysql.log" );
			define("MYSQL_LOG_FILE", INCLUDE_PATH."../log/mysql.log");
			define("MATRIX_QUERY_DEBUG", 1);
			
			ini_set('error_log', INCLUDE_PATH.'../log/errors.log');
		}

		switch( Config::DATABASE_TYPE )
		{
			case 'none':
				self::$_db = NULL;
				break;

			case 'mysql':
				load_lib_file( 'webdefault/db/MySQL' );
				if( @Config::DATABASE_SSL == true )
				{
					$path = INCLUDE_PATH.'boot/certificates/';
					self::$_db = new MySQL(
						Config::DATABASE_NAME, Config::DATABASE_USER, Config::DATABASE_PASS,
						Config::DATABASE_HOST, Config::DATABASE_PORT,
						Config::DATABASE_SSL,
						$path.Config::DATABASE_SSL_CA, $path.Config::DATABASE_SSL_KEY, $path.Config::DATABASE_SSL_CERT );
				}
				else
				{
					self::$_db = new MySQL( Config::DATABASE_NAME, Config::DATABASE_USER, Config::DATABASE_PASS,
										Config::DATABASE_HOST, Config::DATABASE_PORT );
				}
				break;
		}

		$this->context->db = self::$_db;

		$this->defines();

		$this->loadController( 'CMS' );

		$this->runController( $this->uri['controller'] );

		$this->context->db->close();
	}

	private function defines()
	{
		define( 'CONTROLLER_PATH', INCLUDE_PATH.'../'.$this->context->appDir.'/controller/' );
		define( 'MODEL_PATH', INCLUDE_PATH.'../'.$this->context->appDir.'/model/' );
		define( 'VIEW_PATH', INCLUDE_PATH.'../'.$this->context->appDir.'/view/' );

		define( 'ASSETS', HOME.'static/assets/' );
		define( 'STORAGE_PATH', Config::FULL_APP_PATH.Config::STORAGE_PATH );

		define( 'CMS_ASSETS', HOME.'static/cms-assets/' );
		define( 'CMS_CUSTOM_ASSETS', HOME.'static/cms-custom-assets/' );
		define( 'CMS_HOME', HOME );
		define( 'STORAGE', HOME.'/storage/' );
		define( 'SERVICE', HOME.'/service/' );
	}
}

?>