<?php

load_lib_file( 'cms/procedures' );
load_lib_file( 'cms/parse_json_mql' );

class MQLProcedure
{
	public static function process( $process, $instance )
	{
		if( $process->operation != 'for-insert' )
		{
			// print_r( $instance->getObject() );
			$nql = parse_json_mql( $process->mql, $instance->getObject() );
		}

		// echo "finish";
		switch( $process->operation )
		{
			case "select":
				// echo json_encode( $nql );exit;
				$temp = $instance->select( $nql );

				if( is_string( $process->save_as ) )
				{
					$instance->addValue( $process->save_as, $temp );
					CMS::addGlobalValue( $process->save_as, $temp );
				}
				else
				{
					$result = array();
					foreach( $process->save_as AS $key => $value )
					{
						$result[$key] = array();

						foreach( $temp AS $line )
						{
							if( is_string( $value ) )
								$result[$key][] = $line[$value];
							else
							{
								$tline = array();
								foreach( $value AS $tkey => $tval )
								{
									$tline[$tkey] = $line[$tval];
								}
								$result[$key][] = $tline;
							}
						}
					}

					foreach( $result AS $key => $lines )
					{
						$instance->addValue( $key, $lines );
						CMS::addGlobalValue( $key, $lines );
					}
				}
				break;

			case "uselect":
				// print_r( $nql );exit;
				$temp = $instance->select( $nql );
				// print_r( $temp );exit;
				$instance->addValue( $process->save_as, @$temp[0] );
				CMS::addGlobalValue( $process->save_as, @$temp[0] );
				break;
				
			case "for-insert":
				$object = $instance->getObject();
				$list = @$object->{$process->each};
				if( is_null( $list ) )
				{
					$list = CMS::globalValue( $process->each );
				}
				
				foreach( $list AS $item )
				{
					$object->each = $item;
					$nql = parse_json_mql( $process->mql, $object );
					$instance->insert( $nql );
				}
				break;

			case "insert":
				$instance->insert( $nql );
				break;
				
			case "if-insert":
				$object = $instance->getObject();
				//print_r( parse_bracket_instructions( $process->rule, $object ) );exit;
				if( parse_bracket_instructions( $process->rule, $object ) )
					$instance->insert( $nql );
				break;
			
			case "if-update":
				$object = $instance->getObject();
				//print_r( $process->rule );print_r( parse_bracket_instructions( $process->rule, $object ) );exit;
				if( parse_bracket_instructions( $process->rule, $object ) )
					$instance->update( $nql );
				break;
				
			case "update":
				$instance->update( $nql );
				break;
				
			case "delete":
				$instance->delete( $nql );
				break;
		}
	}
}


CMSProcedures::addProcedure( 'mql', function( $process, $instance )
{
	MQLProcedure::process( $process, $instance );
} );

?>