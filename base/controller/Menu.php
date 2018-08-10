<?php

/**
 * @package controller
 * @classe home
 *
 */

class Menu extends CMS
{
	function __construct( $context )
	{
		parent:: __construct( $context );
		$this->loadModel( 'Access' );
		// $this->header['pageTitle']  = ' - Home';
	}

	function index( $vars )
	{
		$this->config['menu'] = json_decode( file_get_contents( Config::FULL_APP_PATH.CMSConfig::CMS_DIR.'/menu.json' ) );
		
		$menu = new stdClass();
		
		$menu = $this->model->access->getMenu( $this->config['menu']->menu, $this->config['map'] );
		// $this->loadMenu();
		$this->viewVars = array( 'menu' => $menu, 'init' => @$this->config['menu']->init );
		$this->loadView( 'JSON', array( 'json' => $this->viewVars ) );
		//load_lib_file( 'lib/backup' );

		//$backup = new Backup(Config::BASE);
		//$backup->backupDb();
		//print_r($backup->listDir());
		//$backup->restoreBackup('1387284950');
	}
	
	private function loadMenu()
	{
		// $menu = array();
		/*
		foreach( $this->config['menu'] AS $key => $item )
		{
			$menu[$key] = array( 'title' => $item['title'], 'icon' => $item['icon'], 'controller' => '' );
		}

		foreach( $this->config['table'] AS $key => $item )
		{
			if( isset( $item['menu'] ) )
			{
				array_push( $menu[$item['menu']],
					array(
						'name' => $key,
						'title' => $item['title'],
						'icon' => $item['icon'],
						'link' => $item['link'] ) );
			}
			else
			{
				$menu[$key] = array(
					'title' => $item['title'],
					'icon' => $item['icon'],
					'controller' => ( $item['viewas'] == 'edition' ? 'list-content' : 'view' )
				);
			}
		}
		*/
		$this->context->menu = $this->config['menu'];
	}
}

?>
