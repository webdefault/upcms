<?php

// Set gzip compression
if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
{ 
	ob_end_clean(); ob_start("ob_gzhandler"); 
}else{ 
	ob_start(); 
}

// Ainda existem servidores com essa coisa habilitada :\ //
if ( get_magic_quotes_gpc() )
{
	function stripslashes_recursive( $var ) { return ( is_array( $var ) ? array_map( 'stripslashes_recursive', $var ) : stripslashes( $var ) ); }
	
	$_GET = stripslashes_recursive( $_GET );
	$_POST = stripslashes_recursive( $_POST );
	$_COOKIE = stripslashes_recursive( $_COOKIE );
}

set_include_path( substr( __FILE__, 0, strlen( __FILE__ ) - strlen( 'index.php' ) ) );

define( 'INCLUDE_PATH', get_include_path() );

// Configurações Gerais
require_once( 'boot/config.php' );

// Define o timezone local
date_default_timezone_set( defined( 'Config::TIMEZONE' ) ? Config::TIMEZONE : 'America/Halifax' );

if (php_sapi_name() == "cli")
{
	$temp = getopt('', array('uri:') );
	$request_uri = $temp['uri'];
	
	define( 'BASE_PATH_SECURE', Config::SITE_HTTPS );
	define( 'BASE_PATH', Config::SITE_HTTP );
	define( 'PATH_NAME', Config::PATH_NAME );
	// print_r( $temp );exit;
}
else
{
	$request_uri = $_SERVER['REQUEST_URI'] == '' ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
	
	if( Config::SITE_HTTPS != '' )
	{
		define( 'BASE_PATH', Config::SITE_HTTPS );
		define( 'PATH_NAME', Config::PATH_NAMES );
		
		if( 'https://'.$_SERVER['HTTP_HOST'].'/' != BASE_PATH || $_SERVER['SERVER_PORT'] != 443 )
		{
			header( 'Location:'.BASE_PATH.substr( $request_uri, 1 ) );
			exit();
		}
	}
	else if( $_SERVER['SERVER_PORT'] == 80 )
	{
		define( 'BASE_PATH', Config::SITE_HTTP );
		define( 'PATH_NAME', Config::PATH_NAME );
		
		if( 'http://'.$_SERVER['HTTP_HOST'].'/' != BASE_PATH )
		{
			header( 'Location:'.BASE_PATH.substr( $request_uri, 1 ) );
			exit();
		}
	}
	else
	{
		header( 'Location:'.Config::SITE_HTTP.substr( $request_uri, 1 ) );
		exit();
	}
}

// Biblioteca de funções + Autoloader ( Invocador de classes )
//require_once( 'internalextender.php' );

// Define os niveis de erro
switch (Config::ENV):
	case 'production':  error_reporting(0); break;
	case 'development': error_reporting(E_ALL | E_STRICT | E_DEPRECATED); break;
endswitch;

/* globl functions and vars */

//
$mainApp = NULL;

function load_lib_file( $name )
{
	if( file_exists( INCLUDE_PATH.'lib/'.$name.'.php' ) )
		require_once( INCLUDE_PATH.'lib/'.$name.'.php' );
	else if( file_exists( INCLUDE_PATH.'../lib/'.$name.'.php' ) )
		require_once( INCLUDE_PATH.'../lib/'.$name.'.php' );
	else if( Config::ENV == 'development' )
	{
		echo "Error: ".INCLUDE_PATH."lib/$name.php not found.\n";
		debug_print_backtrace();
	}
}

function header_redirect( $requestUri )
{
	if( php_sapi_name() !== "cli" )
	{
		if( Config::SITE_HTTPS != '' )
		{
			if( !defined( 'BASE_PATH_SECURE' ) ) define( 'BASE_PATH_SECURE', Config::SITE_HTTPS );
			if( !defined( 'PATH_NAME' ) ) define( 'PATH_NAME', Config::PATH_NAMES );
			
			if( 'https://'.$_SERVER['HTTP_HOST'].'/' != BASE_PATH_SECURE || $_SERVER['SERVER_PORT'] != 443 )
			{
				header( 'Location:'.BASE_PATH_SECURE.substr( $requestUri, 1 ) );
				exit();
			}
		}
		else if( $_SERVER['SERVER_PORT'] == 80 )
		{
			define( 'BASE_PATH', Config::SITE_HTTP );
			define( 'PATH_NAME', Config::PATH_NAME );
			
			if( 'http://'.$_SERVER['HTTP_HOST'].'/' != BASE_PATH )
			{
				header( 'Location:'.BASE_PATH.substr( $requestUri, 1 ) );
				exit();
			}
		}
		else
		{
			header( 'Location:'.Config::SITE_HTTP.substr( $requestUri, 1 ) );
			exit();
		}

		header( 'Location:'.Config::SITE_HTTP.substr( $requestUri, 1 ) );
	}
}

function boot( $request_uri )
{
	// Retira a path do inicio e cria a var $url com o restante
	$url = str_replace( PATH_NAME, '', $request_uri );

	$ignore_get = strpos( $url, '?' );
	if( $ignore_get !== false )
	{
		parse_str(substr($url, $ignore_get+1), $_GET);
		$url = substr( $url, 0, $ignore_get );
	}

	// Cria um array com o resto do URL	
	$array_tmp_uri = preg_split('[\\/]', $url, -1, PREG_SPLIT_NO_EMPTY);
	
	// Aqui vamos definir o que representa o resto do URL
	$array_uri['request_uri'] = $request_uri;
	$array_uri['short_uri'] = $url;
	$array_uri['controller'] = isset($array_tmp_uri[0]) ? $array_tmp_uri[0] : 'home';
	$array_uri['method'] = isset($array_tmp_uri[1]) ? $array_tmp_uri[1] : 'void';
	$count_segments = count($array_tmp_uri);
	$vars = array();

	for( $i = 2; $i < $count_segments; $i++ )
		$vars[] = $array_tmp_uri[$i];

	$array_uri['vars'] = $vars;
	
	define( 'PAGE', $array_uri['controller'] );
	define( 'HOME', BASE_PATH.PATH_NAME );
	define( 'VIEW', HOME.$array_uri['controller'].'/' );
	define( 'BASE', basename( VIEW ) );

	// Carrega a aplicacao
	require_once( 'boot/'.Config::APPLICATION_STARTER.'.php' );

	$mainApp = new MainApplication( $array_uri );
}

boot( $request_uri );

?>