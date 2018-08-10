<?php

/**
 * @package controller
 * @classe home
 *
 */

// ListContent could be just List, but it's a reserved word
class ListContent extends CMS
{
	protected $searching;
	
	function __construct( $context )
	{
		parent:: __construct( $context );

		$this->loadModel( 'List' );
		$this->loadModel( 'Access' );
		$this->loadModel( 'Util' );
		
		$this->searching = false;
		// $this->header['pageTitle']  = ' - Home';
	}
	
	public function index( $vars )
	{
		$path = array_shift( $vars );
		$path = str_replace( '-', '_', $path );
		$path = explode( ',', $path );
		
		$this->viewVars['current_table'] = implode(',', $path );
		$this->viewVars['current_path'] = 'search/';
		
		$mapName = array_shift( $path );
		$this->mapName = $mapName;
		$ids = NULL;
		
		if( count( $path ) > 0 )
		{
			$ids = parseMapId( array_shift( $vars ), $default );
			
			$field = end( $path );
			$fields = $page->subpages->{$field}->list->fields;
			$subfie = @$page->subpages->{$field}->list->appends;
			if( $subfie ) $fields = array_merge( $fields, $subfie );
			
			$map = $this->config['map']->get($mapName);
			$page = $this->config['pages']->get($mapName);
			
			$this->viewVars['submap'] = &$map->fields->{$field};
			$this->viewVars['subpage'] = $page->subpages->{$field};
		}
		else
		{
			$map = $this->config['map']->get($mapName);
			$page = $this->config['pages']->get($mapName);
			
			$info = get_object_vars( $page->list->fields );
			$fields = array_keys( $info );
			$subfie = @$page->subpages->{$field}->list->appends;
			if( $subfie ) $fields = array_merge( $fields, $subfie );
		}
		
		$opt = array( 'page' => 1 );
		if( @$page->list->order )
		{
			$opt['order'] = $page->list->order;
		}
		
		$options = CMS::parseSlashGet( $vars, $opt );
		
		if( $this->searching )
		{
			$post = CMS::globalValue( 'POST' );
			
			if( @$map->search )
			{
				foreach( $map->search AS $k => $o )
				{
					if( @$options[$k] ) $post[$k] = $options[$k];
				}
			}
			
			CMS::addGlobalValue( 'POST', $post );
		}
		
		// CMS::addGlobalValue( 'OPTIONS', $options );

		$access = $this->model->access->check( 'list', $mapName, $map, $path, $ids );

		if( count( $access ) > 0 )
		{
			$this->viewVars['invalid_fields'] = $access;
			$this->viewVars['success'] = false;
		}
		else
		{
			$result = $this->model->list->search( 
				$mapName,
				$map,
				$path, 
				$fields,
				$ids,
				$options['page']-1, 
				@$options['order'],
				$page->list,
				$options );
			
			$this->viewVars['search'] = $options;
			$this->viewVars['mapName'] = $mapName;
			$this->viewVars['list'] = $result['list'];
			$this->viewVars['request'] = $result['request'];
			$this->viewVars['total_list_rows'] = $result['total'];
			
			$this->viewVars['searching'] = $this->searching;
			
			$this->viewVars['page'] = $page;
			$this->viewVars['limit'] = $page->list->{'page-limit'};
			// $this->viewVars['queryPage'] = $options['page'];
			$this->viewVars['options'] = $options;
			$this->viewVars['map'] = &$map;

			$this->viewVars['success'] = $result ? true : false;
			$this->viewVars['layout'] = $page = $this->config['layout'];
		}
		
		if( $this->viewVars['success'] )
			$this->loadView( 'List', $this->viewVars );
		else
			$this->loadView( 'ValidationError', $this->viewVars );
	}
	
	public function search( $vars )
	{
		$this->searching = true;
		$this->index( $vars );
	}
}

?>
