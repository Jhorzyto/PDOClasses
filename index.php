<?php
	require_once 'Conexao.class.php';
	require_once 'DeletePDO.class.php';
	require_once 'InsertPDO.class.php';
	require_once 'SelectPDO.class.php';
	require_once 'UpdatePDO.class.php';
	require_once 'Log.class.php';

	// Metodo Inserir

	$inserir = new InsertPDO( 'usuarios' );

	$inserir->adicionarConteudo( 'usuario_nome' , 'Jhordan Lima' );
	$inserir->adicionarConteudo( 'usuario_email' , 'jhorzyto@email.com' );
	$inserir->adicionarConteudo( 'usuario_senha' , sha1(123456) );

	echo ( $inserir->processarPDO() ) ? 'Usuario Cadastrado!' : 'Ocorreu um erro e foi gerado um arquivo com o  erro encontrado para o administrador!' ;


	// Metodo Update

	$atualizar =  new UpdatePDO( 'usuarios' );

	$atualizar->adicionarCondicoes( 'usuario_email' , "=" , 'jhorzyto@email.com' );
	$atualizar->adicionarConteudo( 'usuario_senha' , sha1(654321) );

	echo ( $atualizar->processarPDO() ) ? 'Usuario Atualizado!' : 'Ocorreu um erro e foi gerado um arquivo com o  erro encontrado para o administrador!' ;

	// Metodo Delete

	$apagar = new DeletePDO( 'usuarios' );
	$apagar->adicionarCondicoes( 'usuario_email' , "=" , 'jhorzyto@email.com' );

	echo ( $apagar->processarPDO() ) ? 'Usuario Apagado!' : 'Ocorreu um erro e foi gerado um arquivo com o  erro encontrado para o administrador!' ;

	//Metodo Select

	$consulta =  new SelectPDO( 'usuarios' );
	//$consulta->adicionarCondicoes( 'usuario_email' , "=" , 'jhorzyto@email.com' );
	$consulta->adicionarColunas( 'usuario_nome' );
	$consulta->adicionarColunas( 'usuario_email' );	
	$consulta->adicionarOrdenacoes( 'usuario_nome' );	
	$consulta->adicionarLimites(0,10);

	$resultado = $consulta->processarPDO();

	if ( is_object($resultado) ) {

		foreach ( $resultado->fetchAll(PDO::FETCH_ASSOC) as $chave => $valor) {

			echo "Chave : {$chave} | Nome: {$valor[ 'usuario_nome' ]} | Email: {$valor[ 'usuario_email' ]} \n\n"

		}
		
	} else {

		echo 'Ocorreu um erro e foi gerado um arquivo com o  erro encontrado para o administrador!' ;

	}

?>