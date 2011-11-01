<?php

class Modules_Pagination {

	/**
	 * Number of icons per page
	 *
	 * @var int
	*/
	public $itemsPerPage 	= 10;

	/**
	 * Total number of available items
	 *
	 * @var int
	 *
	*/
	public $itemsTotal 		= 0;

	/**
	 * Legt fest ob intern mit Pages (0, 1, 2) oder Offset ($num * $itemsPerPage)
	 * gerechnet werden soll.
	 *
	 * @var bool
	*/
	public $usePages 		= FALSE;

	/**
	 * Legt fest, ob die jeweils aktive Seite verlinkt werden soll
	 *
	 * @var bool
	*/
	public $selfLinked		= FALSE;

	/**
	 * Legt fest, ob bei vielen Seiten in der Mitte ebenfalls Seitenverweise
	 * angezeigt werden sollen.
	 *
	 * @var bool
	*/
	public $showMiddle		= FALSE;

	/**
	 * Der Platzhalter bei zu vielen einzelnen Sprunglinks
	 *
	 * @var string
	*/
	public $placeholder		= ' &hellip; ';

	/**
	 * Muster, wie der Link dargestellt werden soll.
	 * Platzhalter: %link%, %pagenum% (jeweilige interne Seitennummer) 
	 * und %label% (sichtbare Seitennummer)
	 *
	 * @var string
	*/
	public $linkPattern		= '<a href="%link%%pagenum%">%label%</a>';

	/**
	 * Seite, auf die Verlinkt werden soll. Der Seitencounter-Parameter wird
	 * diesem Link hinten angehängt
	 *
	 * @var string
	*/
	public $link		 	= '?offset=';

	/**
	 * Trennsymbol zwischen den einzelnen Seiten.
	 *
	 * @var string
	*/
	public $divider			= '  ';

	/**
	 * Seiten die vorne mindestens angezeigt werden (Start | 1 | 2 | 3 | ... | )
	 *
	 * @var int
	*/
	public $atLeast 		= 3;

	/**
	 * Seiten die am Ende mindestens angezeigt werden (| ... | 17 | 18 | 19 | Ende )
	 *
	 * @var int
	*/
	public $atLast 			= 3;

	/**
	 * Anzahl der Seiten um die man bei der aktuellen Seite zurückblättern kann
	 *
	 * @var int
	*/
	public $cropBefore 		= 1;

	/**
	 * Anzahl der Seiten um die man bei der aktuellen Seite vorblättern kann
	 *
	 * @var int
	*/
	public $cropAfter		= 1;

	/**
	 * Beschriftung für "Seite zurück"
	 *
	 * @var string
	*/
	public $labelPrev 		= '‹';

	/**
	 * Beschriftung für "Seite vor"
	 *
	 * @var string
	*/
	public $labelNext 		= '›';

	/**
	 * Beschriftung für "Letzte Seite"
	 *
	 * @var string
	*/
	public $labelEnd 		= '»';

	/**
	 * Beschriftung für "Erste Seite"
	 *
	 * @var string
	*/
	public $labelStart 		= '«';

	/**
	 * Gesamtzahl der vorhandenen Seiten
	 *
	 * @var int
	*/
	private $pageCount		= 0;

	/**
	 * Nummer der aktuell aktiven Seite
	 *
	 * @var int
	*/
	private $currentPageNum	= NULL;

	/**
	 * Faktor mit dem gerechnet werden soll, bei $usePages = true
	 *
	 * @var int
	*/
	private $factor			= 1;

	/**
	 * Der später erzeugte Array mit allen Seitendaten.
	 *
	 * @var array
	*/
	private $pagination		= Array();


	public function __construct() {}

