<?php
/*
*
*Author: Paulo Leonardo da Silva Cassimiro
*
*/
namespace Nopadi\Support;

use Nopadi\FS\Json;

class Translation
{
	private $fileDir = 'storage/langs/'.NP_LANG;
	private $fileName = 'app.json';
	private $instance;
	private $register;
	  
    /*Método para chamar o arquivo de tradução*/
    public function text($key)
    {
	  $text = $this->instance->get($key);
	  $text = $text ? $text : '['.$key.']';
	  return $text;
    }
	
	public function val($key,$index=null,$default=null)
    { 
	  return $this->instance->val($key,$index,$default);	  
    }
	
	/*Retorna todas as chaves da tradução*/
	public function all()
	{
	   return $this->instance->gets();
	}
	
	public function __construct()
	{
	
      $file = $this->fileDir.'/'.$this->fileName;
	  
	  $this->instance = new Json($file);
	 
	}
	
	/*Mescla um arquivo de tradução que esteja no mesmo diretório*/
    public function merge($file)
    { 
      $file = $this->fileDir.'/'.$file.'.json';
	  $this->instance->mergeFile($file);  
    }
	
	/*Mescla ou importa um arquivo de tradução que esteja em um diretório diferente dos arquivos de tradução atual*/
	public function import($file,$local=true)
    { 
	  if($file != 'cache' || $file != 'path')
	       $this->instance->mergeFile($file,$local);  
    }

}