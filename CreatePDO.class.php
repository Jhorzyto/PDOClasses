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
Class CreatePDO{
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
	private $columns;
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
	public function adicionarColunas( $nome , $tipo , $tamanho , $permitirVazio = false  , $autoInclemento = false, $chavePrimaria = false, $valorPadrao = "" ){

		$dataType = array("int", "tinyint", "smallint", "mediumint", "bigint", "float", "double", "decimal", "date", "datetime", "timestamp", "time", "year", "char", "varchar", "text", "blob", "tinytext", "tinyblob" ,"mediumtext", "mediumblob", "longtext", "longblob", "longblob");

		if(in_array ($tipo, $dataType ) ) {

			$this->columns[] = array("nome" => $nome,
			"tipo" => $tipo,
			"tamanho" => $tamanho,
			"valorPadrao" => $valorPadrao,
			"autoInclemento" => $autoInclemento,
			"chavePrimaria" => $chavePrimaria,
			"permitirVazio" => $permitirVazio);
		}
		
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

		$consulta = "CREATE TABLE ";
		$consulta .= $this->tabela;
		$consulta .= " ( ";
		$consulta .= $this->processarColunas();			
		$consulta .= $this->processarPrimaryKey();
		$consulta .= " ) ";
 		$consulta .= "ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;";

 		//Log::getInstance()->inserirLog($consulta);
		return $consulta;
	
	}

	/** 
	* Método para organnizar e estruturar as condições
	* @access private 
	* @return string 
	*/ 
	private function processarColunas(){

		$condicao = "";
		$tamanhoVetor = count($this->columns);
		$i = 1;

		foreach ($this->columns as $value) {

			$value['permitirVazio'] = ( $value['permitirVazio'] ) ? "" : " NOT NULL";
			$value['autoInclemento'] = ( !$value['autoInclemento'] ) ? "" : " AUTO_INCREMENT";

			$value['valorPadrao'] = ( empty( $value['valorPadrao'] ) ) ? "" : " DEFAULT '{$value['valorPadrao']}'" ;
			
			$condicao .= $value['nome']." ".$value['tipo']."(".$value['tamanho'].")".$value['permitirVazio'].$value['autoInclemento'].$value['valorPadrao'];

			$condicao .= ( $i < $tamanhoVetor ) ? ", " : "" ;

			$i++;	

		}	

		return $condicao;
	}

	/** 
	* Método para organnizar e estruturar as condições
	* @access private 
	* @return string 
	*/ 
	private function processarPrimaryKey(){

		$condicao = ",PRIMARY KEY ( ";
		$tamanhoVetor = count($this->columns);
		$i = 0;

		foreach ($this->columns as $value) {
			
			if( $value['chavePrimaria'] ){			

				$condicao .= ( $i > 0 ) ? " , " : "" ;
				$condicao .= $value['nome'];

				$i++;	
			}

		}

		$condicao .= " ) ";	

		return $condicao;
	}


}
?>