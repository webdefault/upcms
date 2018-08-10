<?php

load_lib_file( 'webdefault/db/interface' );

class MySQL implements IDatabase
{
	private $_pdo, $lastError, $logFile;
	protected $transactionCounter = 0, $transactionSuccess = 0;
	
	public function beginTransaction()
	{
		if($this->transactionCounter == 0)
		{
			//print_r( "beginTransaction\n" );
			
			if( $this->_pdo->inTransaction() ) $this->_pdo->rollback();
			$this->_pdo->beginTransaction();
		}
		
		$this->transactionSuccess++;
		$this->transactionCounter++;
		
		// print_r( "beginTransaction".$this->transactionCounter.' == '.$this->transactionSuccess."\n" );
		// $this->_pdo->exec('SAVEPOINT trans'.$this->transactionCounter);
		
		return $this->transactionCounter >= 0;
	}
	
	public function setTransactionSuccessful()
	{
		// print_r( "setTransactionSuccessful\n" );
		$this->transactionSuccess--;
	}
	
	public function endTransaction()
	{
		// print_r( 'outsize: '.$this->transactionCounter.' == '.$this->transactionSuccess."\n"); 
		if( $this->transactionCounter != 0 )
		{
			$this->transactionCounter--;
			
			// error_log( ': '.$this->transactionCounter.' == '.$this->transactionSuccess );
			// print_r( $this->transactionCounter.' == '.$this->transactionSuccess."\n"); 
			if( $this->transactionCounter == $this->transactionSuccess )
			{
				if( $this->transactionCounter == 0 )
				{
					// print_r( "endTransaction" );
					return $this->_pdo->commit();
				}
			}
			else
			{
				// print_r( "rolling back" );
				$this->transactionCounter = 0;
				$this->transactionSuccess = 0;
				$this->_pdo->rollback();
				
				throw new Exception("Transaction has no success. Rolling back", 1);
			}
		}
	}

