<?php
namespace  Nopadi\Routes;

use  Nopadi\Http\URI;
use  Nopadi\MVC\View;
use  Nopadi\Routes\RouteCollection;

class Route extends RouteCollection 
{
	
 private static $group;
 private static $args;
 
 private static function setArgs($args=null){
	 /*array de middleware que serão executados*/
	$middleware = isset($args['middleware']) ? $args['middleware'] : [];
	
	/*variaveis que serão inicializadas na montagem da rota:array*/
	$params = isset($args['params']) ? $args['params'] : array();
	
	/*pacote de onde será carregado a classe de controle*/
	$namespace = isset($args['namespace']) ? $args['namespace'] : 'App\Controllers\\';
	
	$prefix = isset($args['prefix']) ? $args['prefix'].'/' : null;

	
	return array(
	'namespace'=>$namespace,
	'middleware'=>$middleware,
	'params'=>$params,
	'prefix'=>$prefix);
 }
 
 public static function create($type,$route,$callback,$args=null){
    $route = str_ireplace('.','/',$route);
    $args = (!is_null(self::$group)) ? self::setArgs(self::$group) : self::setArgs($args);

	$base = new URI();
	$base = $base->base();
	
	$middleware = $args['middleware'];
	$params = $args['params'];
	$namespace = $args['namespace'];
	$prefix = $args['prefix'];
	
	if($route == '@prefix' || $route == '@'){
		$route = substr($prefix,0,-1);
	}else{
		$route = $prefix.$route;
	}
	
	$route = str_ireplace('.','\/',$route);
	if($route == '/'){
		$route = 'np-route-index';
	}elseif($route == '*' || $route == '404'){
		$route = 'np-route-404';
	}else{
		$route = $base.$route;
	} 

	
	
	/*garante que type está dentro do contexto*/
	$type= strtoupper($type);
	$type = (($type == 'POST') || ($type == 'PUT') || ($type == 'DELETE')) ? $type : 'GET';
	$args = array(
	'callback'=>$callback,
	'middleware'=>$middleware,
	'namespace'=>$namespace,
	'params'=>$params);
	
  

	 
	 self::add($type,$route,$args);
	 
 }
public static function group($args,$callback=null){
	 
    self::$group = $args;
	
	if(is_callable($callback)){
		call_user_func($callback);
	}
	
   self::$group = null;
 }
 public static function get($route,$callback,$args=null){
	 self::create('GET',$route,$callback,$args);
 }
 
  public static function post($route,$callback,$args=null){
	self::create('POST',$route,$callback,$args);
 }
 
 public static function put($route,$callback,$args=null){
	 self::create('PUT',$route,$callback,$args);
 }
 
  public static function delete($route,$callback,$args=null){
	self::create('DELETE',$route,$callback,$args);
   }
  public static function any($route,$callback,$args=null){
	 self::get($route,$callback,$args);
	 self::post($route,$callback,$args);
	 self::put($route,$callback,$args);
	 self::delete($route,$callback,$args);
   }
 public static function resources($route,$callback,$args=null){
	 
	self::get($route,$callback.'@index',$args);
	self::get($route.'/{id}',$callback.'@show',$args);
	self::get($route.'/{id}/edit',$callback.'@edit',$args);
	self::get($route.'/create',$callback.'@create',$args);
	self::get($route.'/help',$callback.'@help',$args);
	
	self::post($route,$callback.'@store',$args);
	self::put($route,$callback.'@update',$args);
	self::delete($route,$callback.'@destroy',$args);

   }
public static function redirect($to=null){
	$base = new URI();
	$base = $base->base();
	$to = ($to == '/') ? null : $to;
	$base = $base.$to;
    header('Location:'.$base);
}
/*Cria diversos controles para mesma classe*/
public static function controllers($array,$class,$args=null){
     
     foreach($array as $key=>$controller){
		 
		 $key = explode(':',$key);
		 
		 $method  = (isset($key[1])) ? strtoupper($key[0]) : 'GET';
		 $route  =  (isset($key[1])) ? $key[1] : $key[0];
         $controller = $class.'@'.$controller;
		 
		 switch($method){
			 case 'POST' : self::post($route,$controller,$args); break;
			 case 'PUT' : self::put($route,$controller,$args); break;
			 case 'DELETE' : self::delete($route,$controller,$args); break;
			 case 'ANY' : self::any($route,$controller,$args); break;
			 default : self::get($route,$controller,$args);
		 } 
	 }
  }
}
