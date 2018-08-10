<?php 

header('Content-Type: application/json');

$base_url = $order_url_link = 'page://list-content/'.$current_path.$current_table.'/';

$json = array(
	'title' => $page->list->title,
	'toolbar' => array(),
	'optsbar' => array(),
	'container' => array()
	);

if( @$page->list->toolbar )
{
	load_lib_file( 'cms/create_view_object' );
	foreach( $layout->get($mapName, $page->list->toolbar) AS $obj )
	{
		$json['toolbar'][] = create_view_object( $obj, $request, array() );
	}
}

if( @$page->list->type == 'default' )
{
	$json['toolbar']['search'] = array(
		'type' => 'search-form',
		'action' => 'list-content/search/'.$current_table.'/',
		'method' => 'get-slash',
		'subs' => array(
			'value' => array(
				'type' => 'simpletext',
				'placeholder' => 'Buscar' ),
			'submit' => array( 'type' => 'submit', 'icon' => 'search', 'title' => '' )
			)
		);
}

$table = array(
	'id' => 'main-table',
	'type' => 'table',
	'class' => 'table-striped table-bordered table-hover dataTable',
	'target' => $current_table,
	'columns' => array(),
	'rows' => array(),
	
	'group-by' => @$page->list->{'group-by'},
	
	'mode' => @$page->list->type
	);

if( @$options['order'] )
{
	$options['order'][0] = strtolower( $options['order'][0] );
	$options['order'][1] = strtolower( $options['order'][1] );
}
else
{
	$options['order'] = array( 'id', 'asc' );
}

function setOrder( $temp, $column )
{
	$temp['order'][1] = $temp['order'][0] == $column ? ($temp['order'][1] == 'asc' ? 'desc' : 'asc') : 'asc';
	$temp['order'][0] = $column;
	
	// print_r( $temp );
	return $temp;
}

if( isset( $page->list->showid ) && $page->list->showid == true )
{
	$table['show-id'] = array();
	
	if( @$page->list->{'allow-custom-order'} === NULL || $page->list->{'allow-custom-order'} == true )
	{
		$temp = setOrder( $options, 'id' );
		$order_table_icon = $options['order'][0] == 'id' ? 'sorting_'.$temp['order'][1] : 'sorting';
		
		$table['show-id']['url'] = $base_url.CMS::createSlashGet($temp);
		$table['show-id']['icon'] = $order_table_icon;
	}
}

if( $page->list->addable )
	$table['add-btn'] = array( 
		'icon' => 'pencil',
		'url' => @$page->list->{'add-link'} != null ? @$page->list->{'add-link'} : $page->list->{'open-add-as'}.'://add-content/'.$current_table,
		'name' => @$page->list->{'add-name'} ? $page->list->{'add-name'} : "Adicionar"
		);

if( $page->list->removable )	
	$table['remove-btn'] = array(
		'icon' => 'remove',
		'url' => 'delete-content/'.$current_table,
		'method' => 'post-ids',
		'name' => @$page->list->{'remove-name'} ? $page->list->{'remove-name'} : "Remover"
		);

$ths = array();
foreach( $page->list->fields AS $key => $config )
{
	$item = $map->fields->{$key};
	if( !isset( $page->list->fields->{$key}->visible ) || parse_bracket_instructions( $page->list->fields->{$key}->visible, array() ) == 'visible' )
	{
		$th = array( 'key' => $key, 'title' => parse_bracket_instructions( $item->title, array() ), 'size' => @$config->size, 'align' => @$config->align );

		if( @$page->list->{'allow-custom-order'} === NULL || $page->list->{'allow-custom-order'} == true )
		{
			$temp = setOrder( $options, $key );
			$order_table_icon = $options['order'][0] == $key ? 'sorting_'.$temp['order'][1] : 'sorting';
			
			$th['url'] = $base_url.CMS::createSlashGet($temp);
			$th['icon'] = $order_table_icon;
		}
		
		$ths[] = $key;
		$table['columns'][] = $th;
	}
}

$total_columns = count( (array) $page->list->fields );

if( !is_array( $list ) )
{
	CMS::exitWithMessage( 'error', 'Error: Invalid generated list' );
}

