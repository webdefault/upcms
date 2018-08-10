<?php

header('Content-Type: application/json');

$json = array(
	'action-page' => 'nothing',
	'action-modal' => 'nothing',
	'message' => array(
		'type' => 'error' ),
	'updates' => $invalid_fields,
	'error' => true );

if( @$page->edit->message->validation )
{
	switch( $page->edit->message->validation )
	{
		case 'first-invalid-field':
			$invalid = reset( $invalid_fields );
			$json['message']['text'] = $invalid['message'];
			break;
		
		default:
			break;
	}
}
else
{
	if( count( $invalid_fields ) == 1 )
	{
		$invalid = reset( $invalid_fields );
		$json['message']['text'] = $invalid['message'];
	}
	else
	{
		$json['message']['text'] = 'Por favor, verifique os campos e tente novamente.';
	}
}

echo json_encode( $json );

?>