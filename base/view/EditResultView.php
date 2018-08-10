<?php

header('Content-Type: application/json');

$ct_path = implode( ',', $current_table );

if( $success )
{
	if( $fastEdit )
	{
		$action = @$page->{'fast-edit'}->{'redirect-after-submit'} ? parse_bracket_instructions( $page->{'fast-edit'}->{'redirect-after-submit'}, $values ) : 'nothing';
		$json = array( 'action-modal' => $action, 'action-page' => $action );
		
		if( @$page->{'fast-edit'}->message->save || @$page->{'fast-edit'}->message->save === false )
		{
			if( $page->{'fast-edit'}->message->save !== false )
			{
				$json['message'] = array(
					'text' => $page->{'fast-edit'}->message->save,
					'type' => 'success' );
			}
		}
		else
		{
			$json['message'] = array(
					'text' => 'Editado com sucesso.',
					'type' => 'success' );
		}
		
		if( @$page->{'fast-edit'}->updates )
		{
			load_lib_file('cms/create_view_object');
			$id = $map_id[$mapName];
			$values['id'] = $id;
			
			$json['updates'] = array();
			$form = array( 'rows' => array( array( 'id' => $id, 'columns' => array() ) ) );
			foreach( @$page->{'fast-edit'}->updates AS $col )
			{
				$column = $request->column( $col );
				$form['rows'][0]['columns'][$col] = $column['field']->listView( $id, @$values );
			}
			$json['updates']['main-table'] = $form;
		}
	}
	else
	{
		$json = array(
			'action-modal' => @$page->edit->{'redirect-after-submit'} ? parse_bracket_instructions( $page->edit->{'redirect-after-submit'}, $values ) : 'nothing',
			'action-page' => @$page->edit->{'redirect-after-submit'} ? parse_bracket_instructions( $page->edit->{'redirect-after-submit'}, $values ) : 'nothing'
			);

		if( @$page->edit->message->save || @$page->edit->message->save === false )
		{
			if( $page->edit->message->save !== false )
			{
				$json['message'] = array(
					'text' => $page->edit->message->save,
					'type' => 'success' );
			}
		}
		else
		{
			$json['message'] = array(
					'text' => 'Editado com sucesso.',
					'type' => 'success' );
		}
	}
}
else
{
	$json = array(
		'action-page' => 'nothing',
		'action-modal' => 'nothing',
		'message' => array(
			'type' => 'error' ),
		'updates' => $invalid_fields );

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
		$json['message']['text'] = 'Por favor, verifique os campos e tente novamente.';
	}
}

echo json_encode( $json );

?>