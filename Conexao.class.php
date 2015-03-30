<?php
/** 
* Classe de conexão PDO
* * 
* @author Ronaldo Lanhellas <http://www.devmedia.com.br/usando-pdo-php-data-objects-para-aumentar-a-produtividade/28446> 
* @access public 
* @package PDO 
* * 
*/ 
class Conexao { 
	/**  
	* Variável usadas para armazenar o host do servidor mysql. 
	* @access private 
	* @name $host
	* *
	* Variável usadas para armazenar o nome da base de dados.  
	* @access private 
	* @name $dbname
	* *
	* Variável usadas para armazenar o nome do usuário do servidor mysql.
	* @access private 
	* @name $user
	* *
	* Variável usadas para armazenar a senha do usuário do servidor mysql.
	* @access private 
	* @name $password
	* *
	* Variável usadas para armazenar a porta do servidor mysql.
	* @access private 
	* @name $port
	* *
	* Variável estática usada para instanciar o objeto. Tipo objeto.
	* @access public 
	* @name $instance
	*/ 
	const HOST_NOME = "localhost";
	const HOST_USUARIO = "root";
	const HOST_SENHA = "";
	const HOST_PORTA = "3306";
	const DB_NOME = "framework";

	public static $instance;

	/** 
	* Método para instanciar o objeto
	* @access public 
	* @return objeto 
	*/ 

	public static function getInstance() { 

		try {

			self::$instance = new PDO("mysql:host=".self::HOST_NOME.";port=".self::HOST_PORTA.";dbname=".self::DB_NOME, self::HOST_USUARIO, self::HOST_SENHA,  array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 

			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
			self::$instance->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING); 

			return self::$instance;
			
		} catch (Exception $e) {

			Log::getInstance()->inserirLog("Classe: ".get_class($this)." | Metodo: ".__FUNCTION__." | Erro: Código: " . $e-> getCode() . " | Mensagem: " . $e->getMessage());
			Login::getInstance()->logout("error/500");
			
		} 

	} 
} 
?>