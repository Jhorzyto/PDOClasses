<?php
/** 
* Classe para Inserir usando PDO
* * 
* @author Jhordan Lima <jhordan@fontedocodigo.com> 
* @version 1.0 
* @access public 
* @package PDO 
* * 
*/ 
Class InsertPDO{
	/**  
	* Variável usadas para armazenar as colunas para inserir. Tipo array. 
	* @access private 
	* @name $colunas
	* *
	* Variável usadas para armazenar os valores para atribuir as colunas. Tipo array.  
	* @access private 
	* @name $valores
	* *
	* Variável usadas para armazenar a tabela para consulta. Tipo string.
	* @access private 
	* @name $tabela
	* *
	* Variável estática usada para instanciar o objeto. Tipo objeto.
	* @access public 
	* @name $instance
	*/ 
	private $colunas;
	private $valores;
	private $tabela;
	private $ultimoId;
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

			self::$instance = new InsertPDO($tabela); 

		return self::$instance; 
	} 

	/** 
	* Método para adicionar colunas e valores
	* @access public 
	* @param String $coluna 
	* @param String $valor 
	* @return void 
	*/ 
	public function adicionarConteudo($coluna,$valor){

		$this->colunas[] = $coluna;
		$this->valores[] = $valor;
	}

	public function ultimoIdRegistrado(){
		return is_null($this->ultimoId) ? 0 : $this->ultimoId ;
	}

	/** 
	* Método para processar a ação
	* @access public 
	* @return boolean 
	*/
	public function processarPDO(){
		try {

			$sql = $this->processarSQL();

			$sql = Conexao::getInstance()->prepare($sql);

			$tamanhoVetor = count($this->colunas);

			for ($i=0; $i < $tamanhoVetor; $i++) { 

				$sql->bindParam(":".$this->colunas[$i], $this->valores[$i]);

			}			

			$sql->execute();

			$this->ultimoId = Conexao::getInstance()->lastInsertId();

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

		$consulta = "INSERT INTO ";
		$consulta .= $this->tabela;
		$consulta .= " ";
		$consulta .= $this->processarColunas();
		$consulta .= " VALUES ";
		$consulta .= $this->processarValores();

		return $consulta;
	}

	/** 
	* Método para organizara as colunas
	* @access private 
	* @return string 
	*/ 
	private function processarColunas(){
		$colunas = "";
		if(is_array($this->colunas)){

			$colunas .= "(";
			$i = 1;
			$tamanhoVetor = count($this->colunas);

			foreach ($this->colunas as $row) {

				$colunas .= $row;

				$colunas .= ($tamanhoVetor > $i)?", ":"";

				$i++;
			}

			$colunas .= ")";

			return $colunas;

		} else {

			return null;
			
		}
	}

	/** 
	* Método para organizara as colunas em formma de valores
	* @access private 
	* @return string 
	*/ 
	private function processarValores(){
		$colunas = "";
		if(is_array($this->colunas)){

			$colunas .= "(";
			$i = 1;
			$tamanhoVetor = count($this->colunas);

			foreach ($this->colunas as $row) {

				$colunas .= ":".$row;

				$colunas .= ($tamanhoVetor > $i)?", ":"";

				$i++;
			}

			$colunas .= ")";

			return $colunas;

		} else {

			return null;
			
		}
	}
}
?>