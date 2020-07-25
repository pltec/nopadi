<?php

use Nopadi\Routes\Route;

/******************************************************
 ******** Nopadi - Desenvolvimento web progressivo*****
 ******** Arquivo de rotas principal (web)*************
*******************************************************/

Route::get('/',function(){
	view('welcome');
});