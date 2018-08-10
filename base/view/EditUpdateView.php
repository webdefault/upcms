<?php

header('Content-Type: application/json');

$json = array(
	'action-page' => 'update',
	'action-modal' => 'update',
	'updates' => array()
	);

if( @$error )
{
	$json['message'] = array('type' => 'error');
	
	if( @$page->edit->message->validation )
	{
		switch( $page->edit->message->validation )
		{
			case 'first-invalid-field':
				$invalid = reset( $validation );
				$json['message']['text'] = $invalid['message'];
				break;
			
			default:
				break;
		}
	}
	else
	{
		if( count( $validation ) == 1 )
		{
			$invalid = reset( $validation );
			$json['message']['text'] = $invalid['message'];
		}
		else
		{
			$json['message']['text'] = 'Por favor, verifique os campos e tente novamente.';
		}
	}
}

$columns = $request->visibleColumnsName();

$vals = isset( $validation ) ? array_keys( $validation ) : array();

function object_replace( $obj, $list )
{
	foreach( $list AS $key => $val )
	{
		$obj->{$key} = $val;
	}
	
	return $obj;
}
load_lib_file( 'cms/create_view_object' );

$edit = @$page->edit;
$l = @$edit->layout ? $edit->layout : $mapName;

$validation = isset( $validation ) ? $validation : array();

$fieldHandler = function( $col, $result ) use ( $validation, $vals )
{
	if( in_array( $col, $vals ) )
	{
		if( $result ) object_replace( $result, $validation[$col] );
		
		unset( $validation[$col] );
	}
};

$results = new stdClass();
$values = array();
foreach( $layout->get($l) AS $obj )
{
	update_view_object( $results, $updates, $obj, $request, $values, $fieldHandler );
}

if( @$map->search )
{
	foreach( $map->search As $key => $obj )
	{
		$temp = new stdClass();
		$temp->target = $key;
		$temp->type = 'field';
		update_view_object( $results, $updates, $temp, $request->search, $values, $fieldHandler );
	}
}
foreach( $validation AS $key => $val )
{
	$results->{$key} = $val;
}

$json['updates'] = $results;

echo json_encode( $json );

?>