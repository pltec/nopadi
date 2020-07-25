<?php
namespace Nopadi\Http;

use Nopadi\Http\URI;

class Request{
	
  private $all;
  
  public function __construct(){
	
	switch($_SERVER['REQUEST_METHOD']){
		
		case 'GET' : 
		     $this->all = isset($_GET) ? $_GET : array();
		break;
		
		case 'POST' : 
		     $this->all = isset($_POST) ? $_POST : array();
		break;
		
		case 'PUT' : 
			 $_PUT = file_get_contents('php://input');
             parse_str($_PUT, $_PUT); 
		     $this->all = isset($_PUT) ? $_PUT : array();
		break;
		
		case 'DELETE' : 
			 $_DELETE = file_get_contents('php://input');
             parse_str($_DELETE, $_DELETE); 
		     $this->all = isset($_DELETE) ? $_DELETE : array();
		break;
		
	    default :  $this->all = array();
	}
	
  }
  
   public function route($position=1)
   {
	    $base = new URI();
		$uri = $base->uri();
		$uri = explode('/',$uri);
		$count = count($uri);
		if(isset($uri[$count - $position])){
			$uri = $uri[$count - $position];
			$uri = explode('?',$uri);
		    return $uri[0];	
		}else return false;	
  }
  
   /*Retorna uma array com todas as chaves*/ 
   public function all($except=null)
   {
	  
	  if(!is_null($except)){
		  $this->except($except);
	  }
	   
	  $all = $this->all;
	  
	  if(array_key_exists('_event',$all)) unset($all['_event']);
	  if(array_key_exists('_token',$all)) unset($all['_token']);
	   
	  return $all;
   } 
  
  /*retorna os eventos enviados no momento do recurso*/  
  public function event(){
	
	return $this->get('_event');
	
  }

  /*Substitui uma chave*/ 
  public function replace($key,$val){
		if(array_key_exists($key,$this->all)){
			 $this->all[$key] = $val; 
             return $this->all[$key];			 
		}else return false;		
 }
  /*Retorna um array e exclui os valores das chaves informadas*/ 
  public function except($x){
	if(is_string($x)) $x = array($x);
	for($i=0;$i<count($x);$i++){
		if(array_key_exists($x[$i],$this->all)){
			unset($this->all[$x[$i]]);   
		}		
    }
	return $this->all;
 }
/*Retorna um array somente com as chaves informadas*/ 
public function only($x){
	$ar = array();
	for($i=0;$i<count($x);$i++){
		if(array_key_exists($x[$i],$this->all)){
			$ar[$x[$i]] = $this->all[$x[$i]];   
		}		
    }
	return $ar;
 }
 /*Retorna um valor de uma chave informada*/ 
public function get($x,$dafault=null){
	 if(array_key_exists($x,$this->all)){
		 return $this->all[$x];
	 }elseif(!is_null($dafault)){
		 return $dafault;
	 }
   }  

  /*define um valor de uma chave informada*/ 
  public function set($x,$y=null)
  {
	 if(array_key_exists($x,$this->all)){
		 $this->all[$x] = $y;
	 }
   } 

  /*Verifica se uma detreminada chave existe e se não está vazia*/ 
  public function has($x)
  {
		if(array_key_exists($x,$this->all)) return true;
        else return false;
  }  
 
 /*Verifica se uma determinada chave existe no array Request*/ 
   public function exists($x)
   {
	$v = 1;
	for($i=0;$i<count($x);$i++){
		if(array_key_exists($x[$i],$this->all)){
				$v *= 1;  
		}else{
			$v *= 0; 
		}		
    }
	if($v) return true;
	else return false;
  } 
  /*Retorna um array com todas as chaves do array do Objeto Request*/ 
  public function keys()
 {
	$ar = array();
	foreach($this->all as $key=>$val){
		$ar[] = $key;
	}
	return $ar;
   }
   
  /*Checa se todas as regras definidas no array são verdadeiras*/
  public function check($x)
  {
	$v = 1;
	foreach($x as $key=>$val){
		if(array_key_exists($key,$this->all) && !empty($this->all[$key])){
			
			$max = isset($val['max']) ? $val['max'] : null;
			$min = isset($val['min']) ? $val['min'] : null;
			$type = isset($val['type']) ? $val['type'] : null;
			$reg = isset($val['reg']) ? $val['reg'] : null;
		
	        $v *= $this->auxType($this->all[$key],$type);
			$v *= $this->auxMinMax($this->all[$key],$min,$max);
			
			if(!is_null($reg)){
				if(!preg_match("/{$reg}/",$this->all[$key])) $v *= 0;
			}
		}else{
			$default = isset($val['default']) ? $val['default'] : null;
			if(!is_null($default)){
				$this->all[$key] = $default;
				$v *= 1;
			}else{
				$v *= 0; 
			}
		}		
    }
	if($v) return true;
	else return false;
   }
   
  public function count()
  {
	return count($this->all);
  }
  
  //Métodos auxiliadores
  private function auxType($x,$type)
  {
	if(!is_null($type)){
	  $type = strtolower($type); 
	  if($type == "text") $type = "string";
	switch($type){
		case 'string' : 
		if(is_string($x)) return 1;
		else return 0;
		break;
		case 'float' : 
		if(is_float($x)) return 1;
		else return 0;
		break;
		case 'int' : 
		if(is_int($x)) return 1;
		else return 0;
		break;
		case 'number' : 
		if(is_numeric($x)) return 1;
		else return 0;
		break;
		case 'email' : 
		if(filter_var($x, FILTER_VALIDATE_EMAIL)) return 1;
		else return 0;
		break;
		case 'date' : 
		$x = explode('-',$x);
		if(count($x) > 1 && count($x) < 4){
			if(checkdate($x[1],$x[2],$x[0])) return 1;
		     else return 0;
		}else return 0;
		break;
	}
	 }else return 1;
  }

 private function auxMinMax($x,$min,$max)
 {
	$v = 1;
	
	if(is_numeric($x)) $x = floatval($x);
	  else $x = strlen($x);

	if($min != null){
		if($x >= $min) $v *= 1;
		else $v *= 0;
	}
	if($max != null){
		if($x <= $max) $v *= 1;
		else $v *= 0;
	}
	return $v;
  }
  
 /*Retorna instancia da classe Request*/
  public static function gets()
  {
	return new Request();
  }
}
