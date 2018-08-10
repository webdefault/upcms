<?php


/**
 * @package model
 * @classe home
 *
 */

class JSONConfig
{
	private $prefix;
	private $mode;
	private $map;
	
	function __construct( $prefix, $mode = 1 )
	{
		$this->prefix = $prefix;
		$this->mode = $mode;
		$this->map = array();
	}
	
	function get( $name, $suffix = NULL )
	{
		$obj = @$this->map[$name.$suffix];
		
		if( !$obj )
		{
			switch ( $this->mode )
			{
			 	case 2:
			 		$obj = $this->get2( $name, $suffix );
			 		break;
			 	
			 	case 1:
			 	default:
			 		$obj = $this->get1( $name, $suffix );
			 		break;
			 }
		}
		
		return $obj;
	}
	
	private function get1( $name, $suffix )
	{
		// echo INCLUDE_PATH.CMSConfig::CMS_DIR.'/'.$name.'/'.$prefix.$suffix.'.json';
		$fname = $suffix ? $this->prefix.'-'.$suffix : $this->prefix;
		$obj = json_decode( file_get_contents( Config::FULL_APP_PATH.CMSConfig::CMS_DIR.'/'.$name.'/'.$fname.'.json' ) );
		
		if( $obj === null )
		{
			if( Config::ENV == "development" )
			{
				debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				CMS::exitWithMessage( 'error', $this->jsonError().' '.CMSConfig::CMS_DIR.'/'.$name.'/'.$fname.'.json' );
			}
			else
			{
				CMS::exitWithMessage( 'error', 'Problema ao carregar informações.' );
			}
		}
		
		$this->map[$name.$suffix] = $obj;
		
		return $obj;
	}
	
	private function get2( $name, $suffix )
	{
		$obj = json_decode( file_get_contents( Config::FULL_APP_PATH.CMSConfig::CMS_DIR.'/'.$this->prefix.'/'.$name.'.json' ) );
		
		if( $obj === null && Config::ENV == "development" )
		{
			CMS::exitWithMessage( 'error', $this->jsonError().' '.CMSConfig::CMS_DIR.'/'.$this->prefix.'/'.$name.'.json' );
		}
		
		$this->map[$name.$suffix] = $obj;
		
		return $obj;
	}
	
	private function jsonError()
	{
		switch (json_last_error())
		{
			case JSON_ERROR_NONE:
				return 'No errors';
				break;
				
			case JSON_ERROR_DEPTH:
				return 'Maximum stack depth exceeded';
				break;
				
			case JSON_ERROR_STATE_MISMATCH:
				return 'Underflow or the modes mismatch';
				break;
				
			case JSON_ERROR_CTRL_CHAR:
				return 'Unexpected control character found';
				break;
				
			case JSON_ERROR_SYNTAX:
				return 'Syntax error, malformed JSON';
				break;
				
			case JSON_ERROR_UTF8:
				return 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
				
			default:
				return 'Unknown error';
				break;
		}
	}
}

?>