foreach( $list as $line )
{
	$row = array(
		'id' => $line['id'], 
		'columns' =>array() 
		);

	if( $page->list->editable === NULL || $page->list->editable == true )
	{
		if( @$page->list->{'line-click'} )
			$row['click'] = parse_bracket_instructions( $page->list->{'line-click'}, $line );
		else
			$row['click'] = $page->list->{'open-edit-as'}.'://edit-content/'.$current_table.'/'.$line['id'];
		
		if( @$page->list->{'class'} )
		{
			$row['class'] = $page->list->{'class'};
		}
	}
	
	foreach( $ths AS $key )
	{
		$config = $page->list->fields->{$key};
		
		$column = $request->column( $key );
		$row['columns'][] = $column['field']->listView( $line['id'], @$line );
	}
	
	if( @$page->list->appends )
	{
		foreach( $page->list->appends AS $key )
		{
			$column = $request->column( $key );
			$row['columns'][] = $column['field']->listView( $line['id'], @$line );
		}
	}
	
	if( @$page->list->{'class'} )
	{
		$column = $request->column( $page->list->{'class'} );
		$lview = $column['field']->listView( $line['id'], @$line );
		$row['class'] = $lview->value;
	}
		
	$table['rows'][] = $row;
}

if( isset( $page->list->search ) && $page->list->search === true )
{
	$formId = 'search-form-'.rand(1000, 9999);
	$json['toolbar']['search'] = array(
		'type' => 'button',
		'title' => '',
		'subtype' => 'toggle-id',
		'icon' => 'search',
		'toggle' => array( 'id' => $formId, 'class' => 'show', 'pressed' => $searching ) );
	
	$form = array(
		'type' => 'form',
		'id' => $formId,
		'class' => 'search-form',
		'subtype' => 'inline',
		'action' => 'list-content/search/'.$current_table,
		'method' => 'get-slash',
		
		'buttons' => array(
			'submit' => array(
				'position' => '#search-form-row',
				'title' => "Filtrar",
				'class' => 'btn-primary '.$mapName.'_search_btn' )
			),
		
		'subs' => array()
	);
	
	$row = array('type' => 'row', 'subs' => array());
	$searchLine = array('id' => 'search-form-row', 'type'=>'column', 'size-md' => 12, 'subs' => array());
	
	load_lib_file('cms/create_view_object');
	
	foreach( $map->search AS $col => $val )
	{
		$obj = new stdClass();
		$obj->type = 'field';
		$obj->target = $col;
		
		$searchLine['subs'][] = create_view_object( $obj, $request->search, $options );
	}
	
	$row['subs'][] = $searchLine;
	$row['subs'][] = array( 'type' => 'horizontal-rule', 'size-md' => 12 );
	
	$form['subs'][] = $row;
	$json['container'][] =  $form;
}

$json['container'][] =  $table;

// Pagination
$pagination = array(
	'type' => 'btn-group',
	'class' => 'pull-right',
	'subs' => array()
	);

$url = 'page://list-content/'.$current_table.'/page/';
$order = ( isset($order[0]) && $order[0] != 'id' && isset($order[1]) )? 'order/'.strtolower($order[0]).','.strtolower($order[1]): '';
print_r( $order );

$total = ceil( $total_list_rows / $limit );
/* // First button
if( $options['page'] != 1 )
	$menu.= '<li><a class="first" href="'.$url.'1">'.$this->texts['first'].'</a></li>';
else
	$menu.= '<li><a class="first nolink">'.$this->texts['first'].'</a></li>';
*/

$temp = $options;
// Prev button
$btn = array( 'icon' => '', 'title' => 'Anterior', 'type' => 'button' );

if( $options['page'] != 1 && $total > 0 )
{
	$temp['page'] = $options['page']-1;
	$btn['url'] = $base_url.CMS::createSlashGet( $temp );
}
else
	$btn['class'] = 'disabled';
$pagination['subs'][] = $btn;

// Numbers buttons
$ttl = $options['page'] + 2;
for( $i = $options['page'] - 2; $i <= $ttl; $i++ )
{
	if( $i >= 1 && $i <= $total )
	{
		$btn = array( 'icon' => '', 'title' => $i, 'type' => 'button' );
		if( $options['page'] != $i )
		{
			$temp['page'] = $i;
			$btn['url'] = $base_url.CMS::createSlashGet( $temp );//$i.'/'.$order;
		}
		else
			$btn['class'] = 'disabled';
		
		$pagination['subs'][] = $btn;
	}
}

// Next button
$btn = array( 'icon' => '', 'title' => 'Próximo', 'type' => 'button' );
if( $options['page'] < $total )
{
	$temp['page'] = $options['page']+1;
	$btn['url'] = $base_url.CMS::createSlashGet( $temp );
}
else
	$btn['class'] = 'disabled';
$pagination['subs'][] = $btn;

$json['optsbar']['pagination'] = $pagination;

echo json_encode( $json );

?>