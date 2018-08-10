<?php

/**
 * Interface for DB connections. All classes made for DB connections should be use this.
 * @author Orlando Leite
 * @version 0.8
 * @package core
 * @access public
 * @name IDatabase
 */

interface IDatabase
{
	/**
	* Get all tables in the connected database.
	* @author Orlando Leite
	* @access public
	* @return array filled with all databases in the connected server.
	*/
	public function tables();
	
	/**
	* Get all columns from a table.
	* @author Orlando Leite
	* @access public
	* @param string $table the table to get columns.
	* @return array filled with all databases in the connected server.
	*/
	public function columns( $table );
	
	/**
	* Create a table in the current connected database.
	* @author Orlando Leite
	* @access public
	* @param string $name the new table name.
	* @param array $fields Columns of the new table. Use as key the name of the new column. The attributes should be 'type', 'unique' and 'unsigned'.
	* Type can be 'chars', 'byte, 'int, 'bigint, 'double', 'text' and 'timestamp'.
	* The attributes unique and unsigned is a boolean;
	* @return true for success.
	*/
	public function createTable( $name, $fields );
	
	/**
	* Alter a table in the current connected database.
	* @author Orlando Leite
	* @access public
	* @param string $name the new table name.
	* @param array $fields Columns of the new table. Use as key the name of the new column. The attributes should be 'do', 'type', 'unique' and 'unsigned'.
	* Set the 'do' for the action to do. It can be 'change', 'add', 'drop'.
	* Type can be 'chars', 'byte, 'int, 'bigint, 'double', 'text' and 'timestamp'.
	* The attributes unique and unsigned is a boolean;
	* @return true for success.
	*/
	public function editTable( $name, $fields );
	
	/**
	* Insert items to a table.
	* @author Orlando Leite
	* @access public
	* @param string $query what should be executed. e.g. 'SELECT * FROM MyDB'
	* @param integer $type what the type used in return mode. You can choose DB_ASSOC, DB_NUM or DB_BOTH
	* @return array an array with rows from query
	*/
	public function insert( $table, $list );
	
	/**
	* Execute a select query in DB.
	* @author Orlando Leite
	* @access public
	* @param string $query what should be executed. e.g. 'SELECT * FROM MyDB'
	* @param integer $type what the type used in return mode. You can choose DB_ASSOC, DB_NUM or DB_BOTH
	* @return array an array with rows from query
	*/
	public function select( $query );
	
	/**
	* Execute your query in DB.
	* @author Orlando Leite
	* @access public
	* @param string $query what should be executed. e.g. 'SELECT * FROM MyDB'
	* @return resource If you make a select, you should use row() for get the items.
	*/
	public function execute( $query );

	/**
	* Update item from a table;
	* @author Orlando Leite
	* @access public
	* @param string $query what should be executed. e.g. 'SELECT * FROM MyDB'
	* @return resource If you make a select, you should use row() for get the items.
	*/
	public function update( $table, $values, $where );
	
	
	/**
	* Close connection. some services host made this after php execution finish. But is highly recomended call after everything is done.
	* @author Orlando Leite
	* @access public
	* @return boolean true for success.
	*/
	public function close();
	
	/**
	* Get a row. If you made a select using execute method, use this for get the rows.
	* @author Orlando Leite
	* @access public
	* @param integer $type what the type used in return mode. You can choose DB_ASSOC, DB_NUM or DB_BOTH
	* @param resource $resource Where the row came from. The default value is the last execute() resource.
	* @return object an object with values selected.
	*/
	public function row( $type = DB_ASSOC, $resource = NULL );
	
	/**
	* In case of error, you can get what happens calling this.
	* @author Orlando Leite
	* @access public
	* @return string error info.
	*/
	public function error();
	
	/**
	* Delete a row from table wheres AND.
	* @author Orlando Leite
	* @access public
	* @param string $table target table
	* @param array $wheres delete item(s) when match all attributes $key=$value AND $key=$value ...
	* @return string error info.
	*/
	public function delete( $table, $wheres );
}