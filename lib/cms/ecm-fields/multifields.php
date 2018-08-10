<?php

load_lib_file( 'cms/fields/simpletext' );

class ECMMultiFields extends SimpletextField
{
	protected $submap, $subrequest, $subvalues;
	protected function usingSetupTable()
	{
		return false;
	}
	
	protected function getFrom( $targetName )
	{
		if( $targetName == $this->mapName )
		{
			return $this->map;
		}
		else
		{
			$from = @$this->map->reltables->{@$targetName};

			if( $from )
				return $from;
			else
			{
				$result = new stdClass();
				$result->id = 'id';
				$result->from = $targetName;
				$result->join = '';

				return $result;
			}
		}
	}
	
	protected function loadMapValues( $id )
	{
		$from = $this->getFrom( @$this->cfrom );
		$join = explode( '=', $from->join[0] );
		
		$subrequest = Request::createRequestFromTarget( $this->field->{'value-map'}, $this->submap, array(), array(), $this->db );
		
		$custom = new CustomField( $this->field->{'value-map'}, $join[0], '"'.$id.'"' );
		$custom->set( $subrequest, $this->field->{'value-map'}, $subrequest, array(), array(), NULL, $this->db );
		
		if( $id )
		{
			$mql = $subrequest->matrixQueryForSelect();
			$mql['data'][$this->field->{'value-map'}]['id'] = array( NULL, '' );
			$subvalues = $this->db->select( MatrixQuery::select( $mql ) );
		}
		
		return array( 'request' => $subrequest, 'values' => @$subvalues );
	}

	public function listView( $id, $values )
	{
		$temp = new stdClass();
		$temp->type = 'text';
		
		$page = CMS::pageByName( $this->field->{'value-map'} );
		$this->submap = CMS::mapByName( $this->field->{'value-map'} );
		
		$text = '';
		$total_columns = count( $page->list->fields );
		$t = $this->loadMapValues( $id );
		$request = $t['request'];
		$list = $t['values'];
		
		$glue = '';
		foreach( $list as $line )
		{
			foreach( $page->list->fields AS $key )
			{
				$column = $request->column( $key );
				$item = $this->submap->fields->{$key};
				
				$col = $column['field']->listView( $line['id'], @$line );
				$text .= $glue.'<b>'.parse_bracket_instructions( @$item->title, $line ).':</b> '.$col->value;
				$glue = ', ';
			}
		}
		
		$temp->value = $text;
		
		return $temp;
	}
	
	protected function loadMap( $mapId )
	{
		$this->submap = CMS::mapByName( $this->field->map );
		
		$from = $this->getFrom( @$this->cfrom );
		
		$this->subrequest = Request::createRequestFromTarget( $this->field->map, $this->submap, array(), $mapId, $this->db );
		
		$mql = $this->subrequest->matrixQueryForSelect();
		// echo MatrixQuery::select( $mql ); exit;
		$this->subvalues = $this->db->select( MatrixQuery::select( $mql ) );
	}
	
	public function addView( $values )
	{
		$list = $this->mapId;
		if( @$value['id'] ) $list[$this->mapName] = $values['id'];
		$this->loadMap( $list );
		
		
		$temp = new stdClass();
		$temp->size = 12;
		$temp->type = 'column';
		$temp->subs = array();
		
		$page = CMS::pageByName( $this->field->map );
		
		load_lib_file( 'cms/create_view_object' );
		foreach( $this->subvalues AS $item )
		{
			foreach( $page->edit->layout AS $obj )
			{
				// print_r( $item );
				$temp->subs[] = create_view_object( $obj, $this->subrequest, $item, function( &$field ) use( $item )
				{
					$field->id = $this->fieldName.'['.$item['id'].']['.$field->id.']';
					return $field;
				} );
			}
		}
		
		return $temp;
	}
	
	public function editView( $values )
	{
		if( !@$value['id'] )
			return $this->addView( $values );
		else
		{
			$this->submap = CMS::mapByName( $this->field->{'value-map'} );

			$t = $this->loadMapValues( $values['id'] );
			$request = $t['request'];
			$list = $t['values'];
			
			// print_r( $list ); exit;
			$temp = new stdClass();
			$temp->size = 12;
			$temp->type = 'column';
			$temp->subs = array();
			
			$page = CMS::pageByName( $this->field->map );
			
			load_lib_file( 'cms/create_view_object' );
			foreach( $list AS $item )
			{
				foreach( $page->edit->layout AS $obj )
				{
					// print_r( $item );
					$temp->subs[] = create_view_object( $obj, $request, $item, function( &$field ) use( $item )
					{
						$field->id = $this->fieldName.'['.$item['id'].']['.$field->id.']';
						return $field;
					} );
				}
			}
			
			return $temp;
		}
	}
	
	public function select( $quick = false )
	{
		// $this->request->setColumnUsing( $this->cfrom, $this->fieldName, true );
		// $this->options = $this->loadOptions();
	}

	public function insert( $values )
	{
		$list = $this->mapId;
		if( @$value['id'] ) $list[$this->mapName] = $values['id'];
		
		$this->loadMap( $list );
		$colvals = @$values[$this->column()];
		
		$from = $this->getFrom( @$this->cfrom );
		$join = explode( '=', $from->join[0] );
		$path = explode( '.', $join[1] );
		
		$map = CMS::mapByName( $this->field->{'value-map'} );
		
		$num = 0;
		foreach( $this->subvalues AS $item )
		{
			$id = $item['id'];
			$linevals = $colvals[$id];
			
			$relName = $this->fieldName.'_'.$num;
			$num++;
			
			$rel = new stdClass();
			$rel->id = $from->id;
			$rel->table = $from->table;
			$rel->join = $from->join;
			$this->map->reltables->{$relName} = $rel;
			
			foreach( $linevals AS $key => $fieldValue )
			{
				$field = clone( $map->fields->{$key} );
				$field->from = $relName;
				
				if( !@$field->column ) $field->column = $key;
				
				$class = CMSFieldClass( $field->type );
				$obj = new $class( $key );
				
				$obj->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, $field, $this->db );
				$obj->insert( $linevals );
			}
		}
	}
	
	public function update( $values )
	{
		$list = $this->mapId;
		if( @$value['id'] ) $list[$this->mapName] = $values['id'];
		
		$this->loadMap( $list );
		$colvals = @$values[$this->column()];
		
		$from = $this->getFrom( @$this->cfrom );
		$join = explode( '=', $from->join[0] );
		$path = explode( '.', $join[1] );
		
		$map = CMS::mapByName( $this->field->{'value-map'} );
		
		$num = 0;
		foreach( $this->subvalues AS $item )
		{
			$id = $item['id'];
			$linevals = $colvals[$id];
			
			$relName = $this->fieldName.'_'.$num;
			$num++;
			
			$rel = new stdClass();
			$rel->id = $from->id;
			$rel->table = $from->table;
			$rel->join = $from->join;
			$this->map->reltables->{$relName} = $rel;
			
			foreach( $linevals AS $key => $fieldValue )
			{
				$field = clone( $map->fields->{$key} );
				$field->from = $relName;
				
				if( !@$field->column ) $field->column = $key;
				
				$class = CMSFieldClass( $field->type );
				$obj = new $class( $key );
				
				$obj->set( $this->request, $this->mapName, $this->map, $this->path, $this->mapId, $field, $this->db );
				$obj->update( $linevals );
			}
		}
	}
}

global $__CMSFields;
$__CMSFields['ecm-multifields'] = 'ECMMultiFields';

?>