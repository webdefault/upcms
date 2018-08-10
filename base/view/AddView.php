<?php

header('Content-Type: application/json');

$ct_path = implode( ',', $current_table );
$json = array(
	'title' => $page->add->title,
	'toolbar' => array(),
	'modal-params' => @$page->add->{'modal-params'},
	
	'optsbar' => array(),
	
	'container' => array()
	);

$form = array(
	'type' => 'form',
	'action' => 'add-content/save/'.$ct_path.'/'.$raw_map_id,
	'method' => 'post',
	
	'buttons' => array(
		'submit' => array(
			'position' => 'toolbar',
			'title' => @$page->add->{'submit-button-name'} ? $page->add->{'submit-button-name'} : "Salvar",
			'class' => 'btn-primary' ),
		'cancel' => array(
			'position' => 'toolbar',
			'title' => 'Cancelar',
			'class' => 'btn-default' )
		),
	
	'subs' => array()
	);

if( @$page->add->action )
{
	$form['action'] = parse_bracket_instructions( $page->add->action, $values );
}

$edit = $page->add;
if( @$edit->mode === NULL ) $edit->mode = 'tabs';

if( $edit->mode != 'tabs' )
	echo 'Modo '.$edit->mode.' não suportado.'; 
else 
{
	load_lib_file( 'cms/create_view_object' );
	$l = @$edit->layout ? $edit->layout : $mapName;
	foreach( $layout->get($l) AS $obj )
	{
		$form['subs'][] = create_view_object( $obj, $request, $values );
	}
}

$json['container'][] = $form;

echo json_encode( $json );

?>