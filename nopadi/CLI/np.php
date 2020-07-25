#!/usr/bin/php
<?php
$options = isset($argv[1]) ? $argv : false;

$msg = 'Mensagem do Nopadi - Tente novamente e informe um argumento para o comando. www.nopadi.com/doc';

if($options){
	  
	  $func = $options[1];
	  
	  $arg1 = isset($options[2]) ? $options[2] : null;
	  $arg2 = isset($options[3]) ? $options[3] : null;
	  $arg3 = isset($options[4]) ? $options[4] : null;
	  $arg4 = isset($options[5]) ? $options[5] : null;
	  $arg5 = isset($options[6]) ? $options[6] : null;
	  $arg6 = isset($options[7]) ? $options[7] : null;
	  $arg7 = isset($options[8]) ? $options[8] : null;
	  $arg8 = isset($options[9]) ? $options[9] : null;
	  $arg9 = isset($options[10]) ? $options[10] : null;
	  $arg10 = isset($options[11]) ? $options[11] : null;
	  
	  
	  switch($func){
		 case 'create' : 
		 include('create.php'); 
		  create($arg1,$arg2,$arg3); 
		 break;
		 case 'cli' : 
		   if(file_exists('config/cli.php')){

			   include('config/cli.php');
	
		   }else{
			   echo 'Crie um arquivo php com o nome cli dentro da pasta config.';
		   }
		 break;
		 default : echo $msg;
	  }
	  
  }else echo $msg;




?>

