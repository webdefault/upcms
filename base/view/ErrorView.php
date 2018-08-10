<?php

header('Content-Type: application/json');

$json = array(
	'action-page' => 'nothing',
	'action-modal' => 'nothing',
	'message' => array('type'=>$type, 'text'=>$text),
	'error' => true );

if( $debug ) $json['debug'] = $debug;

echo json_encode( $json );

?>