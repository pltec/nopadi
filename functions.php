<?php
/*
*Arquivo de funções do Nopadi
*Author: Paulo Leonardo Da Silva Cassimiro
*/
use Nopadi\MVC\View;
use Nopadi\Http\URI;
use Nopadi\FS\Json;
use Nopadi\Http\Request;
use Nopadi\Support\Translation;

$GLOBALS['np_instance_of_view'] = new View;
$GLOBALS['np_instance_of_uri'] = new URI;
$GLOBALS['np_instance_of_json'] = new Json('config/app/hello.json');
$GLOBALS['np_instance_of_request'] = new Request;
$GLOBALS['np_instance_of_translater'] = new Translation;


/*Retorna a instancia da classe Request*/
function get_instance(){
	return $GLOBALS['np_instance_of_request'];
}

/*Retonar o id numerico do recurso*/
function get_id(){
	    $uri = $GLOBALS['np_instance_of_uri'];
		$uri = $uri->uri();
		$route = explode('/',$uri);
		$count = count($route) - 1;
		
		if($route[$count] == 'edit' && isset($route[$count -1]))
			return is_numeric($route[$count -1]) ? $route[$count -1] : false;
		else return is_numeric($route[$count]) ? $route[$count] : false;
  }

/*Função para pegar um especifico request da aplicação*/
function get($x,$dafault=null){
	$instance = $GLOBALS['np_instance_of_request'];
	return $instance->get($x,$dafault); 
}

/*Função para pegar todos os requests da aplicação*/
function get_all($except=null){
	$instance = $GLOBALS['np_instance_of_request'];
	return $instance->all($except); 
}

/*Função para verficar se um request existe*/
function has_get($x){
	$instance = $GLOBALS['np_instance_of_request'];
	return $instance->has($x); 
}

/*Função para renderização de arquivos de visualização*/
function view($file,$scope=null){
	$instance = $GLOBALS['np_instance_of_view'];
	$instance->render($file,$scope); 
}

/*Função para verificar se um token crsf de sessão existe*/
function csrf_token(){
	
	if(!isset($_SESSION)) session_start();	
	$csrf = isset($_SESSION['np_csrf_token']) ? $_SESSION['np_csrf_token'] : false;
	return $csrf;
	
}

/*Função para validar token de sessão*/
function csrf_check($token){
	return (csrf_token() == $token) ? true : false;
}

/*Função para verfificar se um  usuário está autenticado*/
function auth($role=null){
   return call_user_func('Nopadi\Http\Auth::check',$role); 
}

/*Retorna a URL da aplicação*/
function url($uri=null){
	$x = $GLOBALS['np_instance_of_uri'];
	$uri = (is_null($uri) || $uri == '/') ? null : $uri;
	return $x->base().$uri;
}

/*Verifica se está na url atual*/
function is_url($route=null){
	$uri = $GLOBALS['np_instance_of_uri'];
    $url = $uri->base();
	$uri = $uri->uri();
	
	
	if(substr($route,0,4) != 'http'){
		
	   $route = str_ireplace('.','/',$route);
       $route = ($route == '/' || is_null($route)) ? $uri : $url.$route;
	   
	   $route = str_ireplace('/{loop}','{loop}',$route);
	   $route = str_ireplace('/','\/',$route);
	   $route = str_ireplace(array('{id}','{int}'),'([0-9]+)',$route);
	   $route = str_ireplace('{string}','([A-Za-zÀ-ú0-9\.\-\_]+)',$route);
	   $route  = str_ireplace('{letter}','([A-Za-z]+)',$route);
	   $route = str_ireplace('{loop}','(\/[A-Za-zÀ-ú0-9\.\-\_]+)*',$route);
	   /*ira aplicar em tudo, menos na api*/
	   $route = str_ireplace('{!api}','([^api]+)',$route);

	
	    if(preg_match("/^{$route}$/i",$uri)) return true; else return false;
	
	}else{
		if($route == $uri) return true; else return false;
	}
	
}

