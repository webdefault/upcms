<?php

/**
 * Manage the options. All options is saved in sys options table from db.
 * @author Orlando Leite
 * @version 0.8
 * @package core
 * @subpackage internal
 * @access public
 * @name Options
 */
class Options
{
	private static $db, $table, $opt, $autocommit;
	
	/**
	* Constructor
	* Select all options and put in an array.
    * @author Orlando Leite
    * @access public
    * @return Options
    */
	private function __consctruct()
	{
		
	}
	
	public static function setup( $dbConnection, $table, $autocommit = TRUE )
	{
		self::$db = $dbConnection;
		self::$table = $table;
		self::$autocommit = $autocommit;

		$result = $dbConnection->select( 'SELECT * FROM '.$table.' ORDER BY class ASC' );
		
		self::$opt = array();
		$lastclass = '';
		foreach( $result AS $item )
		{
			if( $item['class'] != $lastclass )
			{
				self::$opt[$item['class']] = array();
				$lastclass = $item['class'];
			}

			self::$opt[$lastclass][$item['name']] = $item['value'];
		}
	}
	
	/**
	* Get a option value.
	* @author Orlando Leite
    * @access private
    * @param string $class the class of this option, use lowercase. Options of CMS uses cms.
    * @param string $name the option name.
    * @return string the option value.
    */
	public static function get( $class, $name )
	{
		return isset( self::$opt[$class] ) ? @self::$opt[$class][$name] : NULL;
	}
	
	/**
	* Set a option value.
	* @author Orlando Leite
    * @access private
    * @param string $class the class of this option, use lowercase. Options from CMS uses cms.
    * @param string $name the option name.
    * @param string $value the value to be set.
    * @param string $status currently status of the option. In case of false, the value will not be returned from 'get'.
    * @return string the option value.
    */
	public static function set( $class, $name, $value, $status = true )
	{
		if( !isset( self::$opt[$class] ) )
			self::$opt[$class] = array();

		if( $status == '' || $status == 'true' )
			self::$opt[$class][$name] = $value;
		else
			self::$opt[$class][$name] = NULL;
		
		if( $value !== NULL )
		{
			if( count( self::$db->select( 'SELECT * FROM '.self::$table.' WHERE class = ? AND name = ?', 
				array( 'class' => $class, 'name' => $name ) ) ) > 0 )
			{
				self::$db->update( self::$table, 
					array( 
						'value'=>$value, 
						'status'=>$status 
					), 
					array( 'class'=>$class, 'name'=>$name ), self::$autocommit );
			}
			else
			{
				self::$db->insert( self::$table, array( array( 
					'class'=>$class, 
					'name'=>$name, 
					'value'=>$value, 
					'status'=>$status 
				) ), self::$autocommit );
			}
		}
		else
		{
			self::$db->delete( self::$table, array( array( 'class' => $class, 'name' => $name ) ), self::$autocommit );
		}
	}
}

?>