	/**
	 * Setzt die Nummer der aktuell aktiven Seite
	 *
	 * @param int Seite, die übergeben wird und umgerechnet werden soll
	 * @return $this
	*/
	public function currentPageNum($page=0) {

		$this->getPageCount();

		if($this->usePages === TRUE) {
			$this->currentPageNum = (int) $page + 1;
		} else {
			$this->currentPageNum = (int) floor($page / $this->itemsPerPage)+1;
		}

		if($this->currentPageNum > $this->pageCount || $this->currentPageNum <= 0) {
			$this->currentPageNum = 1;
		}

		return $this;

	}

	/**
	 * Legt fest, wieviele Items (=Datensätze) insgesamt existieren
	 *
	 * @param int Gesamtzahl der vorhandenen Datensätze
	 * @return $this
	*/
	public function setItemsTotal($items) {
		$this->itemsTotal = (int) $items;
		return $this;
	}

	/**
	 * Legt fest, wieviele Items pro Seite angezeigt werden sollen
	 *
	 * @param int Anzahl der angezeigten Datensätze
	 * @return $this
	*/
	public function setItemsPerPage($items=10) {
		$this->itemsPerPage = (int) $items;
		return $this;
	}

	/**
	 * Legt den URL fest, der im erzeugten Link benutzt werden soll
	 *
	 * @param int Anzahl der angezeigten Datensätze
	 * @return $this
	*/
	public function setLink($link) {
		$this->link = $link;
		return $this;
	}

	/**
	 * Legt fest ob der Offset in Pages umgerechnet werden soll
	 * Also /?offset=0,5,10,... wird zu /?offset=0,1,2
	 *
	 * @param bool
	 * @return $this
	*/
	public function setUsePages($mode=false) {
		$this->usePages = $mode === false ? false : true;
		return $this;
	}

	public function setDivider($label='|') {
		$this->divider = $label;
		return $this;
	}

	public function setAtLeast($count) {
		$this->atLeast = (int) $count < 0 ? 0 : $count;
		return $this;
	}

	public function setAtLast($count) {
		$this->atLast = (int) $count < 0 ? 0 : $count;
		return $this;
	}

	public function setLabelPrev($label='‹') {
		$this->labelPrev = $label;
		return $this;
	}

	public function setLabelNext($label='›') {
		$this->labelNext = $label;
		return $this;
	}

	public function setLabelStart($label='»') {
		$this->labelStart = $label;
		return $this;
	}

	public function setLabelEnd($label='«') {
		$this->labelEnd = $label;
		return $this;
	}



	/**
	 * Ermittelt die Gesamtzahl der vorhandenen Seiten
	 *
	 * @return int Anzahl der insgesamt vorhandenen Seiten
	*/
	public function getPageCount() {
		$this->pageCount = (int) ceil($this->itemsTotal/$this->itemsPerPage);
		return $this->pageCount;
	}

	/**
	 * Ermittelt die interne Seitennummer je nach gewählter $usePages-Einstellung
	 *
	 * @param int Anzahl der angezeigten Datensätze
	 * @return $this
	*/
	private function getInternalPageNum($offset) {

		$this->getFactor();
		$page = (int) floor( ($offset-1) * $this->factor);
		return $page;

	}

	/**
	 * Umrechnungsfaktor abhängig von $usePages == true/false
	 *
	 * @return $this
	*/
	private function getFactor() {

		if($this->usePages === TRUE) {
			$this->factor = 1;
		} else {
			$this->factor = $this->itemsPerPage;
		}

		return $this;

	}

	/**
	 * Ersetzt strings innerhalb eines Suchmusters mit Daten aus einem Array
	 *
	 * @param array Markers in der Form 'suchen'=>'ersetzen'
	 * @param string Suchmuster, in welchem ersetzt werden soll
	 * @return string Das neue Suchmuster mit den ersetzten Markern
	*/
	private function replace($markers, $pattern) {

		if(is_array($markers)) {
			foreach($markers AS $search => $replace) {
				$pattern = str_replace($search, $replace, $pattern);
			}
		}

		return $pattern;

	}

