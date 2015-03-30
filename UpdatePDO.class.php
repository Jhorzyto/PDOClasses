<?php
/** 
* Classe para Update usando PDO
* * 
* @author Jhordan Lima <jhordan@fontedocodigo.com> 
* @version 1.0 
* @access public 
* @package PDO 
* * 
*/ 
Class UpdatePDO{
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
	* Variável usadas para armazenar os valores das colunas. Tipo array. 
	* @access private 
	* @name $valores
	* *
	* Variável usadas para armazenar as colunas para inserir. Tipo array. 
	* @access private 
	* @name $colunas	
	* *
	* Variável usadas para armazenar a tabela para consulta. Tipo string.
	* @access private 
	* @name $tabela
	* *
	* Variável para distiguir o tipo de conteudo. Tipo boolean.
	* @access public 
	* @name $param
	* *
	* Variável estática usada para instanciar o objeto. Tipo objeto.
	* @access public 
	* @name $instance
	*/ 

	private $condicoes;
	private $intervaloData;
	private $comparadores;
	private $valores;
	private $colunas;
	private $tabela;
	private $param;
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

			self::$instance = new UpdatePDO($tabela); 

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
	* Método para adicionar colunas e valores
	* @access public 
	* @param String $coluna 
	* @param String $valor 
	* @return void 
	*/ 
	public function adicionarConteudo($coluna, $valor, $param = false){
		
		$this->colunas[] = $coluna;
		$this->valores[] = $valor;
		$this->param[] = $param;

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

			if(is_array($this->colunas) && is_array($this->valores)){

				$tamanhoVetor = count($this->colunas);

				for ($i=0; $i < $tamanhoVetor; $i++) {

					if(!$this->param[$i]){

						$sql->bindParam(":".$this->colunas[$i], $this->valores[$i]);

					}

				}

			}		

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

			return true;
			
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

		$consulta = "UPDATE ";
		$consulta .= $this->tabela;
		$consulta .= " SET ";
		$consulta .= $this->processarValores();
		$consulta .= " ";
		$consulta .= $this->processarCondicoes();		

		return $consulta;
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
	* Método para organizara as colunas em formma de valores
	* @access private 
	* @return string 
	*/ 
	private function processarValores(){

		$colunas = "";
		
		if(is_array($this->colunas)){

			$i = 1;
			$j = 0;
			$tamanhoVetor = count($this->colunas);

			foreach ($this->colunas as $row) {

				$colunas .= $row;

				$colunas .= " = ";

				$colunas .= ($this->param[$j]) ? $this->valores[$j] : ":".$row ;

				$colunas .= ($tamanhoVetor > $i)?", ":"";

				$i++;
				$j++;
			}

			return $colunas;

		} else {

			return null;
			
		}
	}

}
?>