<?php
namespace Nopadi\Routes;

use Nopadi\Http\URI;
use Nopadi\Routes\RouteCallback;
use Exception;

class RouteRequest extends RouteCollection 
{
	
    private $code = 404;
	
    public function response()
     {
	    /*Verifica qual é o método da solicitação e carrega só as rotas correspondente ao metodo iniciado*/
	    $routes = self::all($_SERVER['REQUEST_METHOD']);
		/*Verifica se a URL atual existe*/
		$base = new URI();
		$url = $base->base();
		$uri = $base->uri();

		if($url != $uri){
			/*varre toda a matriz de rotas*/
			foreach($routes as $route=>$param){
            
			$uri = explode('?',$uri);
			$uri = $uri[0];
			 
			if(preg_match("/^{$route}$/i",$uri)){
				   
				   $callback = $param['callback'];
				   $namespace = $param['namespace'];
	               $params = $param['params'];
				   $middleware = $param['middleware'];
				   $execute = new RouteCallback();
				   
				   $execute->before($middleware);
				   
				   $execute->execute($callback,$params,$namespace);
				   $this->code = 200; 
			   }	
			}	
		}
		
		if($url == $uri){
			if(array_key_exists('np-route-index',$routes)){
				
				   $param = $routes['np-route-index'];
				   $callback = $param['callback'];
				   $namespace = $param['namespace'];
	               $params = $param['params'];
				   
				   $execute = new RouteCallback();
				   
				   $execute->execute($callback,$params,$namespace);
				   
				   $this->code = 200;
			}
		}
	 
	 if($this->code == 404){
	     if(array_key_exists('np-route-404',$routes)){
				
				   $param = $routes['np-route-404'];
				   $callback = $param['callback'];
				   $namespace = $param['namespace'];
	               $params = $param['params'];
				   
				   $execute = new RouteCallback();
				   
				   $execute->execute($callback,$params,$namespace);
				   
				   $this->code = 200;	
	 }
	   }
	 
	 
	 if($this->code == 404)
	   {
		   
		     throw new Exception('Roteamento inexistente');
	 
        }
	}
      }
