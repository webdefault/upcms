<?php

/**
 * @package model
 * @classe home
 *
 */

load_lib_file( 'cms/fields' );
load_lib_file( 'cms/request' );
load_lib_file( 'webdefault/db/mysql/matrixquery' );

// It need to be updated like other models
class ListModel extends BaseModel
{
	function __construct( $context )
	{
		parent::__construct( $context );
	}

	function get( $mapName, $map, $path, $fields, $ids, $queryPage, $orderBy, $page )
	{
		/*
		$request = Request::createRequestFromTarget( $mapName, $map, $path, $ids, $this->db );
		
		$limit = isset( $page->{'page-limit'} ) ? $page->{'page-limit'} : 30;
		$fields = array_keys(get_object_vars( $page->fields ));
		$mql = $request->matrixQueryForSelect( $fields );

		// MatrixQuery::printQuery( $mql ); exit;

		// Add column id
		if( @$map->{"custom-mql"} )
		{
			load_lib_file( 'cms/parse_json_mql' );

			$mql = parse_json_mql( $map->{"custom-mql"}, array() );
		}
		else
		{
			$temp = $mql['tables'][ $mql['target'] ];
			$mql['data'][ $mql['target'] ][ 'id' ] = array( $temp['id'], '' );
		}
		
		// MatrixQuery::printQuery( $mql ); exit;
		// print_r( MatrixQuery::select( $mql ) ); exit;
		
		// Get total rows
		// $selid = array_push( $mql['select'], 'count(*) AS temp_cms_total_rows' ) - 1;
		// print_r( MatrixQuery::select( $mql ) ); exit;
		// 
		if( isset( $map->{"custom-list-where"} ) )
		{
			$mql['custom-where'] = $map->{"custom-list-where"};
		}
		
		if( !@$mql['group'] ) $mql['group'] = [$mapName.'.id'];
		
		// Get list page
		// unset( $mql['select'][$selid] );
		$mql['slice'] = array( $queryPage * $limit, $limit );
		if( @$mql['order'] == null ) $mql['order'][] = $orderBy;
		
		$mql['options'] = array('count'=>true);

		// print_r( MatrixQuery::select( $mql ) );exit;
		$result = array( 
			'list' => MatrixQuery::select( $mql, $this->db ),
			'request' => $request
			);
		
		$ttl = $this->db->select( 'SELECT FOUND_ROWS() AS found;' );
		$result['total'] = $ttl[0]['found'];
		
		if( $result['total'] == 0 && $this->db->error() && Config::ENV == 'development' )
		{
			$result = $this->db->error();
			unset( $mql['select'][0] );
			CMS::exitWithMessage( 'error', $result, array( 'mql' => $mql, 'sql' => MatrixQuery::select( $mql ) ) );
		}
 		// print_r( $result );
 		// print_r( $this->db->error() );
		return $result;
		*/
	
		return $this->search( $mapName, $map, $path, $fields, $ids, $queryPage, $orderBy, $page, NULL );
	}
	
	function search( $mapName, $map, $path, $fields, $ids, $queryPage, $orderBy, $page, $search )
	{
		$request = Request::createRequestFromTarget( $mapName, $map, $path, $ids, $this->db );
		
		$limit = isset( $page->{'page-limit'} ) ? $page->{'page-limit'} : 30;
		$fields = array_keys(get_object_vars( $page->fields ));
		$mql = $request->matrixQueryForSelect( $fields );

		// MatrixQuery::printQuery( $mql ); exit;

		// Add column id
		if( @$map->{"custom-mql"} )
		{
			load_lib_file( 'cms/parse_json_mql' );

			$mql = parse_json_mql( $map->{"custom-mql"}, array() );
		}
		else
		{
			$temp = $mql['tables'][ $mql['target'] ];
			$mql['data'][ $mql['target'] ][ 'id' ] = array( $temp['id'], '', MatrixQuery::GET );
		}
		
		if( !@$mql['group'] ) $mql['group'] = [$mapName.'.id'];
		
		// Get list page
		// unset( $mql['select'][$selid] );
		$mql['slice'] = array( $queryPage * $limit, $limit );
		
		// print_r( $orderBy );
		if( @$orderBy != null ) $mql['order'][$orderBy[0]] = $orderBy[1];
		
		// print_r( $mql );
		
		$mql['options'] = array('count'=>true);
		
		$where = $wglue = '';
		$rsearch = $request->search;
		if( isset( $map->search ) && $search != null )
		{
			foreach( $map->search AS $key => $field )
			{
				$column = $rsearch->column($key);
				$value = $column['field']->search($request, $search);
				
				if( $value !== NULL )
				{
					$where .= $wglue.$value;
					$wglue = ' AND ';
				}
			}
		}
		
		if( $where != '' )
		{
			$mql['custom-where'] = $where;
		}
		
		if( isset( $map->{"custom-list-where"} ) )
		{
			if( $where )
				$mql['custom-where'] .= $wglue.'('.$map->{"custom-list-where"}.')';
			else
				$mql['custom-where'] = $map->{"custom-list-where"};
		}
		
		// print_r( MatrixQuery::select( $mql ) );exit;
		$result = array( 
			'list' => MatrixQuery::select( $mql, $this->db ),
			'request' => $request
			);
		
		$ttl = $this->db->select( 'SELECT FOUND_ROWS() AS found;' );
		$result['total'] = $ttl[0]['found'];
		
		if( $result['total'] == 0 && $this->db->error() && Config::ENV == 'development' )
		{
			$result = $this->db->error();
			unset( $mql['select'][0] );
			CMS::exitWithMessage( 'error', $result, array( 'mql' => $mql, 'sql' => MatrixQuery::select( $mql ) ) );
		}
 		// print_r( $result );
 		// print_r( $this->db->error() );
		return $result;
	}
}
