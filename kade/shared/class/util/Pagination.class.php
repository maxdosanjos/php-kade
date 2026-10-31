<?php
/**
 * @author Max dos anjos
 * @since 1.0 - 25/04/2011
 * @desc Classe para Paginação dos registros
 * @update 
 */
	class Pagination
	{
		private $totalPag    	 = null; //total de paginas
		private $startPag    	 = null; //pagina inicial
		private $nextPag     	 = null; //proxima pagina
		private $previousPag 	 = null; //pagina anterior
		private $totalReg	 	 = null; //total de registros
		private $currentPag  	 = null; //pagina corrente
		private $endLimit   	 = null; //final do limite de registro por pagina
		private $startLimit	 	 = null; //registro inicial da pagina 
		
		/**
		 * Construct
		 * @param Int Total de registros
		 */
		public function __construct($totalReg = null)
		{
			$this->setPagCurrent((!empty($_REQUEST['pag'])) ? $_REQUEST['pag']: 1);			
			$this->setEndLimit((!empty($_REQUEST['reg_per_pag'])) ? $_REQUEST['reg_per_pag']: 10);
			// !== e não !=: com 0 resultados (0 == null) o início da página ficava nulo e o SQL saía com "LIMIT ,10"
			if($totalReg !== null)
				$this->loadInfo($totalReg);
		}
		/**
		 * @method void Carrega as informações inicias para a classe
		 */
		public function loadInfo($totalReg)
		{
			$this->setTotalReg($totalReg);
			$this->totalPag    	  = ceil($this->totalReg/$this->getEndLimit());
			// Página pedida além da última (ou zero) volta para um valor válido
			$this->currentPag     = max(1, min($this->currentPag, max(1, $this->totalPag)));
			$this->startPag    	  = 1;
			$this->previousPag 	  = max(1,$this->currentPag-1);
			$this->nextPag 	   	  = min($this->totalPag,$this->currentPag+1);
			$this->startLimit  	  = ($this->currentPag-1)*$this->getEndLimit();
		}
		/**
		 * Getter: totalReg
		 * @return Int 
		 */
		public function getTotalReg()
		{
			return $this->totalReg;
		}
		/**
		 * Setter: totalReg
		 * @param Int
		 * @return void
		 */
		public function setTotalReg($totalReg)
		{
			$this->totalReg = abs(intval($totalReg));
		}
		/**
		 * Getter: startLimit
		 * @return Int
		 */
		public function getStartLimit()
		{
			return $this->startLimit;
		}
		/**
		 * Setter: startLimit
		 * @param Int
		 * @return void
		 */
		public function setStartLimit($startLimit)
		{
			$this->startLimit = abs(intval($startLimit));
		}
		/**
		 * Setter: currentPag
		 * @param Int
		 * @return void
		 */
		public function setPagCurrent($currentPag)
		{
			// Valor da requisição: inteiro a partir de 1 (o limite superior é aplicado no loadInfo)
			$this->currentPag = max(1, intval($currentPag));
		}
		/**
		 * Getter: currentPag
		 * @return Int
		 */
		public function getPagCurrent()
		{
			return $this->currentPag;
		}
		/**
		 * Getter: totalPag
		 * @return Int
		 */
		public function getTotalPag()
		{
			return $this->totalPag;
		}
		/**
		 * Getter : startPag
		 * @return Int
		 */
		public function getStartPag()
		{
			return $this->startPag;
		}
		/**
		 * Getter: nextPag
		 * @return Int
		 */
		public function getNextPag()
		{
			return $this->nextPag;
		}
		/**
		 * Getter: previousPag
		 * @return Int
		 */
		public function getPreviousPag()
		{
			return $this->previousPag;
		}
		/**
		 * Getter: endLimit
		 * @return Int
		 */
		public function getEndLimit()
		{
			return $this->endLimit;
		}
		/**
		 * Setter: endLimit
		 * @param Int
		 * @return void
		 */
		public function setEndLimit($endLimit)
		{
			// Entre 1 e 100 registros por página: o valor vem da requisição
			$endLimit = intval($endLimit);
			$this->endLimit = ($endLimit > 0) ? min(100, $endLimit) : 10;
		}
		/**
		 * @method String Método para exibir a paginação por numeros
		 * @param Int Numero de opções de paginas a serem exibidas
		 */
		public function getPagByNumber($numberLimit)
		{
			$totalPag = $this->totalPag;
			$pag      = $this->currentPag;
			$i        = max($pag-$numberLimit,1);
			$length   = min($pag+$numberLimit,$totalPag);
			
			while($i<=$length)
			{ 
				 if($pag!=$i)
				 {
					 $link = '';
					 echo "<a href='#' style='color:#000;font-weight:bold;padding:5px;margin:1px;text-decoration:none;' class='pag_number' title='Ir para página ".$i."'>".$i."</a>";
				 } 
				 else
					 echo "<span title='P&aacute;gina atual'>".$i."</span>"; 
				$i++; 
			} 
		}
		/**
		 * @method String Exibi detalhes das páginas: total e pagina corrente
		 */
		public function showDetailsPag()
		{
			if($this->totalReg > 0)
			{
				$pag       = $this->getPagCurrent();
				$endLimit = $this->getEndLimit();
				$detail1   = max(1,(($pag-1)*$endLimit)+1);
				$detail2   = min($this->getTotalReg(),($detail1-1) + $endLimit);
				return $detail1." - ".$detail2 ;
			}
			else
				return 0;
		}
		/**
		 * Destruct
		 */
		public function __destruct()
		{
			$this->totalPag    	 = null;
			$this->startPag    	 = null;
			$this->nextPag     	 = null;
			$this->previousPag 	 = null;
			$this->totalReg	 	 = null;
			$this->currentPag  	 = null;
			$this->endLimit   	 = null;
			$this->startLimit 	 = null;
		}
	}
?>