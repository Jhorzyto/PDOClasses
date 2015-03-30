<?php
/** 
* Classe para Select usando PDO
* * 
* @author Jhordan Lima <jhordan@fontedocodigo.com> 
* @version 1.0 
* @access public 
* @package PDO 
* * 
*/ 
Class SelectPDO{
	/**  
	* Variável usadas para armazenar condições da consulta. Tipo array. 
	* @access private 
	* @name $condicoes
	* *
	* Variável usadas para armazenar condições de intervalo de datas da consulta. Tipo array. 
	* @access private 
	* @name $intervaloData
	* *
	* Variável usadas para armazenar operadores lógicos das condições. Tipo array.  
	* @access private 
	* @name $comparadores
	* *
	* Variável usadas para armazenar as colunas para inserir. Tipo array. 
	* @access private 
	* @name $colunas
	* *
	* Variável usadas para armazenar as tabelas relacionadas. Tipo array. 
	* @access private 
	* @name $tabelasRelacionadas
	* *
	* Variável usadas para armazenar a tabela para consulta. Tipo string.
	* @access private 
	* @name $tabela
	* *
	* Variável estática usada para instanciar o objeto. Tipo objeto.
	* @access public 
	* @name $instance
	*/ 
	private $condicoes;
	private $intervaloData;
	private $comparadores;
	private $colunas;
	private $tabelasRelacionadas;
	private $ordecacoes;
	private $limites;
	private $tabela;
	private $dadosRepetidos;
	public static $instance;

	/** 
	* Método construtor
	* @access public 
	* @param String $tabela 
	* @return void 
	*/ 
	function __construct($tabela) {

		$this->tabela = $tabela;

	}

	/** 
	* Método para instanciar o objeto
	* @access public 
	* @param String $tabela 
	* @return objeto 
	*/ 
	public static function getInstance($tabela){ 
		
		if (!isset(self::$instance))

			self::$instance = new SelectPDO($tabela); 

		return self::$instance; 
	} 

	/** 
	* Método para adicionar condições
	* @access public 
	* @param String $coluna 
	* @param String $operador 
	* @param String $valor 
	* @return void 
	*/ 
	public function adicionarCondicoes($coluna, $operador, $valor, $param = false){

		$operador = (in_array($operador, array("=",">",">=","<","<=","<>","LIKE","NOT LIKE"))) ? $operador: "=";
		
		$this->condicoes[] = array("coluna" => $coluna,
			"operador" => $operador,
			"valor" => $valor,
			"param" => $param);
		
	}

	/** 
	* Método para impedir dados repetidos
	* @access public 
	* @param String $coluna 
	* @param String $operador 
	* @param String $valor 
	* @return void 
	*/ 
	public function dadosRepetidos(){
		
		$this->dadosRepetidos = true;
		
	}
	
	/** 
	* Método para adicionar condições por intervalo de datas
	* @access public 
	* @param String $coluna 
	* @param String $dataInicial 
	* @param String $dataFinal 
	* @return void 
	*/ 
	public function adicionarIntervaloData($coluna, $dataInicial, $dataFinal){

		
		$this->intervaloData[] = array("coluna" => $coluna,
			"dataInicial" => $dataInicial,
			"dataFinal" => $dataFinal);
		
	}

	/** 
	* Método para adicionar operador lógico às condições
	* @access public 
	* @param String $comparador 
	* @return void 
	*/ 
	public function adicionarComparador($comparador = "AND"){

		$this->comparadores[] = (in_array($comparador, array("AND","OR","NOT"))) ? $comparador : "AND";

	}
	
	/** 
	* Método para adicionar colunas
	* @access public 
	* @param String $coluna 
	* @return void 
	*/ 
	public function adicionarColunas($coluna = "*"){

		if(!is_array($this->colunas) && $coluna == "*"){

			$this->colunas = $coluna;

		} elseif (!is_array($this->colunas) && is_null($this->colunas)){

			$this->colunas[] = $coluna;

		} elseif ($coluna != "*"){

			if(is_array($this->colunas))
				$this->colunas[] = $coluna;

		} 		

	}

	/** 
	* Método para adicionar tabelas para relacionamento
	* @access public 
	* @param String $tabela 
	* @return void 
	*/ 
	public function adicionarTabelas($tabela, $colunaA = false, $colunaB = false){

		$this->tabelasRelacionadas[] = array('tabela' => $tabela,
											 'colunaA' => $colunaA,
											 'colunaB' => $colunaB);
	}

	/** 
	* Método para adicionar ordenação da consulta
	* @access public 
	* @param String $coluna 
	* @param String $ordenacao 
	* @return void 
	*/ 
	public function adicionarOrdenacoes($coluna, $ordenacao = "ASC"){

		$ordenacao = (in_array($ordenacao, array("ASC","DESC"))) ? $ordenacao : "ASC";

		$this->ordecacoes[] = array('coluna' => $coluna,
			'ordem' => $ordenacao);

	}

	/** 
	* Método para adicionar limite da consulta
	* @access public 
	* @param String $inicial 
	* @param String $final 
	* @return void 
	*/ 
	public function adicionarLimites($inicial, $final){

		$this->limites = array('inicial' => (int)$inicial,
			'final' => (int)$final);

	}

	/** 
	* Método para processar a ação
	* @access public 
	* @return objeto 
	*/
	public function processarPDO(){
		try {

			$sql = $this->processarSQL();

			$sql = Conexao::getInstance()->prepare($sql);

			if(is_array($this->condicoes)){

				foreach ($this->condicoes as $row) {

					if($row['param'] == false) {

						$sql->bindParam(":".$row['coluna'], $row['valor']);
						
					}

				}	

			}		

			if(is_array($this->intervaloData)){

				foreach ($this->intervaloData as $row) {

					$sql->bindParam(":".$row['coluna']."dataInicial", $row['dataInicial']);
					$sql->bindParam(":".$row['coluna']."dataFinal", $row['dataFinal']);

				}	

			}				

			$sql->execute();

			return $sql;
			
		} catch (Exception $e) {

			Log::getInstance()->inserirLog("Classe: ".get_class($this)." | Metodo: ".__FUNCTION__." | Erro: Código: " . $e-> getCode() . " | Mensagem: " . $e->getMessage());
			return false;
		}
	}
	
	/** 
	* Método para gerar o sql
	* @access private 
	* @return string 
	*/ 
	private function processarSQL(){

		$consulta = "SELECT ";
		$consulta .= $this->processarDadosRepetidos();
		$consulta .= $this->processarColunas();
		$consulta .= " FROM ";
		$consulta .= $this->tabela;
		$consulta .= " ";
		$consulta .= $this->processarTabelasJoin();
		$consulta .= $this->processarCondicoes();
		$consulta .= " ";
		$consulta .= $this->processarOrdenacao();
		$consulta .= " ";
		$consulta .= $this->processarLimites();

		//Log::getInstance()->inserirLog($consulta);

		return $consulta;
	}

	/** 
	* Método para verificar dados repetidos
	* @access private 
	* @return string 
	*/ 
	private function processarDadosRepetidos(){
		return ( $this->dadosRepetidos ) ? 'DISTINCT ' : '' ;
	}

	/** 
	* Método para organnizar e estruturar as condições
	* @access private 
	* @return string 
	*/ 
	private function processarCondicoes(){

		$condicao = "";

		if(is_array($this->condicoes)){

			$condicao = " WHERE ";

			$tamanhoVetor = count($this->condicoes);

			if($tamanhoVetor > 2){

				$condicao .= "(";
			}

			$i = 1;

			$j = 0;

			foreach ($this->condicoes as $row) {

				if($tamanhoVetor > 3 && $tamanhoVetor > $i && $i > 2 && $i%2 != 0){

					$condicao .= "(";

				}

				$condicao .= ($row['param'] == true) ? $row['coluna']." ".$row['operador']." ".$row['valor'] : $row['coluna']." ".$row['operador']." :".$row['coluna'];

				if($tamanhoVetor > 2 && $i%2 == 0){

					$condicao .= ")";

				}

				if($i > 0 && !is_array($this->comparadores)){

					$this->comparadores[0] = "AND";
				}

				if($tamanhoVetor > $i){

					$this->comparadores[$j] = (!isset($this->comparadores[$j]))?"AND": $this->comparadores[$j];

					$condicao .= " ".$this->comparadores[$j]." ";

					$j++;

				}

				$i++;

			}
		} 

		if(is_array($this->intervaloData)){

			$tamanhoVetor = count($this->intervaloData);

			$i = 1;

			$j = (isset($j))?$j:0;

			if(!is_array($this->condicoes)){

				$condicao .= " WHERE ";

			} else {

				$condicao .= (!isset($this->comparadores[$j]))?" AND ": $this->comparadores[$j];
				$j++;

			}

			foreach ($this->intervaloData as $row) { 		

				$condicao .= "(".$row['coluna']." BETWEEN ".":".$row['coluna']."dataInicial"." AND ".":".$row['coluna']."dataFinal".")";

				if($tamanhoVetor > $i){

					$condicao .= (!isset($this->comparadores[$j]))?" AND ": $this->comparadores[$j];
					$j++;

				}

				$i++;

			}


		}

		return $condicao;
	}

	/** 
	* Método para organizara as colunas
	* @access private 
	* @return string 
	*/ 
	private function processarColunas(){

		$colunas = "";

		if(is_array($this->colunas)){

			$tamanhoVetor = count($this->colunas);

			$i = 1;

			foreach ($this->colunas as $row) {

				$colunas .= $row;

				$colunas .= ($tamanhoVetor > $i)?", ":"";

				$i++;
			}

		} else {

			$colunas = $this->colunas;

		}

		return $colunas;
	}

	/** 
	* Método para organizara os relacionamentos de tabela
	* @access private 
	* @return string 
	*/ 
	private function processarTabelasJoin(){

		$tabelas = "";

		if(is_array($this->tabelasRelacionadas)){
			
			foreach ($this->tabelasRelacionadas as $row) {

				if($row['colunaA'] && $row['colunaB']){

					$tabelas .= "INNER JOIN ".$row['tabela']." ON ".$row['colunaA']." = ".$row['colunaB']." ";

				} else {

					$tabelas .= "NATURAL JOIN ".$row['tabela']." ";

				}

			}
		}

		return $tabelas;
	}


	/** 
	* Método para organizara as ordenações
	* @access private 
	* @return string 
	*/ 
	private function processarOrdenacao(){

		$ordenacao = "";

		if(is_array($this->ordecacoes)){

			$ordenacao .= "ORDER BY ";
			$tamanhoVetor = count($this->ordecacoes);
			$i = 1;

			foreach ($this->ordecacoes as $row) {
				
				$ordenacao .= $row['coluna']." ".$row['ordem'];
				$ordenacao .= ($tamanhoVetor > $i)?", ":"";
				$i++;

			}

		}

		return $ordenacao;
	}

	/** 
	* Método para organizara os limites
	* @access private 
	* @return string 
	*/ 
	private function processarLimites(){

		$limites = "";

		if(is_array($this->limites)){
			$limites .= "LIMIT ";
			$limites .= $this->limites['inicial'];
			$limites .= ", ";
			$limites .= $this->limites['final'];
		}

		return $limites;
	}

}
?>