<?php
/*
*
*Author: Paulo Leonardo da Silva Cassimiro
*
*/
namespace Nopadi\FS;

use Nopadi\Http\URI;

class ReadArray
{
  private $arr = array();

  /*Ler o caminho do arquivo*/
  public function __construct($file){
	  
	  $uri = new URI();
	  $file = $uri->local($file);
	  
	  if(file_exists($file)){
		  
		 $file = require($file); 

	  if(is_array($file)){
		 
		 $this->arr = $file;
		 
	  }
	    }
  }
  
    /*Faz um include de arquivo*/
  public static function addFile($file){
	  
	  $uri = new URI();
	  $file = $uri->local($file);
	  
	  if(file_exists($file))
		        require($file); 
  }
  
  /*Retona todos os valores*/
  public function gets(){
	return $this->arr;  
  }
  
  /*Mescla um array ao array do nó*/
  public function merge($array)
  { 
     if(is_array($array)){
		$this->arr = array_merge($this->arr, $array);
		return true;
	 }else return false;   
  }
  
  public function mergeFile($file){
	  $file = new ReadArray($file);
	  $this->merge($file->gets());
  }
  
  /*Retorna um valor por meio da chave especifica*/
  public function get($key,$default=null){
	  
	  return isset($this->arr[$key]) ? $this->arr[$key] : $default;
	  
  }
}