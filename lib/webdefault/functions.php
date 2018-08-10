<?php

// Create lcfirst if no exists
if( function_exists( 'lcfirst' ) === false )
{
	function lcfirst( $str )
	{
		$str[0] = strtolower( $str[0] );
		return $str;
	}
}

if( !function_exists('ceiling') )
{
    function ceiling($number, $significance = 1)
    {
        return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
    }
}



// Invocador de classes
/*
function __autoload( $class )
{
	$class = strtolower( $class );
	require_once( $class.'.class.php' );
	if( method_exists( $class, 'init' ) )
		call_user_func( array( $class, 'init' ) );
	return true; 
}
*/

function var_dump_to_str($var)
{
    ob_start();
    var_dump($var);
    return ob_get_clean();
}

// Make example-of-something to ExampleOfSomething
function parse_hyphen_name( $class )
{
	$name = explode( '-', $class );
	foreach( $name as $k => $piece ) $name[$k] = ucfirst( $piece );
	return implode( '', $name );
}

function valida_type_arq ($arq , $type_valido = array ('doc','xls', 'pdf', 'txt')) 
{

	$type_arq = explode (".",$arq);
	if (in_array(end($type_arq), $type_valido)) 
		return true;
	else 
		return false;
}

/***
 * Função para limpar caracteres especiais

 */
function clean_special_chars( $s, $d=false )
{
	if($d) $s = utf8_decode( $s );

	$chars = array(
		'_' => '/`|´|\^|~|¨|ª|º|©|®/',
		'a' => '/à|á|â|ã|ä|å|æ/', 
		'e' => '/è|é|ê|ë/', 
		'i' => '/ì|í|î|ĩ|ï/',	
		'o' => '/ò|ó|ô|õ|ö|ø/', 
		'u' => '/ù|ú|û|ű|ü|ů/', 
		'A' => '/À|Á|Â|Ã|Ä|Å|Æ/', 
		'E' => '/È|É|Ê|Ë/', 
		'I' => '/Ì|Í|Î|Ĩ|Ï/',	
		'O' => '/Ò|Ó|Ô|Õ|Ö|Ø/', 
		'U' => '/Ù|Ú|Û|Ũ|Ü|Ů/',	
		'c' => '/ć|ĉ|ç/', 
		'C' => '/Ć|Ĉ|Ç/', 
		'n' => '/ñ/', 
		'N' => '/Ñ/', 
		'y' => '/ý|ŷ|ÿ/', 
		'Y' => '/Ý|Ŷ|Ÿ/'
	);

	return preg_replace( $chars, array_keys( $chars ), $s );
}

function return_interval_date_day( $datastart , $dataend , $sec = 86400 )
{
	
	$datastart	= strtotime("2012-09-1 00:00");
	$dataend  	= strtotime("now");
	$interval	= ($dataend-$datastart)/$sec; //transformação do timestamp em dias
	
	print $intervalo;
	
}
function reoganizeContactEmail($obj)
{	
	$email_values = array();
	foreach( $obj as $item ):
		$email_values[$item->subject]['value'][$item->id]  	= $item->value;
		$email_values[$item->subject]['name'][$item->id] 	= $item->name;
		$email_values[$item->subject]['branch'][$item->id]  	= $item->branch;
		$email_values[$item->subject]['subject'][$item->id]  	= $item->subject;
		$email_values[$item->subject]['id'][$item->id]  	= $item->id;
	endforeach;
	
	return $email_values;
}

/***
 * Clear special chars and make a slug from that
 *
 */
function slug( $str, $d=false )
{
	$str = clean_special_chars( $str, $d );
	
	$str = strtolower( trim( $str ) );
	$str = preg_replace( '/[^a-z0-9-]/', '-', $str );
	$str = preg_replace( '/-+/', "-", $str );
	return $str;
}

function get_youtube_id( $str )
{
	$vstart = strpos( $str, '?' );
	$start = strpos( $str, 'v=', $vstart ) + 2;
	$end = strpos( $str, '&', $start );
	if( $end === false )
		$temp = substr( $str, $start );
	else
		$temp = substr( $str, $start, $end - $start );
	return $temp;
}

function limitString( $str , $limit = 100, $clear = true , $more = '...' )
{

	if($clear)
		$str = strip_tags($str);

	if(strlen($str) <= $limit )
		return $str;

	$limit_str 			= substr($str, 0, $limit);
	$last_occurrence 	= strrpos( $limit_str , ' ');
	return substr($str, 0, $last_occurrence).$more; 

}
function clearLink($link)
{
	$url = explode( 'www.' , $link );
	return $url[1];
}

function explodeClear( $string , $separator = ';' )
{
		$array = explode( $separator , $string );
		$total = count($array);
		unset($array[$total-1]);
		foreach($array as $key =>$item){
				if($key == $total-2)
						echo $item;
				else
						echo $item.' / ';
		}	
		
}

function zerofill( $num, $zerofill )
{
	while (strlen($num)<$zerofill)
	{
		$num = "0".$num;
	}
	
	return $num;
}

function dataextenso($data) {

		$data = explode("-",$data);

		$dia = $data[2];
		$mes = $data[1];
		$ano = $data[0];

		switch ($mes){

		case 1: $mes = "JANEIRO"; break;
		case 2: $mes = "FEVEREIRO"; break;
		case 3: $mes = "MARÇO"; break;
		case 4: $mes = "ABRIL"; break;
		case 5: $mes = "MAIO"; break;
		case 6: $mes = "JUNHO"; break;
		case 7: $mes = "JULHO"; break;
		case 8: $mes = "AGOSTO"; break;
		case 9: $mes = "SETEMBRO"; break;
		case 10: $mes = "OUTUBRO"; break;
		case 11: $mes = "NOVEMBRO"; break;
		case 12: $mes = "DEZEMBRO"; break;

		}

		$mes = mb_strtolower( $mes, 'UTF-8' );
		print ("$dia de $mes de $ano | ".date('H:j'));
}


function createHash($tamanho = 8, $maiusculas = true, $numeros = true, $simbolos = false)
{
	// Caracteres de cada tipo
	$lmin = 'abcdefghijklmnopqrstuvwxyz';
	$lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$num = '1234567890';
	$simb = '!@#$%*-';

	// Variáveis internas
	$retorno = '';
	$caracteres = '';

	// Agrupamos todos os caracteres que poderão ser utilizados
	$caracteres .= $lmin;

	if ($maiusculas) $caracteres .= $lmai;
	if ($numeros) $caracteres .= $num;
	if ($simbolos) $caracteres .= $simb;

	// Calculamos o total de caracteres possíveis
	$len = strlen($caracteres);

	for ($n = 1; $n <= $tamanho; $n++) 
	{
		// Criamos um número aleatório de 1 até $len para pegar um dos caracteres
		$rand = mt_rand(1, $len);
		// Concatenamos um dos caracteres na variável $retorno
		$retorno .= $caracteres[$rand-1];
	}

	return $retorno;
}

function array_copy($arr)
{
	$newArray = array();

	foreach($arr as $key => $value)
	{
		if(is_array($value)) $newArray[$key] = array_copy($value);
		elseif(is_object($value)) $newArray[$key] = clone $value;
		else $newArray[$key] = $value;
	}
	
	return $newArray;
}

?>