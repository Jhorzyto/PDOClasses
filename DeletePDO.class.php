<?php
/** 
* Classe para Delete usando PDO
* * 
* @author Jhordan Lima <jhordan@fontedocodigo.com> 
* @version 1.0 
* @access public 
* @package PDO 
* * 
*/ 
Class DeletePDO{
	/**  
	* Variável usadas para armazenar condições da consulta. Tipo array. 
	* @access private 
	* @name $condicoes
	* *
	* Variável usadas para armazenar operadores lógicos das condições. Tipo array.  
	* @access private 
	* @name $comparadores
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
	private $tabela;
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

			self::$instance = new DeletePDO($tabela); 

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
	* Método para adicionar operador lógico às condições
	* @access public 
	* @param String $comparador 
	* @return void 
	*/ 
	public function adicionarComparador($comparador = "AND"){

		$this->comparadores[] = (in_array($comparador, array("AND","OR","NOT"))) ? $comparador : "AND"; 

	}

	/** 
	* Método para processar a ação
	* @access public 
	* @return boolean 
	*/
	public function processarPDO(){
		try {

			$sql = $this->processarSQL(); //Atribuir valor a variavel local $sql

			$sql = Conexao::getInstance()->prepare($sql); //Preparar consulta

			if(is_array($this->condicoes)){

				foreach ($this->condicoes as $row) {

					if($row['param'] == false) {

						$sql->bindParam(":".$row['coluna'], $row['valor']);
						
					}

				}	

			}	

			$sql->execute(); //executar consulta

			return true; //retornar verdadeiro
			
		} catch (Exception $e) {

			Log::getInstance()->inserirLog("Classe: ".get_class($this)." | Metodo: ".__FUNCTION__." | Erro: Código: " . $e-> getCode() . " | Mensagem: " . $e->getMessage());
			return false; // retornar falso
		}
	}

	/** 
	* Método para gerar o sql
	* @access private 
	* @return string 
	*/ 
	private function processarSQL(){

		$consulta = "DELETE ";
		$consulta .= "FROM ";
		$consulta .= $this->tabela;
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

		return $condicao;
	}

}
?>