<?php 
/**
 * Classe para criar arquivos de log.
 * Dev: Jhordan Lima
 * Data: 21/10/2014
 */
class Log{ 

	public static $instance; 

	public static function getInstance(){ 
		if (!isset(self::$instance)) 
			self::$instance = new Log(); 

		return self::$instance; 
	} 

	public function inserirLog($msg){ 

		/////
		
	} 

	
} 
?> 