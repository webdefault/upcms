<?php header('Content-Type: application/json'); ?>
{"status":<?php 
	echo (@$error != NULL) ? 'false, "error_number":'.$error : 'true'; 
?>,"result":<?php 
	echo json_encode( $result ); 
?>}