<?php

header( 'Content-Type: application/json' );

$ct_path = implode( ',', $current_table );
$json = array(
	'title' => parse_bracket_instructions( $page->edit->title, $values ),
	'toolbar' => array(),
	'modal-params' => @$page->edit->{'modal-params'},
	
	'optsbar' => array(
		),
	
	'container' => array()
	);

$form = array(
	'type' => 'form',
	'action' => 'edit-content/save/'.$ct_path.'/'.$raw_map_id,
	'method' => 'post',
	
	'buttons' => array(
		'submit' => array(
			'position' => 'toolbar',
			'title' => @$page->edit->{'submit-button-name'} ? $page->edit->{'submit-button-name'} : "Salvar",
			'class' => 'btn-primary' ),
		'cancel' => array(
			'position' => 'toolbar',
			'title' => 'Cancelar',
			'class' => 'btn-default' )
		),
	
	'subs' => array()
	);

if( @$page->edit->readonly )
{
	unset( $form['buttons']['submit'] );
	
	$form['readonly'] = true;
	$form['buttons']['cancel']['title'] = 'Fechar';
}

if( @$page->edit->action )
{
	$form['action'] = parse_bracket_instructions( $page->edit->action, $values );
}

$edit = $page->edit;
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
