<?php

header('Content-Type: application/json');

$ct_path = implode( ',', $current_table );

if( $success )
{
	$json = array(
		'action-modal' => @$page->add->{'redirect-after-submit'} ? parse_bracket_instructions( $page->add->{'redirect-after-submit'}, $values ) : 'nothing',
		'action-page' => @$page->add->{'redirect-after-submit'} ? parse_bracket_instructions( $page->add->{'redirect-after-submit'}, $values ) : 'nothing'
		);

	if( @$page->add->message->save || @$page->add->message->save === false )
	{
		if( $page->add->message->save !== false )
		{
			$json['message'] = array(
				'text' => $page->add->message->save,
				'type' => 'success' );
		}
	}
	else
	{
		$json['message'] = array(
				'text' => 'Adicionado com sucesso.',
				'type' => 'success' );
	}
}
else
{
	$json = array(
		'action-popup' => 'nothing',
		'action-page' => 'nothing',
		'message' => array(
			'type' => 'error' ),
		'updates' => $invalid_fields
		);

	if( @$page->add->message->validation )
	{
		switch( $page->add->message->validation )
		{
			case 'first-invalid-field':
				$invalid = reset($invalid_fields);
				$json['message']['text'] = $invalid['message'];
				break;
			
			default:
				break;
		}
	}
	else
	{
		$json['message']['text'] = 'Por favor, verifique os campos e tente novamente.';
	}
}

echo json_encode( $json );

?>