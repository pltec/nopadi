<?php
namespace Nopadi\Base;
use Nopadi\Base\DB;

class Schema
{
	private $table_name;
	private $column;
	private $type;
	
	public function __construct($table_name,$type,$column=null){
		$this->table_name = $table_name;
		$this->type = $type;
		$this->column = $column;
	}
	
	public static function create($table_name){
       return new Schema($table_name,'create');
    }
	public static function add($table_name){
       return new Schema($table_name,'add');
    }
	public static function drop($table_name,$column=null){
       return new Schema($table_name,'drop',$column);
    }
	
	public function primary($name='id'){
	   $this->column .= $name.' BIGINT(20) NOT NULL AUTO_INCREMENT, 
	   PRIMARY KEY('.$name.'),';
    }
	
	public function text($name,$size=null,$def=null){
		
       $size = (!is_null($size) && is_int($size)) ? $size : 30;
	   $size = ($size > 535) ? 535 : $size;
	   
	   $column = trim($name.' VARCHAR('.$size.')'.$this->def($def).',');
	   $this->column .= $column;
    }
	
	public function textFixed($name,$size=null,$def=null){
		
       $size = (!is_null($size) && is_int($size)) ? $size : 1;
	   $size = ($size > 255) ? 255 : $size;
	   $column = trim($name.' CHAR('.$size.')'.$this->def($def).',');
	   $this->column .= $column;
    }
	
	public function textLong($name,$def=null){
	   $column = trim($name.' LONGTEXT'.$this->def($def).',');
	   $this->column .= $column;
    }
	
	private function def($def=null){
	   if(!is_null($def)){
		  if(is_bool($def) && $def == true){
			  $def = ' NOT NULL';
		  }else{
			 $def = " NOT NULL  DEFAULT '{$def}'"; 
		  } 
	   }
       return $def;	   
	}

	public function number($name,$size=null,$def=null){
       $size = !is_null($size) ? $size : 250;
	   $column = trim($name.' INT('.$size.')'.$this->def($def).',');
	   $this->column .= $column;
    }
	
	public function money($name,$def=null){
	   $column = trim($name.' FLOAT'.$this->def($def).',');
	   $this->column .= $column;
    }

   public function foreing($name,$table,$ref='id'){
	   $column = trim($name.' BIGINT(20) NOT NULL,
	   FOREIGN KEY('.$name.') REFERENCES '.$table.'('.$ref.'),');
	   $this->column .= $column;
    }
	public function execute(){
	   $schema = null;
	   
	   switch($this->type){
		   case 'create' : 
		   $schema .= 'DROP TABLE IF EXISTS '.$this->table_name.'; ';
           $schema .= 'CREATE TABLE IF NOT EXISTS '.$this->table_name;
	       $schema .= '('.substr($this->column, 0, -1).') ENGINE = innodb;';
		   break;
		   case 'add' : 
		   $schema .= 'ALTER TABLE '.$this->table_name;
	       $schema .= ' ADD '.substr($this->column, 0, -1).';';
		   break;
		   case 'drop' : 
		   if(strlen($this->column) > 2)
		   {
		   $schema .= 'ALTER TABLE '.$this->table_name;
	       $schema .= ' DROP '.$this->column.';';
		   }else $schema .= 'DROP TABLE IF EXISTS '.$this->table_name.';';
		   break;
	   }
	   return $schema;
    }
}