/*Redireciona o usuário para a URL informada*/
function to_url($to=null){
	$base = $GLOBALS['np_instance_of_uri'];
    $base = $base->base();
	$to = ($to == '/') ? null : $to;
	$base = $base.$to;
    header('Location:'.$base);
}

/*Retorna os dados do usuário*/
function user($role=null){
   return call_user_func('Nopadi\Http\Auth::user',$role); 
}

/*Retorna o id do usuário da sessão atual*/
function user_id(){
   return user()->id;
}

/*Retorna o nome da função/tipo  do usuário da sessão atual*/
function user_role(){
   return user()->role;
}

/*Retorna o caminho da pasta public*/
function asset($path=null){
	$uri = $GLOBALS['np_instance_of_uri'];
    return $uri->asset($path); 
}

/*Retorna a transformação um array associativo em options do HTML*/
function options($array=null,$check=null)
{
	   $option = null;
	   foreach($array as $key=>$val){
		   if($key == $check){
			   $option .=  '<option value="'.$key.'" selected>'.$val.'</option>';
		   }else{
		       $option .=  '<option value="'.$key.'">'.$val.'</option>';
		   }
	   }
      return $option;	   
}

/*Retorna a transformação um array associativo em options do HTML*/
function array_one($key,$array)
{
	  $array = array_key_exists($key,$array) ? $array[$key] : $key;
      return $array;	   
}

/*Retorna uma tradução de um item formatado ou de uma variável*/
function text($value=null,$alert=null){
	
	$instance = $GLOBALS['np_instance_of_translater'];
    
	if(substr($value,0,1) == ':'){
		$value = trim(str_ireplace(':','',$value));
		$value = $instance->text($value);
	}else{
	   $value = str_ireplace('!:',':',$value);
	}
	
	if(is_object($value)) $value = get_object_vars($value); 
	if(is_array($value)) $value = implode(', ',$value);
		
	
	if(!is_null($alert)){
		
		$hello = $GLOBALS['np_instance_of_json'];
		$alert = $hello->val('hello',$alert);
		
		$value = '<div class="'.$alert.'">'.$value.'</div>';
	}   
	return $value;
}

/*Similar a text, com a diferença que essa função imprime o valor em tela*/
function hello($value=null,$alert=null){
	echo text($value,$alert);
}

/*Similar a text, com a diferença que a tradução é feito em um array*/
function array_text($array){
	foreach($array as $key=>$val){
		$array[$key] = text($val);
	}
	return $array;
}

/*Carrega os arquivos de css da aplicação que estão configurados no diretório config/app/hello.json*/
function style($only=null){
	
	$instance = $GLOBALS['np_instance_of_json'];
	$uri = $GLOBALS['np_instance_of_uri'];

	
	$style = is_null($only) ? $instance->val('styles') : [$instance->val('styles',$only)];
	
	$css = null;
	
	    if(is_array($style)){
			foreach($style as $key=>$val){
				
			  $val = trim($val);
			  
			  if(substr($val,-4,4) != '.css') $val = $val.'.css';
			  
			  if(substr($val,0,4) != 'http'){
					$css .= '<link rel="stylesheet" href="'.$uri->asset('css/'.$val).'">';
			   }else{
					$css .= '<link rel="stylesheet" href="'.$val.'">';
				   }
			}
		}	
	return $css;
}

/* Função para saída do tipo json */  
function json($value=null){
		if(is_numeric($value) || is_string($value)){
			$value = array($value);
		}
        elseif(is_object($value)){
			$value = get_object_vars($value);
		}
		
		header('Content-Type: application/json;charset=utf-8');
		echo json_encode($value);
    }

