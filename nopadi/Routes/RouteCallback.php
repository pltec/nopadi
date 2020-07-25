<?php
namespace Nopadi\Routes;

use Nopadi\Http\Middleware;
use Nopadi\Support\ServiceProvider;

class RouteCallback
{
    public function before($middleware,$method='handle'){
		 if(is_array($middleware)){
			foreach($middleware as $val){
			
			$params = explode(':',$val);
			$val = $params[0];
			unset($params[0]);
			$params = (!isset($params[1])) ? [0=>null] : $params;
			
            $count = explode('\\',$val);	
			$namespace = (count($count) == 1) ? 'App\Http\Middlewares\\'.$val : $val;
				
			 call_user_func_array(array(new $namespace,$method), array_values($params));
			
		  } 
	   }
	}
	public function execute($callback, array $params = [], $namespace)
	{	
		if(is_callable($callback) && ServiceProvider::execute())
		{
			return call_user_func_array($callback, array_values($params));
			
		} elseif (is_string($callback) && ServiceProvider::execute()) {
			

				$callback = explode('@', $callback);

				$controller = $namespace.$callback[0];
				$method = $callback[1];

				$rc = new \ReflectionClass($controller);

				if($rc->isInstantiable() && $rc->hasMethod($method))
				{
					return call_user_func_array(array(new $controller, $method), array_values($params));
					
				} else {

					throw new \Exception("Nopadi: Erro ao execultar callback: controller não pode ser instanciado, ou método não exite");				
				}
			}else{
				ServiceProvider::message();
			}
	  }
}