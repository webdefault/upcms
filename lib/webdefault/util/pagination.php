<?php

class Pagination 
{
	private $currentPage;
	private $basepath;
	private $totalItems;
	private $totalPages;
	private $limit = -1;

	private $texts;

	public function __constructor( $texts = NULL )
	{
		if( $texts == NULL )
		{
			load_lib_file( 'webdefault/languages' );
			$texts = l( 'pagination-texts' );
		}
		else
		{
			$this->texts = $texts;
		}
	} 

	public function getSQL()
	{
		$offset = ($this->currentPage - 1) * $this->limit;
		$offset = abs( $offset );

	
		return ($this->limit == -1) ? '' : (' LIMIT '.$this->limit.' OFFSET '.$offset);
	}

	/*
	public function SQL_Query( $PDO = null, $SQL = null)
	{
		$pre = $PDO->prepare(self::SQL_Limit($SQL, false));
		$pre->execute();
		$this->has_registros = $pre->rowCount();
		
		$pre = $PDO->prepare(self::SQL_Limit($SQL, true));
		$pre->execute();
		$this->dba_registros = $pre->fetchAll(PDO::FETCH_OBJ);
		self::setPageRows($this->has_registros);
		
		return $this->dba_registros;
	}
	*/

	/**
	 * 	Configura o paginador
	 */
	public function setBasePath( $basepath )
	{
		//$this->currentPage = max( $this->currentPage, 1);
		//$this->currentPage = min( $this->currentPage, $this->pageRows );

		$this->basepath = $basepath;
	}

	/**
	 * 	Define o limite de registros por página
	 */
	public function setLimit( $n )
	{
		$this->limit = $n;
	}

	/**
	 * 	Define a página atual de navegação
	 */
	public function setCurrentPage( $n )
	{
		$this->currentPage = $n;
	}

	/**
	 * 	Define o total de registros 
	 */
	public function setTotalItems( $n )
	{
		$this->totalItems = $n;
		$n = ceil( $n / $this->limit );
		$this->totalPages = $n;
	}

	/**
	 * 	Exibe o paginador
	 */
	public function getHtml( $id = NULL, $classes = NULL )
	{
		$pag = $this->currentPage;
		$pgs = $this->totalPages;
		$url = $this->basepath;

		if($pag == 0) return '';

		$menu = '<nav'.($id != NULL ? ' id="'.$id.'"':'').($classes != NULL ? ' class="'.$classes.'" ':'').'><ul>';
			
		if($pag != 1)
			$menu.= '<li><a class="first" href="'.$url.'1">'.$this->texts['first'].'</a></li>';
		else
			$menu.= '<li><a class="first nolink">'.$this->texts['first'].'</a></li>';

		if($pag != 1 && $pgs > 0)
			$menu.= '<li><a class="prev" href="'.$url.($pag-1).'">'.$this->texts['prev'].'</a></li>';
		else
			$menu.= '<li><a class="prev nolink">'.$this->texts['prev'].'</a></li>';


		if($pgs == 0)
			$menu.= '<li><a class="selected">1</a></li>';
		else
		{
			for($i=1; $i<=$pgs; $i++)
			{
				if($pag == $i+3) $menu.= '<li><a class="numbers" href="'.$url.$i.'">'.$i.'</a></li>';
				if($pag == $i+2) $menu.= '<li><a class="numbers" href="'.$url.$i.'">'.$i.'</a></li>';
				if($pag == $i+1) $menu.= '<li><a class="numbers" href="'.$url.$i.'">'.$i.'</a></li>';

				if($pag == $i)   $menu.= '<li><a class="numbers selected">'.$i.'</a></li>';

				if($pag == $i-1) $menu.= '<li><a class="numbers" href="'.$url.$i.'">'.$i.'</a></li>';
				if($pag == $i-2) $menu.= '<li><a class="numbers" href="'.$url.$i.'">'.$i.'</a></li>';
				if($pag == $i-3) $menu.= '<li><a class="numbers" href="'.$url.$i.'">'.$i.'</a></li>';
			}
		}

		if($pag < $pgs)  
			$menu.= '<li><a class="next" href="'.$url.($pag+1).'">'.$this->texts['next'].'</a></li>';
		else
			$menu.= '<li><a class="next nolink">'.$this->texts['next'].'</a></li>';

		if($pag != $pgs && $pgs > 0)
			$menu.= '<li><a class="last" href="'.$url.$pgs.'">'.$this->texts['last'].'</a></li>';
		else
			$menu.= '<li><a class="last nolink">'.$this->texts['last'].'</a></li>';

		$menu.= '</ul></nav>';

		return $menu;
	}
}

?>