/*Carrega os arquivos de js da aplicação que estão configurados no diretório config/app/hello.json*/
function script($only=null){
	$uri = $GLOBALS['np_instance_of_uri'];
	$instance = $GLOBALS['np_instance_of_json'];
	
	$script = is_null($only) ? $instance->val('scripts') : [$instance->val('scripts',$only)];
	
	$js = null;
	
	    if(is_array($script)){
			foreach($script as $key=>$val){
				
			  $val = trim($val);
			  
			  if(substr($val,-3,3) != '.js') $val = $val.'.js';
			  
			  if(substr($val,0,4) != 'http'){
					$js .= '<script src="'.$uri->asset('js/'.$val).'"></script>';
			   }else{
					$js .= '<script src="'.$val.'"></script>';
				   }
			}
		}	
	return $js;
}

/*Inicio das funções para formatação*/

/*Transforma uma string comum no formato URL*/  
function str_url($strTitle,$ignorePonto=true){
	/* Remove pontos e underlines */
    $arrEncontrar = array(".", "_");
    $arrSubstituir = null;
	
	if($ignorePonto == true) $strTitle = str_ireplace($arrEncontrar, $arrSubstituir, $strTitle);
	   
    /* Caracteres minúsculos */
    $strTitle = strtolower($strTitle );
    /* Remove os acentos */
    $acentos = array("á", "Á", "ã", "Ã", "â", "Â", "à", "À", "é", "É", "ê", "Ê", "è", "È", "í", "Í", "ó", "Ó", "õ", "Õ", "ò", "Ò", "ô", "Ô", "ú", "Ú", "ù", "Ù", "û", "Û", "ç", "Ç", "º", "ª");
    $letras = array("a", "A", "a", "A", "a", "A", "a", "A", "e", "E", "e", "E", "e", "E", "i", "I", "o", "O", "o", "O", "o", "O", "o", "O", "u", "U", "u", "U", "u", "U", "c", "C", "o", "a");
    $strTitle = str_ireplace($acentos, $letras, $strTitle);
    $strTitle = preg_replace("/[^a-zA-Z0-9._$, ]/", "", $strTitle);
    $strTitle = iconv("UTF-8", "UTF-8//TRANSLIT", $strTitle);
    /* Remove espaços em branco*/
	$strTitle = strip_tags(trim($strTitle));
    $strTitle = str_ireplace(" ", "-", $strTitle );
	$strTitle = str_ireplace(array("-----", "----", "---", "--"), "-", $strTitle);
    return $strTitle;
  }

function format($string,$format){
	$instance = $GLOBALS['np_instance_of_translater'];
	$format = $instance->val('function.format',$format);
	if($format){
		$format = explode('=',$format);
		$er = "/{$format[0]}/simU";
		$replace = isset($format[1]) ? $format[1] : null;
		$string = preg_replace($er,$replace,$string);
	}
	return $string;
}

/*Função para exibir uma simples lista de menu. Essa função não aceita submenus, mas aceita traduções*/
function list_menu($items,$options=null){
 
 $class = isset($options['class']) ? $options['class'] : 'bar-item button';
 $icon_class = isset($options['icon']) ? $options['icon'] : 'icon';
 $route = isset($options['route']) ? $options['route'] : 'dashboard/';
 $active = isset($options['active']) ? $options['active'] : 'teal animate-left';
 $active = trim($class.' '.$active);

 
 $menu = null;
 if(is_array($items)){
	foreach($items as $item){
		$item = explode('|',$item);
		
		$link = str_ireplace('.','/',$item[0]);
		
		$link = $route.$link;

		$title = (isset($item[1]) && '!' != $item[1]) ? text($item[1]) : null;
		$title_link = $title ? ' title="'.$title.'"' : null;
		$icon = isset($item[2]) ? '<i class="'.$icon_class.'">'.$item[2].'</i>' : null;
		
		if(is_url($link)){
			$link = url($link);
			$menu .= '<a href="'.$link.'" class="'.$active.'"'.$title_link.'>'.$icon.$title.'</a>';
		}else{
			$link = url($link);
			$menu .= '<a href="'.$link.'" class="'.$class.'"'.$title_link.'>'.$icon.$title.'</a>';
		}
		
    }
  }
  return $menu;
}
