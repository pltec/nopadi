<?php

namespace Nopadi\Http;

use App\Models\UserModel;
use App\Models\TokenModel;

class Auth
    {
	 private static $code;
	 private static $status;
	 
	 /*Verifica se o usuário está logado*/
	 public static function check($role=null)
	 {
	    if(!isset($_SESSION)){ session_start(); } 
		
		$user = isset($_SESSION['np_user_logged']) ? $_SESSION['np_user_logged'] : false;
		
		$token_user = null;
		
		if($user == false){
			$token_user = self::sessionAutomaticByToken();
			if($token_user){
				$user = isset($_SESSION['np_user_logged']) ? $_SESSION['np_user_logged'] : false;
			}
		}
		
		if($token_user){
            $user_id = self::user()->id; 
            $date = date('Y-m-d H:i:s');
		    $token = md5($date.$user_id);	
			TokenModel::model()->update(['token'=>$token],$token_user);
			setcookie('np_token_remember',$token,time()+60*60*24*30);		
		}
		
		if($user){
			if(!is_null($role)){
				$role = is_array($role) ? $role : array($role);
				if(in_array($user['role'],$role)){
					       return true;
				     }else return false;
			    }else return true;
			}else{ return false;}
			
	 }
	 /*Faz login automatico do usuário por meio do token armazenado em seu navegador*/
	 private static function sessionAutomaticByToken(){
		if(isset($_COOKIE['np_token_remember'])){
			$token = $_COOKIE['np_token_remember'];
			$query = TokenModel::model()->find('token',$token);
			if($query){
				
				$id = $query->id;
				$role = $query->role;
				$status = $query->status;
				$user_id = $query->user_id;
				
				if($role == 'remember' && $status == 'active'){
					$user = UserModel::model()->find($user_id);
					if($user){
					    self::loginById($user->id);
						return $id;
					}
				}else return false;
			}else return false;
		}else return false;
	 }
	 
	 /*Retonar um objeto com todos os dados do usuário  caso ele esteja logado*/
	 public static function user($data=null)
	 {
		if(!isset($_SESSION)){ session_start(); } 
		
	   $user = isset($_SESSION['np_user_logged']) ? $_SESSION['np_user_logged'] : false;
		
		if($user){
			$info = array(
			  'id'=>(int)$user['id'],
			  'role'=>$user['role'],
			  'name'=>$user['name'],
			  'email'=>$user['email'],
			  'first'=>null,
			  'last'=>null
			);
		}else{
           $info = array(
			  'id'=>false,
			  'role'=>false,
			  'name'=>false,
			  'email'=>false,
			  'first'=>false,
			  'last'=>false
			);
		}
		 
		if($info['name'] != false){
			$name = explode(' ',$info['name']);
			$first = $name[0];
			$last = $name[count($name) - 1];
			$info['first'] = $first;
			$info['last'] = $last;
		}
		 
		 $info['all'] = $info;
		 
		 if(!is_null($data)){
			 return $info[$data];
		 }else{
			return (object)$info;
		 }
	     
	 }
	 /*Destroi a sessão e o token do usuário*/
	 public static function destroy(){
		 
		if(!isset($_SESSION)) 
			      session_start();
		
		if(isset($_COOKIE['np_token_remember'])){
			
			$remember = $_COOKIE['np_token_remember'];
			
			$id = TokenModel::model()->id('token',$remember);
			
			if($id){
				TokenModel::model()->update(['status'=>'disabled'],$id);
			}
			
			setcookie('np_token_remember', '', time()-3600);
			
		} 
			   
			
        if(session_destroy()) 
			return true;
		else 
			return false;
		
	 }
	  /*Faz o login do usuário pelo verbo post*/
	  public static function post(){
		  $remember = filter_input(INPUT_POST,'remember');
		  $remember = ($remember == 1 || $remember == 'on' || $remember == true || $remember == 'true') ? true : false;
		  $credentials = [
		     'email'=>filter_input(INPUT_POST,'email'),
			 'password'=>filter_input(INPUT_POST,'password'),
			 'remember'=>$remember
			];
		  return self::login($credentials);
	  }
	  /*Faz login manual por meio do ID do usuário*/
	  public static function loginById($id)
	   {	
	    
        $id = isset($id['id']) ? $id['id'] : $id;	
		
		$credentials = array(
		  'email'=>null,
		  'password'=>null,
		  'loginById'=>$id
		);
		
		return self::login($credentials);
	 
	   }
	 /*Faz o login do usuário de forma manual*/
	 public static function login($credentials)
	 {	
	     $email = $credentials['email'];
		 $password = $credentials['password'];
		 $remember = isset($credentials['remember']) ? $credentials['remember'] : false;
		 $session = isset($credentials['session']) ? $credentials['session'] : true;
		 $loginById = isset($credentials['loginById']) ? $credentials['loginById'] : null;
		 
		 /*Se "remember" for diferente de boolean, então, ele será falso*/
		 if(!is_bool($remember)) $remember = false;

		$email = ($loginById == null) ? filter_var($email, FILTER_VALIDATE_EMAIL) : true;
		
		$msg = null;
		
	    if($email){

		$user = ($loginById == null) ? UserModel::model()->find('email',$email) : UserModel::model()->find($loginById);
		   
		    if($user){
				$id = $user->id;
				$role = $user->role;
				$name = $user->name;
				$email = $user->email;
				$hash = $user->password;
				
				$passwordCheck = ($loginById == null) ? password_verify($password,$hash) : true;
				
				if($passwordCheck){
				
				if($loginById == null){
					
					if(self::passwordHashUpdate($hash) === true){
					   $password = self::passwordHash($password);
					   UserModel::model()->update(['password'=>$password],$id);
					}
				}
				
                $response = array(
					  'id'=>$id,
					  'role'=>$role,
					  'name'=>$name,
					  'email'=>$email
				    );
				
				/*inicia uma sesão de usuário logado*/
				if($session){
				   
					if(!isset($_SESSION)) session_start();
					$_SESSION['np_user_logged'] = $response;
					
				          }
				
				  /*Criar um token por meio de um COOKIE para fazer login automatico*/	
				  if($remember){ 
				          self::token($id);
				  }
				  
				  self::$status = 'success';
				  return true;
				  
				}else{ self::$status = 'invalid_password'; return false; }
				//Senha inválida
		    }else{ self::$status = 'user_not_found'; return false; }
			//usuário não localizado
		}else{ self::$status = 'invalid_email';  return false; }
		//E-mail inválido
	}
	public static function status(){
		return self::$status;
	}
	/*Metodo para lembrança para geração de token para lembrança de senha*/
	private static function token($user_id,$role='remember'){
		
		$date = date('Y-m-d H:i:s');
		$token = md5($date.$user_id);
		
		$values = array(
		  'role'=>$role,
		  'status'=>'active',
		  'user_id'=>$user_id,
		  'created_in'=>$date,
		  'token'=>$token
		);
		
		$query = TokenModel::model()->insert($values);
		if($query){
			/*Cria um COOKIE para lembrança da sessão*/
			if($role == 'remember'){
				setcookie('np_token_remember',$token,time()+60*60*24*30);
			}
			return $token;
		}else return false;	
	}
	 /*Cria um novo usuário. Deve ser informado um array indexado com os dados para cadastramento do usuário*/
	 public static function create($user,$minPass=6)
	 {
		 $name = (isset($user['name']) && mb_strlen($user['name']) > 1) ? $user['name'] : false;
		 $password = (isset($user['password']) && mb_strlen($user['password']) >= $minPass) ? $user['password'] : false;
		 $email = (isset($user['email']) && filter_var($user['email'],FILTER_VALIDATE_EMAIL)) ? $user['email'] : false;
		 $role = (isset($user['role'])) ? $user['role'] : 'admin';
	if($name){
	   if($email){
		 if($password){
			 
			 $values = array(
			  'role'=>$role,
			  'name'=>$name,
			  'email'=>$email,
			  'password'=>self::passwordHash($password),
			  'created_in'=>date('Y-m-d H:i:s')
			 );
			 if(!UserModel::model()->have('email',$email)){
				 
				if(UserModel::model()->insert($values)){
					/*Usuário cadastrado com sucesso*/
					self::$status = 'success';
					return true;
				}else{
					/*Erro ao cadastrar usuário no banco de dados*/
					self::$status = 'error_server_db';
					return false;
				} 
			 }else{
				 /*Já existe um usuário com o mesmo endereço de email*/
			     self::$status = 'email_exists';
			     return false;
			 } 
	      }else{
			  /*Senha invalido*/
			  self::$status = 'invalid_password';
			  return false; 
		    }}else{
			  /*E-mail invalido*/
			  self::$status = 'invalid_email';
			  return false; 
		    }}else{
			 /*Nome de usuário invalido*/
			 self::$status = 'invalid_name';
			 return false; 
		 } 
	
	 }
	 /*Gera uma hash da senha*/
	 public static function passwordHash($pass)
	 {
	    return password_hash($pass, PASSWORD_DEFAULT);
	 }
	 /*Gera um hash de senha atualizado*/
	 private static function passwordHashUpdate($pass)
	 {
	    return password_needs_rehash($pass, PASSWORD_DEFAULT);
	 }
	 
	 /*gera um token para recuperação de senha*/
	 public static function createTokenByEmail(){
		 
		$email = filter_input(INPUT_POST,'email');
		$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		
		$user = UserModel::model()->find('email',$email);
	    $token = null;
		
        if($user){
			
			$array = array(
			   ['user_id',$user->id],
			   ['role','recover'],
			   ['status','active']
			   );
			   
			$token = TokenModel::model()->where($array)->get();
			
			$id = $user->id;
			$name = $user->name;

            if(!$token){
				
			
			
			$key = md5(NP_DATETIME.$id);
			$token = array('token'=>$key,'name'=>$name);
			
			$values = array(
			  'user_id'=>$id,
			  'role'=>'recover',
			  'token'=>$key,
			  'status'=>'active',
			  'created_in'=>NP_DATETIME
			);
			
			TokenModel::model()->insert($values);
			}else{

				$token = array('token'=>$token[0]['token'],'name'=>$name);
			}
		}
		
	    return $token;
	 }
	/*Validar token*/
	 public static function checkToken($token){
		 
		 $token = TokenModel::model()->find('token',$token);
		 
		 if($token){
			 
			 $id = $token->id;
			 $user_id = $token->user_id;
			 $role = $token->role;
			 $created_in = $token->created_in;
			 $status = $token->status;
			 
			 if($role == 'recover' && $status == 'active'){
				 
				 TokenModel::model()->update(['status'=>'used'],$id);
				 
				 return $user_id;
				 
			 }else return false;
			 
		 }else return false;
		 
	 }
	/*Altera a senha do usuário*/
	 public static function passwordUpdate(){
		 
		$id = filter_input(INPUT_POST,'id');
		$password = filter_input(INPUT_POST,'password');
		
		$id = filter_var($id, FILTER_SANITIZE_STRING);
	    $password = filter_var($password, FILTER_SANITIZE_STRING);
		
		$password = self::passwordHash($password);
		
		$password = UserModel::model()->update(['password'=>$password],$id);
		
		return $password;
		
	 }
}
