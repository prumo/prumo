<?php
/**
 * Este arquivo contém apenas as configurações de conectividade com o banco de dados do framework, outras configurações 
 * estão disponíveis no Painel de Controle do Prumo no menu Sistema.
 *
 * O Prumo Framework pode operar em modo "dbSingle" (um único banco para o framework e para a aplicação), ou com dois
 * bancos de dados distintos, um para o framework e outro para a aplicação.
 *
 * A conexão do framework suporta SQLite3 ou PostgreSQL, enquanto que para a conexão da aplicação está disponível apenas 
 * PostgreSQL.
 *
 * Por padrão o Prumo Framework vem com um banco de dados SQLite3 pronto para operar com o modo dbSingle desativado.
 * É uma boa opção para quem está iniciando, ou simplesmente testando o framework, uma vez que dispensa configuração 
 * inicial de banco de dados. Se esta for a sua escolha, não é necessário alterar este arquivo.
 *
 * Para aplicações em produção, a recomendação é usar PostrgreSQL com modo dbSingle ativado por questões de desempenho e 
 * segurança.
 *
 * A seguir, um exemplo de configuração para o banco do framework em PostgreSQL com modo dbSingle ativado. A estrutura 
 * das tabelas (dump) do banco está disponível na pasta "db" do framework.
 *
 * As configurações de conexão com o banco de dados da aplicação estão disponíveis no painel de controle do framework
 * acessível via navegador.
 */


/* Exemplo de banco de dados do framework em PostgreSQL */
$pConfig['dbSingle']         = true;
$pConfig['sgdb_prumo']       = 'pgsql';
$pConfig['dbHost_prumo']     = 'localhost';
$pConfig['dbPort_prumo']     = '5432';
$pConfig['dbName_prumo']     = 'db_prumo';
$pConfig['dbUserName_prumo'] = 'prumo';
$pConfig['dbPassword_prumo'] = '123456';

// configuração do ambiente do framework (não remover ou comentar este bloco)
if (file_exists('prumo/ctrl_ambient.php')) {
	require_once 'prumo/ctrl_ambient.php';
}
