<?php

load_lib_file( 'webdefault/controllers/simplepage' );
load_lib_file( 'webdefault/util/pagination' );

class GalleryPage extends SimplePage
{
	protected $pagination;

	function __construct( $context )
	{
		parent::__construct( $context );

		$this->pagination = $this->context->pagination = new Pagination();
	}
	
	protected function prepareGridPagination( $currentPage, $totalItens, $method='' )
	{
		// Set the maximum records per page 
		$this->pagination->setLimit( Config::TOTAL_GRID_ITENS );
		$this->pagination->setTotalItems( $totalItens );
		$this->preparePagination( $currentPage, $method );
	}
	
	protected function prepareListPagination( $currentPage, $totalItens, $method='' )
	{
		// Set the maximum records per page 
		$this->pagination->setLimit( Config::TOTAL_LIST_ITENS );
		$this->pagination->setTotalItems( $totalItens );
		$this->preparePagination( $currentPage, $method );
	}
	
	private function preparePagination( $currentPage, $method='' )
	{
		/* PAGINATION */
		//Pagination::$texts['prev'] = 'Anterior';
		//Pagination::$texts['next'] = 'Próximo';
		
		// Sets the current page navigation
		$this->pagination->setCurrentPage( $currentPage );
		
		if( $method == '' )
			$this->pagination->setBasePath( VIEW.'/page/' );
		else
			$this->pagination->setBasePath( VIEW.$method.'/page/' );
	}
}

?>