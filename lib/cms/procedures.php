<?php

/**
 * @package controller
 * @classe home
 *
 */

class CMSProcedures
{
	private static $instance = NULL, $functions = array();

	private $object, $db, $debug;

	private function __construct( $mapName, $map, $path, $ids, $db, $values )
	{
		$object = new stdClass();
		$object->mapName = $mapName;
		//$object->map = $map;
		$object->path = $path;
		$object->ids = $ids;
		//print_r( $ids );
		$object->main_id = @$ids[$mapName];
		$object->values = $values;
		$this->db = $db;

		$this->object = $object;
		
		$this->debug = array();
	}
	
	public static function getInstance()
	{
		return self::$instance;
	}

	public function addValue( $name, $value )
	{
		$this->object->{$name} = $value;
	}

	public function getObject()
	{
		return $this->object;
	}

	public function select( $mql )
	{
		if( Config::ENV == 'development' )
		{
			$this->debug[] = array( 'select', MatrixQuery::select( $mql ), MatrixQuery::select( $mql, $this->db ) );
		}
		// echo MatrixQuery::select( $mql );exit;
		return MatrixQuery::select( $mql, $this->db );
	}
	
	public function getDebug()
	{
		return $this->debug;
	}

	public function insert( $mql )
	{
		// print_r( $mql );
		MatrixQuery::insert( $mql, $this->db );
		
		if( Config::ENV == 'development' )
		{
			$this->debug[] = array( 'delete', MatrixQuery::getDebug() );
		}
	}

	public function update( $mql )
	{
		// print_r( $mql );
		// echo MatrixQuery::printQuery( $mql );exit;
		MatrixQuery::update( $mql, $this->db );
		
		if( Config::ENV == 'development' )
		{
			$this->debug[] = array( 'update', MatrixQuery::getDebug() );
		}
	}
	
	public function delete( $mql )
	{
		// print_r( $mql );
		// echo MatrixQuery::printQuery( $mql );exit;
		MatrixQuery::delete( $mql, $this->db );
		
		if( Config::ENV == 'development' )
		{
			$this->debug[] = array( 'delete', MatrixQuery::getDebug() );
		}
	}

	public function getDB()
	{
		return $this->db;
	}

	public static function setup( $mapName, $map, $path, $ids, $db, $values )
	{
		self::$instance = new CMSProcedures( $mapName, $map, $path, $ids, $db, $values );
		self::setupDefaulValidators();
	}

	private static function setupDefaulValidators()
	{
		load_lib_file( 'cms/procedures/mql' );
		load_lib_file( 'cms/procedures/return' );
		load_lib_file( 'cms/procedures/loadurl' );
		load_lib_file( 'cms/procedures/exec' );
	}

	public static function addProcedure( $type, $func )
	{
		self::$functions[$type] = $func;
	}

	public static function apply( $func, $values = NULL )
	{
		// print_r( $values );
		if( $values ) self::$instance->object->values = $values;
		
		foreach( $func AS $process )
		{
			$tfunc = @self::$functions[$process->type];
			$result = $tfunc( $process, self::$instance );

			if( $process->type == 'return' ) return $result;
		}
	}
}

?>