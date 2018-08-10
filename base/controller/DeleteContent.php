<?php

/**
 * @package controller
 * @classe home
 *
 */

// ListContent could be just List, but it's a reserved word
class DeleteContent extends CMS
{
	function __construct( $context )
	{
		parent:: __construct( $context );

		$this->loadModel( 'Delete' );
		// $this->header['pageTitle']  = ' - Home';
	}

	function index( $vars )
	{
		$vars = str_replace( '-', '_', $vars );
		$targetName = array_shift( $vars );
		
		$target = $this->config['map']->get($targetName);
		
		$page = $this->config['pages']->get($targetName);
		$page = @$page->delete;
		
		if( count( $vars ) )
			$removeList = array( $vars[0] );
		else
			$removeList = @$_POST['ids'];
		
		$removedRows = $result = array();
		
		if( @$page->type == 'update' )
		{
			$removedRows = $result = $this->model->delete->updateItems( $target, $targetName, array(), $target->table, $page->fields, $removeList );
		}
		else
		{
			$removedRows = $result = $this->model->delete->deleteItems( $target, $targetName, array(), $target->table, $removeList );
		}
		
		$result = array();
		$result['action-page'] = 'refresh';
		$result['action-modal'] = 'nothing';
		$result['total_removed_rows'] = $removedRows;
		
		$message = $removedRows == 0 ? 'Nenhum item foi removido.' : 
			( $removedRows == 1 ? 'Item removido com sucesso.' : 'Itens removidos com sucesso' );
		
		$result['message'] = array(
			'text' => 'Item removido', //$message,
			'type' => 'warning' );

		$this->loadView( 'JSON', array( 'json' => $result ) );
	}
}

?>