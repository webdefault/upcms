<?php

function sendHTMLEmail( $from, $to, $subject, $data, $view )
{
	if( is_array( $data ) && count( $data ) > 0 ) extract( $data, EXTR_PREFIX_SAME, 'wddx' );
	
	// Load and print the values for the e-mail
	ob_start();	
	require( VIEW_PATH.'email/'.$view.'View.php' );
	$body = ob_get_contents();
	ob_end_clean();

	// Header for HTML
	$boundary = 'XYZ-'.date('dmYis').'-ZYX';
	$headers = '';
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= 'Content-Type: text/html; charset=UTF-8'."\r\n";
	$headers .= 'From: '.$from[0].' <'.$from[1].'>';
	$headers .= "Return-Path: ".$from[1]."\r\n"; // return-path
	// $headers .= "boundary=\"".$boundary."\"\r\n";
	
	// Send the mail
	if( mail( 
		$to, 
		"=?UTF-8?B?".base64_encode($subject)."?=", 
		$body, 
		$headers, 
		"-r".$from[1] ) )
		return true;
	else
		return false;
}

?>