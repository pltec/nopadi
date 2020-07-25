<?php
/*
Classe responsável pela compilação dos arquivos de visualizações
*/
namespace Nopadi\MVC;

use Exception;
use Nopadi\FS\Json;
use Nopadi\Http\URI;
use Nopadi\Support\Translation;


class View extends Translation{
  /*caminho dos templates*/
  protected $path = 'app/Views';
  /*Caminho dos caches*/
  protected $cache =  'storage/cache/views';
  /*regra ER para trasnformação*/
  private $all =  "([\[\]\t\n\r\f\v\-A-Za-zÀ-ú0-9\s\{\} &,\_\$\.\"\'\:\(\)\+\-\*\%\/\!\?\>\<\=]+)";
  /*armazena os dados do scope*/
  private $scope;
  /*armazena os comandos de componentização*/
  private $components;

  /*retonar o caminho de raz do site*/
  final public function local($local)
  {
	$config = new URI();
    return $config->local($local);
  }

  /*Método para renderizar o arquivo de visualização*/
  final public function render($view,$np_scope=null)
  {
	
	$this->components = new Json($this->path.'/components.json'); 
	
	/*Faz a extração das variaveis do scope*/
	if(!is_array($np_scope)) $np_scope = ['scope'=>$np_scope];

	extract($np_scope);
	
	$this->scope = $np_scope;
	
	$view = str_ireplace(['.php','.view','.html'],'',$view);
	
	$view = str_ireplace('.','/',$view);
	
    $view_file = $this->local($this->path.'/'.$view.'.php');
	$view_php = $this->local($this->path.'/'.$view.'.view.php');
	$view_html = $this->local($this->path.'/'.$view.'.view.html');
    
	$filename = $view_php;

	if(file_exists($view_file)){
		include($view_file);
	}elseif(file_exists($view_html)){
        $this->create($view_html,$this->scope);
	}elseif(file_exists($view_php)){
        $this->create($view_php,$this->scope);
	}else{
		
		$view1 = explode('/',$view);
		$file = $view1[count($view1) - 1];
		
		$msg = $this->text('view.not.found');
		echo '<span style="font-size:15px;color:white;background-color:red;font-family:verdana,arial;padding:2px"><b>view.not.found</b>|<b>'.$this->path.'/'.$file.'</b>|'.$msg.'</span>';
		
	  } 
   }
 
  /*cria um arquivo de visualização*/
  private function create($mfile,$scope=null)
  {
	if(is_array($scope)) extract($scope,EXTR_PREFIX_SAME,"np");
	
	$view = $mfile;
	
	$com = $this->local($this->cache.'/'.md5($view).'.php');
	
	//gmdate("M d Y H:i:s", mktime(0, 0, 0, 1, 1, 1998));
	$datecom = file_exists($com) ? date('Y.m.d.H.i.s', filemtime($com)) : 'com';
	$dateview = file_exists($view) ? date('Y.m.d.H.i.s', filemtime($view)) : 'view';

    if($datecom == $dateview){
		include($com);
	}else{
		$content = file_get_contents($view);
		
		$content =  $this->transform($content);

		if(file_put_contents($com, $content))
        include($com);
	}
  }

