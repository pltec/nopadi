<?php
/*
*
*Author: Paulo Leonardo da Silva Cassimiro
*
*/

namespace Nopadi\FS;

use Nopadi\Http\URI;

class Json
{
  private $arr = array();
  private $filename;
  
  /*Ler o caminho do arquivo*/
  public function __construct($file,$local=true){
	  

	  if($local){
		  
	      $uri = new URI();
	      $file = $uri->local($file);
	  }
	  
	  $this->filename = $file;

	  $file = @file_get_contents($this->filename);

	  if($file){
		 $file = json_decode($file,true);
         $this->arr = $file;		 
	  } 
  }
  
  /*Retona todos os valores*/
  public function gets()
  {
	return $this->arr;  
  }
  
  /*Retorna um valor por meio da chave especifica*/
  public function get($key)
  {
	  
	  if(isset($this->arr[$key]))
	        return $this->arr[$key];	
		
  }
  
  public function val($key,$index=null,$default=null)
  { 
	  $key = $this->get($key);
      
	  if(is_array($key) && !is_null($index)){
		 $key = array_key_exists($index,$key) ? $key[$index] : $default;
	  }
	  
	  return $key;
	  
   }
  
  /*Retorna um valor por meio da chave especifica*/
  public function read($format=false)
  {
	  
	 if($format){
		return json_encode($this->arr,JSON_PRETTY_PRINT);
	 }else{
		return json_encode($this->arr); 
	 } 
		
  }
  
  /*Adiciona um novo elemento ao nó de dados*/
  public function set($key,$val=null)
  {  
	  $this->arr[$key] = $val; 
  }
  
  /*Mescla um array ao array do nó*/
  public function merge($array)
  { 
     if(is_array($array)){
		$this->arr = array_merge($this->arr, $array);
		return true;
	 }else return false;   
  }
  
  public function mergeFile($file,$local=true){
	  $file = new Json($file,$local);
	  $this->merge($file->gets());
  }
  
  /*Substitui o array atual do nó por outro*/
  public function replace($array)
  { 
      if(is_array($array)){
		 $this->arr = $array; 
		 return true;
	  }else return false;   
  }
  
  /*Elimina um elemento do nó de dados*/
  public function del($key)
  {
	 if(isset($this->arr[$key])){
		  unset($this->arr[$key]);
		  return true;
	 }else return false;
  }
  
  /*Salva o arquivo com as alterações*/
  public function save($format=false)
  {
	  
	 $filename = $this->filename; 
	 /*Se format for true, o json será salvo de forma formatada*/
	 if($format){
		$data = json_encode($this->arr,JSON_PRETTY_PRINT);
	 }else{
		 $data = json_encode($this->arr); 
	 }
	
	 return file_put_contents($filename,$data); 
  }
  
  public function create($filename, $format=false, $sobrepor=false)
  {
	   
	 /*Se format for true, o json será salvo de forma formatada*/
	 if($format){
		$data = json_encode($this->arr,JSON_PRETTY_PRINT);
	 }else{
		 $data = json_encode($this->arr); 
	 }
	if(!file_exists($filename)){
		return file_put_contents($filename,$data);
	}elseif(file_exists($filename) && $sobrepor == true){
		return file_put_contents($filename,$data);
	}else return false;
  }
  
  /*Apaga um arquivo caso ele exista*/
  public function delete($filename)
  {
	if(file_exists($filename)){
		return unlink($filename);
	}else return false;
  }
  
  /*Instancia da classe de forma estática*/
  public static function url($url)
  {
	  $x = new Json($url);
	  return $x;
  }
}