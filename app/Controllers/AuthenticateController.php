<?php 
/*
*Essa é uma classe nativa para autenticação do usuário. Só é permitido alterar as visualizações ligadas a essa classe. 
*/
namespace App\Controllers; 

use Nopadi\Http\URI;
use Nopadi\Http\Auth;
use Nopadi\Http\Send;
use Nopadi\Http\Request;
use Nopadi\MVC\Controller;

class AuthenticateController extends Controller
{
	
	/*Exibe um formulário de login para o usuário entrar na área administrativa*/
	public function formLogin()
	{
	
	  if(!Auth::check())
		  view('authenticate/login',['logout'=>false]);
	  else to_url('dashboard');	 
	
	}
	
	/*Realiza o login do usuário*/
	public function sendLogin()
	{
		
	    Auth::post();
	    echo Auth::status();	 
	}
	
	/*Realiza o logout do usuário*/
	public function logout()
	{
		
	    if(Auth::destroy())
		   view('authenticate/login',['logout'=>true]);
	 
	}
	
	/*Exibe um formulário para gerar token de senha por meio do e-mail fornecido pelo usuário*/
	public function formRecoverPassword()
	{
		
	  view('authenticate/recover-password');
	
	}
	
	/*Gera o token do usuário e o envia para o e-mail correspondente*/
	public function recoverPassword()
	{
		
	  $email = filter_input(INPUT_POST,'email');
	  filter_var($email, FILTER_VALIDATE_EMAIL);
	  
	  if($email){
		  
		  $key = Auth::createTokenByEmail();
		  
		  if($key){
			  
			$name = $key['name'];
			$token = $key['token'];
			
            $link = new URI();
            $link = $link->base();		
			$link_token = $link.'recover-password/'.$token;

			  
		  $send = Send::email([
	              'email'=>$email,
	              'name'=>$name,
	              'title'=>'Recuperar senha',
	              'text'=>'Caso não consiga visualizar o conteúdo desta mensagem, copie e cole a seguinte URL em seu navegador: '.$link_token,
	              'html'=>'<div style="text-align:center;font-family:arial"><h1>Recuperar senha</h1>
				  <h4>Olá <b>'.$name.'</b>! Essa é uma mensagem gerada por meio de um token seguro para recuperação da sua senha.</h4>
				  <a href="'.$link_token.'">Clique aqui para recuperar a sua senha.</a></div>'
	       ]);
		   
		   if($send) hello('Olá <b>'.$name.'</b>! Já enviamos uma mensagem para <b>recuperação da sua senha</b> para o endereço de e-mail informado.','info');
			  
		  }else hello('<b>Sua pesquisa não retornou nenhum resultado.</b> Tente novamente com outro endereço de e-mail.','danger');
		  
	  }else hello('Endereço de e-mail inválido!','danger');
	
	}
	
   /*Exibe um formulário para alteração da senha após validar token do usuário obtido via URL*/
   public function formRecoverPasswordUpdate()
   {
      $token = Request::gets();
      $token = $token->route();
	  
	  $token = Auth::checkToken($token);
	
      view('authenticate/update-password',['token'=>$token]);
	  
   }
   
   /*Altera a senha do usuário*/
   public function passwordUpdate()
   {
  
	  $password = Auth::passwordUpdate();

      if($password) hello('Senha atualizada com sucesso!','success');
	  else hello('Erro ao atualizar senha.','success','danger');
	  
   }
} 