 /*Sintaxe dos arquivos de inclusão*/
  private function dir_tmp($file)
  {
	
  /*Include de templates*/
  $include = "/@include\(({$this->all})\)/simU";
  $file = preg_replace($include,"<?php \$this->render($1,\$this->scope); ?>",$file);
  /*Função para token contra ataque crsf*/
  $crsf = "/\{\{ ?csrf_field\(\) ?\}\}/simU";
  $file = preg_replace($crsf,'<input type="hidden" name="_token" value="{{csrf_token()}}"/>',$file);
  
  $event = "/\{\{ ?event_field\(({$this->all})\) ?\}\}/simU";
  $file = preg_replace($event,'<input type="hidden" name="_event" value=$1/>',$file);

  return $file;  
  }

/*Sintaxe para condicionais*/
private function dir_if($file){
	
	   /*If e Elseif*/
	   $if = "/@if\(({$this->all})\)/simU";
	   $file = preg_replace($if,"<?php if($1): ?>",$file);
	   
	   $elseif = "/@elseif\(({$this->all})\)/simU";
	   $file = preg_replace($elseif,"<?php elseif($2): ?>",$file);
	   
	   /*Else e fechamento do if*/
	   $file = str_ireplace("@else","<?php else: ?>",$file);
	   $file = str_ireplace("@endif","<?php endif; ?>",$file);

	   return $file;
   }

/*Sintaxe para imprimir*/
private function dir_echo($file){
	
	$echo = "/\{{2}{$this->all}\}{2}/imU";
	$echoFilter = "/\{{2}{$this->all}\|{$this->all}\}{2}/imU";
	$echoHTML = "/\{\!{$this->all}\!\}/imU";
	$echoCall = "/\{\?{$this->all}\?\}/imU";

	$file = preg_replace($echo,"<?php echo htmlspecialchars(trim($1), ENT_QUOTES); ?>",$file);

	$file = preg_replace($echoFilter,"<?php echo htmlspecialchars($2(trim($1)), ENT_QUOTES); ?>",$file);
	 
	$file = preg_replace($echoHTML,"<?php echo html_entity_decode(trim($1)); ?>",$file);
	 
	$file = preg_replace($echoCall,"<?php $1; ?>",$file);

	$file = str_ireplace(['{!{','@php','@endphp'],['{{','<?php','?>'],$file);
	 
	return $file;
   }

/*sintaxe para o loop*/
private function dir_for($file){
	
	$in = "/@in\(({$this->all})\)/simU";
	$for = "/@for\(({$this->all})\)/simU";
	$foreach = "/@foreach\(({$this->all})\)/simU";
	
	$file = preg_replace($in,"<?php foreach($1 as \$items): extract(\$items); ?>",$file);
	$file = preg_replace($for,"<?php for($1): ?>",$file);
	$file = preg_replace($foreach,"<?php foreach($1): ?>",$file);
	
	$file = str_ireplace(['@endin','@endfor','@endforeach'],['<?php endforeach; ?>','<?php endfor; ?>','<?php endforeach; ?>'],$file);

	return $file;
	
   }

/*Tradução do arquivo*/
private function dir_lang($file){
	
	$var = "/@var\(({$this->all})\)/simU";
	$file = preg_replace($var,"<?php $$1; ?>",$file);
	
	$lang = "/@use\(({$this->all})\)/simU";
    $file = preg_replace($lang,"<?php \$this->merge($1); ?>",$file);
	
	$lang = "/@import\(({$this->all})\)/simU";
    $file = preg_replace($lang,"<?php \$this->import($1); ?>",$file);
	
    $text = "/#({$this->all})#/simU";
    $file = preg_replace($text,"<?php echo \$this->text('$1'); ?>",$file);
	
	$view = "/@view\(({$this->all})\)/simU";
	$template = "/@template\(({$this->all})\)/simU";
	
    $file = preg_replace($view,"<?php \$this->loadComponent($1); ?>",$file);
	
	$file = preg_replace($template,"<?php \$this->loadComponent($1); ?>",$file);
	
	return $file;
	
   }
   
/*Carrega um componente*/
public function loadComponent($name,$scope=null){
      $component = $this->components->get($name);
	  if($component){
		  $this->render($component,$scope);
	  }
   }

/*Transforma tudo em algo legivel para o PHP*/
public function transform($content){
	    $content = $this->dir_lang($content);
	    $content = $this->dir_tmp($content);
		$content = $this->dir_for($content);
		$content = $this->dir_echo($content);
		$content = $this->dir_if($content);
		return $content;
 }
}
