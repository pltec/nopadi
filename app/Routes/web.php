<?php

use Nopadi\Routes\Route;

/******************************************************
 ******** Nopadi - Desenvolvimento web progressivo*****
 ******** Arquivo de rotas principal (web)*************
*******************************************************/

use Nopadi\MVC\View;
use Nopadi\Http\Auth;
use Nopadi\Http\URI;
use Nopadi\FS\Json;
use Nopadi\Http\Request;
use Nopadi\Base\DB;

Route::get('/',function(){
	
	
	  view('welcome',['title'=>'Página principal']);
	    
	
	   
	   //view('welcome',['posts'=>$x]);
});

/*Rota para página 404*/
Route::get('*',function(){ view('404'); });


/*Docs*/
Route::get('doc',function(){
	  view('doc/all',['title'=>'Documentação']);
});
/*Doc categoria*/
Route::get('doc/{string}',function(){
	  view('doc/cat',['title'=>'Categoria']);
});

/*Doc categoria*/
Route::get('doc/{string}/{string}',function(){
      
	  view('doc/doc',['title'=>'detalhes da documentação']);
});


/*Rotas para controle de sessão do usuário*/
$authenticate = array(
   'login|admin'=>'formLogin',
   'post:login|admin'=>'sendLogin',
   'logout'=>'logout',
   'recover-password'=>'formRecoverPassword',
   'post:recover-password'=>'recoverPassword',
   'post:recover-password-update'=>'passwordUpdate',
   'recover-password/{string}'=>'formRecoverPasswordUpdate'
   );
   
Route::controllers($authenticate,'AuthenticateController');

Route::get('users.teste|api.users.teste','UserController@teste');


Route::group(['prefix'=>'dashboard',
             'middleware'=>['Authenticate']],function(){
/*Rota inicial do dashboard*/
    Route::get('@',function(){
			
      $user =  Auth::user();
      view('dashboard/home',['user'=>$user,'page_title'=>'Painel de controle']);
			
  });
 
/*Rota de usuários*/
Route::resources('users','UserController');

/*Rota de categorias*/
Route::resources('cats','CatController');

/*Rota de posts*/
Route::resources('posts','PostController');


});


 /*Controle de documentos*/
 Route::group(['prefix'=>'doc'],function(){

	Route::get('@','DocumentationController@index');
	Route::get('{string}','DocumentationController@category');
	Route::get('{string}/{string}','DocumentationController@page');
	
	});