	public function __construct( $dbname, $user, $pass, $host = 'localhost', 
		$port = '3306', $ssl = false, $sslCa = NULL, $sslKey = NULL, $sslCert = NULL, $timezone = NULL )
	{
		if( defined( "MYSQL_LOG_FILE" ) )
		{
			$this->logFile = MYSQL_LOG_FILE;
			
			if( $this->logFile )
			{
				echo "logfie: ".$this->logFile;
				touch( $this->logFile );
				if( !is_writable( $this->logFile ) )
				{
					$this->logFile = NULL;
					error_log( "Error: MYSQL_LOG_FILE is not writable." );
				}
			}
		}
		
		try
		{
			$db = $dbname != NULL ? 'dbname='.$dbname : '';
			$opt = array( PDO::ATTR_PERSISTENT => true, PDO::ATTR_CASE => PDO::CASE_LOWER );
			
			if( $ssl )
			{
				$opt[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
				
				if( $sslCert )
				{
					$opt[PDO::MYSQL_ATTR_SSL_KEY] = $sslKey;
					$opt[PDO::MYSQL_ATTR_SSL_CERT] = $sslCert;
				}
			}
			
			$this->_pdo = new PDO('mysql:host='.$host.';port='.$port.';'.$db, $user, $pass, $opt);
			$this->_pdo->setAttribute( PDO::ATTR_ERRMODE, (@$_SERVER['SERVER_ADDR'] && $_SERVER['SERVER_ADDR'] == '127.0.0.1') ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT );
			// $this->_pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
			// 
			$this->slog("\n\n".date(DATE_ATOM)." PDO CONNECTED TO: mysql:host=".$host.';port='.$port.';'.$db.' user:'.$user );
		}
		catch( PDOException $e )
		{
			$this->slog("\n\nPDO CONNECTION ERROR: ".$e->getMessage() );
			exit( $e->getMessage() );
		}
		
		$this->_pdo->exec( 'SET CHARACTER SET utf8' );
		if( $timezone ) $this->_pdo->exec( 'SET @@session.time_zone = "'.$timezone.'"' );
	}
	
	private function slog( $text )
	{
		if( $this->logFile )
		{
			file_put_contents( $this->logFile, $text."\n", FILE_APPEND );
		} 
	}
	
	private function log( $sql, $values = NULL )
	{
		if( $this->logFile )
		{
			$text = date(DATE_ATOM).' '.$sql.';'.( $values ? ' BINDING: '.var_dump_to_str( $values ) : '');
			file_put_contents( $this->logFile, $text."\n", FILE_APPEND );
		} 
	}

	private function __clone(){}

	/*
	 * 	select registers
	 */
	public function select( $sql, $values = NULL, $fetch = PDO::FETCH_ASSOC )
	{
		assert( is_string( $sql ), print_r( $sql, true ) );
		
		$this->log( $sql, $values );
		
		$stmt = $this->_pdo->prepare( $sql );
		
		if( $values )
		{
			$i = 1;
			foreach( $values AS $k => $v )
			{
				// $stmt->bindValue( $i, $v, PDO::PARAM_STR );
				$stmt->bindValue( $i, $v, 
						is_bool( $v ) ? PDO::PARAM_BOOL : 
						( is_int( $v ) ? PDO::PARAM_INT : PDO::PARAM_STR ) );
				$i++;
			}
		}

		$t = $stmt->execute();
		if( $t )
		{
			$result = $stmt->fetchAll( $fetch );
		}
		else if( $t === false )
		{
			$this->log( print_r( $this->lastError = $stmt->errorInfo(), true ) );
			$result = false;
		}
		
		return $result;
	}
	
	/*
	 * 	Insere um novo registro
	 */
	public function insert( $table, $list )
	{
			foreach( $list as $k1 => $v1 )
			{
				try
				{
					$this->beginTransaction();
					
					$sql = self::createInsertSQL( $table, $v1 );
					$stmt = $this->_pdo->prepare($sql);
					$log = array();
					
					$i = 1;
					foreach( $v1 as $k2 => $v2 )
					{
						if( is_array( $v2 ) )
						{
							if( count( $v2 ) > 1 )
							{
								$log[] = $v = $v2[1];
								
								$stmt->bindValue( $i, $v, 
									is_bool( $v ) ? PDO::PARAM_BOOL : 
									( is_int( $v ) ? PDO::PARAM_INT : PDO::PARAM_STR ) );
								$i++;
							}
						}
						else
						{
							$log[] = $v = $v2;
							
							$stmt->bindValue( $i, $v, 
								is_bool( $v ) ? PDO::PARAM_BOOL : 
								( is_int( $v ) ? PDO::PARAM_INT : PDO::PARAM_STR ) );
							$i++;
						}
					}
					
					$this->log( $sql, $log );
					if( $stmt->execute() === false )
					{
						$this->log( print_r( $stmt->errorInfo(), true ) );
					}
					else
					{
						$this->setTransactionSuccessful();
					}
					
					$lastInsertId = $this->_pdo->lastInsertId();
				}
				catch ( PDOException $e )
				{
					//echo $e->getMessage();
					$this->lastError = $stmt->errorInfo();
					// $this->rollback();
				}
				finally
				{
					$this->endTransaction();
					return $lastInsertId;
				}
			}
	}
	
	/*
	 * 	Atualiza o registro
	 */
	public function update( $table, $values, $where )
	{
		$this->beginTransaction();
		
		try
		{
			$sql = self::createUpdateSQL( $table, $values, $where );
			$stmt = $this->_pdo->prepare( $sql );
			
			$log = array();
			
			$i = 1;
			foreach( $values AS $k2 => $v2 )
			{
				if( is_array( $v2 ) )
				{
					if( count( $v2 ) > 1 )
					{
						$log[] = $v = $v2[1];
						
						$stmt->bindValue( $i, $v, 
							is_bool( $v ) ? PDO::PARAM_BOOL : 
							( is_int( $v ) ? PDO::PARAM_INT : PDO::PARAM_STR ) );
						$i++;
					}
				}
				else
				{
					$log[] = $v = $v2;
					
					$stmt->bindValue( $i, $v, 
						is_bool( $v ) ? PDO::PARAM_BOOL : 
						( is_int( $v ) ? PDO::PARAM_INT : PDO::PARAM_STR ) );
					$i++;
				}
			}
			
			foreach( $where as $k2 => $v2 )
			{
				if( is_array( $v2 ) )
				{
					if( count( $v2 ) > 1 )
					{
						$log[] = $v = $v2[1];
						
						$stmt->bindValue( $i, $v, 
							is_bool( $v ) ? PDO::PARAM_BOOL : 
							( is_int( $v ) ? PDO::PARAM_INT : PDO::PARAM_STR ) );
						$i++;
					}
				}
				else
				{
					$log[] = $v = $v2;
					
					$stmt->bindValue( $i, $v, 
						is_bool( $v ) ? PDO::PARAM_BOOL : 
						( is_int( $v ) ? PDO::PARAM_INT : PDO::PARAM_STR ) );
					$i++;
				}
			}
			
			$this->log( $sql, $log );
			
			$result = $stmt->execute();
			if( $result === false )
			{
				$this->log( print_r( $stmt->errorInfo(), true ) );
				$count = false;
			}
			else
			{
				$count = $stmt->rowCount();
				$this->setTransactionSuccessful();
			}
		} 
		catch (PDOException $e)
		{
			//echo $e->getMessage();
			$this->lastError = $stmt->errorInfo();
			// $this->rollback();
		}
		finally
		{
			$this->endTransaction();
			return $count;
		}
	}
	
	/*
	 * 	Apaga o registro
	 */
	public function delete( $table, $wheres )
	{
		$count = 0;
		
		$this->beginTransaction();
		
		try
		{
			$values = array();
			foreach( $wheres as $k1 => $v1 )
			{
				$sql = self::createDeleteSQL( $table, $v1 );
				$stmt = $this->_pdo->prepare( $sql );
				
				foreach($v1 as $k2 => $v2)
				{
					$values[] = $v2;
					$stmt->bindValue( ':'.$k2, $v2 );
				}
				
				$stmt->execute();
				$count = $stmt->rowCount();
			}
			
			$this->log( @$sql, $values );
			$this->setTransactionSuccessful();
		} 
		catch ( PDOException $e )
		{
			//echo $e->getMessage();
			$this->lastError = $stmt->errorInfo();
			// $this->rollback();
		}
		finally
		{
			$this->endTransaction();
			
			return $count;
		}
	}
	
	public function tables()
	{
		echo 'tables not implemented.
		';
	}

	public function columns( $table )
	{
		echo 'columns not implemented.
		';
	}

	public function createTable( $name, $fields )
	{
		echo 'createTable not implemented.
		';
	}

	public function editTable( $name, $fields )
	{
		echo 'editTable not implemented.
		';
	}
	
	

	public function execute( $sql, $values = NULL, $fetch = PDO::FETCH_ASSOC )
	{
		$this->beginTransaction();
		
		try
		{
			$stmt = $this->_pdo->prepare( $sql );
			
			$this->log( $sql, $values );
			
			if( $values )
			{
				$i = 1;
				foreach( $values AS $k => $v )
				{
					// $stmt->bindValue( $i, $v, PDO::PARAM_STR );
					if( is_array( $v ) )
					{
						if( count( $v ) > 1 )
						{
							$log[] = $v = $v[1];
							
							$stmt->bindValue( $i, $v, 
								is_bool( $v ) ? PDO::PARAM_BOOL : 
								( is_int( $v ) ? PDO::PARAM_INT : PDO::PARAM_STR ) );
							$i++;
						}
					}
					else
					{
						$log[] = $v = $v;
						
						$stmt->bindValue( $i, $v, 
							is_bool( $v ) ? PDO::PARAM_BOOL : 
							( is_int( $v ) ? PDO::PARAM_INT : PDO::PARAM_STR ) );
						$i++;
					}
				}
			}
			
			if( $stmt->execute() )
			{
				$result = $stmt->fetchAll( $fetch );
				$this->setTransactionSuccessful();
			}
		}
		catch( Exception $e )
		{
			$this->lastError = $stmt->errorInfo();
			//$this->rollback();
		}
		finally
		{
			$this->endTransaction();
			
			return @$result;
		}
	}

	public function close()
	{
		$this->slog("PDO CLOSE CONNECTION\n\n" );
		unset( $this->_pdo );
	}

	public function row( $type = DB_ASSOC, $resource = NULL )
	{
		echo 'row() not implemented.
		';
	}

	public function error()
	{
		if( @$this->lastError && intval( @$this->lastError[0] ) != 0 )
			return $this->lastError;
		else
			return NULL;
	}


	private static function createInsertSQL( $table, $array ) 
	{
		$x = 'INSERT INTO '.$table.' (';
		
		$glue = '';	
		foreach($array as $k => $v)
		{
			$x.= $glue.$k;
			$glue = ', ';
		}
		
		$x.= ') VALUES (';
		
		$glue = '';
		foreach($array as $k => $v) 
		{
			$x.= $glue;

			if( is_array( $v ) ) 
				$x .= $v[0];
			else
				$x .= '?';

			$glue = ', ';
		}
		
		$x.= ')';
		
		return $x;
	}


	private static function createUpdateSQL( $table, $values, $where )
	{
		$x = 'UPDATE '.$table.' SET ';

		$glue = '';
		foreach( $values as $k => $v ) 
		{
			$x .= $glue.$k.' = '.( is_array( $v ) ? $v[0] : '?' );
			$glue = ', ';
		}

		$x.= ' WHERE ';

		$glue = '';
		foreach( $where as $k => $v )
		{
			$x .= $glue.$k.( is_array( $v ) ? ' '.$v[0] : ' = ? ' );
			$glue = ' AND ';
		} 

		return $x;
	}


	private static function createDeleteSQL( $table, $array )
	{
		$x = 'DELETE FROM '.$table.' WHERE ';

		foreach( $array as $k => $v ) $x.= ' AND '.$k.'=:'.$k;

		$x = preg_replace( '/ AND /', '', $x, 1);

		return $x;
	}
}

?>
