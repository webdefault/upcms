<?php

function i18n_add_texts( $values )
{
	global $GLOBALS;
	
	if( !isset( $GLOBALS['AppLanguageWords'] ) )
	{
		$GLOBALS['AppLanguageWords'] = array();
	}
	
	$GLOBALS['AppLanguageWords'] = array_merge( $GLOBALS['AppLanguageWords'], $values );
}

if( defined( 'Config::LANGUAGE' ) )
{
	load_lib_file( 'cms/i18n/'.Config::LANGUAGE );
	if( file_exists( INCLUDE_PATH.'boot/i18n/'.$GLOBALS['Language'].'.php' ) )
		require_once( INCLUDE_PATH.'boot/i18n/'.$GLOBALS['Language'].'.php' );
}

function l( $name, $list = array() )
{
	global $GLOBALS;
	
	return __lf( $name, $list );
}

function el( $name, $list = array() )
{
	global $GLOBALS;
	
	echo __lf( $name, $list );
}

function eul( $name, $list = array() )
{
	global $GLOBALS;
	
	echo strtoupper( __lf( $name, $list ) );
}

function eufl( $name, $list = array() )
{
	global $GLOBALS;
	
	echo ucfirst( __lf( $name, $list ) );
}

function euwl( $name, $list = array() )
{
	global $GLOBALS;
	
	echo ucwords( __lf( $name, $list ) );
}

function __lf( $name, $list = array() )
{
	return vsprintf( $GLOBALS['AppLanguageWords'][$name], $list );
}

?>