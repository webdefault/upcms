<?php

load_lib_file( 'cms/procedures' );
load_lib_file( 'cms/parse_bracket_instructions' );

CMSProcedures::addProcedure('loadurl', function( $process, $instance )
{
	$object = $instance->getObject();

	$data = array();
	foreach( $process->data AS $key => $value )
		$data[$key] = parse_bracket_instructions( $value, $object );

	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header' => "Content-type: application/x-www-form-urlencoded\r\n",
			'method' => $process->method,
			'content' => http_build_query($data),
		)
	);
	$context  = stream_context_create($options);
	$result = file_get_contents( $process->url, false, $context);

	if( @$process->result_type == 'json' && $result !== FALSE )
		$result = json_decode( $result, true );

	$instance->addValue( $process->save_as, $result );
});

?>