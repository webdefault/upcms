<?php

/**
 * @package controller
 * @classe home
 *
 */

class Home extends CMS
{
	function __construct( $context )
	{
		parent:: __construct( $context );
		// $this->header['pageTitle']  = ' - Home';
	}

	function index( $vars )
	{
		$this->loadView( 'Home', $this->viewVars );
		//load_lib_file( 'lib/backup' );

		//$backup = new Backup(Config::BASE);
		//$backup->backupDb();
		//print_r($backup->listDir());
		//$backup->restoreBackup('1387284950');
	}
}

?>