	/**
	 * Erzeugt den Link, der in der Pagination angezeigt wird
	 *
	 * @param int Versatz bzw. Seitennummer
	 * @param string Alternative Beschreibung (optional)
	 * @param bool Trennsymbol hinzufügen oder nicht (optional)
	 * @return string Der erzeugte Link
	*/
	private function getLink($pageOffset, $altLabel=NULL, $addDivider=true) {

		$label = ($altLabel === NULL) ? (int) $pageOffset : $altLabel;
		#$label = '<span class="pLabel">' . $label . '</span>';
		$divider = ($addDivider === true) ? $this->divider : '';

		if((int) $pageOffset === $this->currentPageNum && $this->selfLinked === false) {
			$link = $label . $divider;
		} else {

			$markers = array(
				'%link%' => $this->link, 
				'%pagenum%' => $this->getInternalPageNum($pageOffset),
				'%label%' => $label
				);
			$link = $this->replace($markers, $this->linkPattern);

			$link .= $divider;

		}

		return $link;

	}

	/**
	 * Hier wird die Pagination erzeugt und ausgegeben.
	 *
	 * @return string Die erzeugte Pagination unter Berücksichtigung aller Parameter
	*/
	public function render() {

		$this->labelNext = '<span class="pLabel pNext">' . $this->labelNext . '</span>';
		$this->labelPrev = '<span class="pLabel pPrev">' . $this->labelPrev . '</span>';
		$this->labelStart = '<span class="pLabel pStart">' . $this->labelStart . '</span>';
		$this->labelEnd = '<span class="pLabel pEnd">' . $this->labelEnd . '</span>';

		$this->getPageCount();

		for($i=1; $i<=$this->pageCount; $i++) {

			$numLabel = '<span class="pLabel">' . $i . '</span>';

			if($this->currentPageNum !== NULL) {

				if($this->atLeast === 0 && $i === 1) {
					$addPlaceholder = true;
				}

				if($this->atLeast >= $i) {

					$addPlaceholder = true;
					$this->pagination[50] .= $this->getLink($i, $numLabel);

				} else if($this->pageCount-$this->atLast < $i) {

					$addPlaceholder = true;
					$this->pagination[50] .= $this->getLink($i, $numLabel);

				} else if( 
							($this->currentPageNum - $this->cropBefore <= $i) && 
							($this->currentPageNum + $this->cropAfter >= $i) 
						) {

					$addPlaceholder = true;
					$this->pagination[50] .= $this->getLink($i, $numLabel);
				} else if (
							$this->showMiddle === true &&
							(
								floor($this->pageCount/2)-1 <= $i && 
								floor($this->pageCount/2)+1 >= $i
							)								
						) {
					$addPlaceholder = true;
					$this->pagination[50] .= $this->getLink($i, $numLabel);
				} else {

					if($addPlaceholder === true) {
						$this->pagination[50] .= $this->placeholder . $this->divider;
					}
					$addPlaceholder = false;

				}
			} else {
				$this->pagination[50] .= $this->getLink($i, $numLabel);
			}

		}

		$this->pagination[0] = $this->getLink(1, $this->labelStart);

		if($this->currentPageNum !== NULL) {
			$this->pagination[25] = ($this->currentPageNum > 1) ? $this->getLink($this->currentPageNum-1, $this->labelPrev) : $this->labelPrev . $this->divider;
			$this->pagination[75] = ($this->currentPageNum < $this->pageCount) ? $this->getLink($this->currentPageNum+1, $this->labelNext) : $this->labelNext . $this->divider;
		}
		$this->pagination[100] = $this->getLink($this->pageCount, $this->labelEnd, 0);

		ksort($this->pagination);
		return join($this->pagination);

	}

}

/*
$pagina = new pagination;
$pagina->link = '?offset1=';
$pagina->itemsPerPage = 10;
$pagina->itemsTotal   = 205;
$pagina->currentPageNum($_GET['offset1']);
echo $pagina->drawPagination();
*/

?>