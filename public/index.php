<?php
/*
*Arquivo de inicialização da aplicação Nopadi
*Autor: Paulo Leonardo da Silva Cassimiro
*/
use Nopadi\Http\Middleware;
use Nopadi\Routes\RouteRequest;

/*Variável para pegar o caminho raíz da aplicação*/
define('NP_PATH',dirname(__FILE__));

/*Carrega o arquivo de autoload do composer, além dos arquivos de configurações*/
require __DIR__ . '/../bootstrap.php';

/*Carrega os arquivos de rotas de API e WEB*/
require __DIR__ . '/../app/Routes/api.php';
require __DIR__ . '/../app/Routes/web.php';

/*Carrega o arquivo de autoload do composer, além dos arquivos de configurações*/
if(preg_match('/\.(?:png|jpg|jpeg|gif|css|svg|pdf|sass|woff2|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}

/*Inicializa as rotas*/
$x = new RouteRequest();
$x->response();
