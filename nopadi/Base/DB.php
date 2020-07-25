<?php
namespace Nopadi\Base;
use Nopadi\Base\Connection;
use Nopadi\Base\Select;
use Nopadi\Base\Where;
use Nopadi\Http\URI;
use PDO;

class DB extends Connection
{
    private $sql;
	private $select;
    private $where = null;
	private $whereOr = null;
	private $innerJoin = null;
	private $leftJoin = null;
	private $rightJoin = null;
	private $orderBy = null;
	private $groupBy = null;
	private $having = null;
	private $limit = null;
	private $union = null;
	private $status = 0;
	private $table;
	private $driver = 'mysql';
	private $primary = 'id';
	private $total = 0;
	private $connect = null;
	
	public function __construct($table=null){
		$this->table = is_null($table) ? $this->table : $table;	
	}

    /*metodos para transfererir o nome das tabelas*/
	protected function tableName($table){
	    $this->table = $table;
	}
	
	protected function connectName($connect){
	    $this->connect = $connect;
	}
	protected function primaryName($primary){
	    $this->primary = $primary;
	}
	
	public static function table($table=null){
		return new DB($table);
	}
	
    public function select($select='*')
    {
		$drive = new Select($select);
		$this->select = $drive->results();
        return $this;
    }
	
	/*WHERE*/
	public function where($key,$op=null,$val=null,$val2=null)
    {
	  $drive = new Where();
	  $drive->setWhere($key,$op,$val,$val2);
	  $this->where =  $drive->getWhere();
      return $this;
    }

	public function whereOr($key,$op=null,$val=null,$val2=null)
    {
	  $drive = new Where();
	  $drive->setWhere($key,$op,$val,$val2,'OR');
	  $this->whereOr =  $drive->getWhere('OR');
      return $this;
    }
	
	public function join($table,$key1,$key2,$op='=')
    {
	  $drive = new Joins();
	  $drive->setJoin($table,$key1,$key2,$op,'INNER JOIN');
	  $this->innerJoin =  $drive->getJoin();
      return $this;
    }
	public function leftJoin($table,$key1,$key2,$op='=')
    {
	  $drive = new Joins();
	  $drive->setJoin($table,$key1,$key2,$op,'LEFT JOIN');
	  $this->innerJoin =  $drive->getJoin();
      return $this;
    }
	public function rightJoin($table,$key1,$key2,$op='=')
    {
	  $drive = new Joins();
	  $drive->setJoin($table,$key1,$key2,$op,'RIGHT JOIN');
	  $this->innerJoin =  $drive->getJoin();
      return $this;
    }
	
	public function having($select=null)
    {
	  $drive = new Having();
	  $drive->having($select);
	  $this->having =  $drive->getHaving();
      return $this;
    }
	
	public function groupBy($select=null)
    {
	  $drive = new Having();
	  $drive->groupBy($select);
	  $this->having =  $drive->getGroupBy();
      return $this;
    }
	
	public function orderBy($select=null)
    {
	  $drive = new Having();
	  $drive->orderBy($select);
	  $this->orderBy =  $drive->getOrderBy();
      return $this;
    }
     
	 public function limit($l=null,$o=null)
    {
	  $drive = new Having();
	  $drive->limit($l,$o);
	  $this->limit =  $drive->getLimit();
      return $this;
    }
	
	public function union($query)
    {
	  $this->union = 'UNION '.$query;
	  return $this;
    }
	public function find($id,$value=null)
    {
	  if(!is_null($value) && !is_numeric($id)){
		  $value = is_string($value) ? "'{$value}'" : $value;
		  $sql = 'SELECT * FROM '.$this->table.' WHERE '.$id.' = '.$value; 
	  }else{
		 $sql = 'SELECT * FROM '.$this->table.' WHERE '.$this->primary.' = '.$id; 
	  }

	  return $this->query($sql,'OBJ');
	  
    }
	public function all()
    {
	  $sql = 'SELECT * FROM '.$this->table;
	  return  $this->query($sql);
    }
	public function value($key,$value=null)
    {
	  if(is_null($value)){
		  $sql = 'SELECT '.$key.' FROM '.$this->table;
		  $results = $this->query($sql);
	  }else{
		 $sql = "SELECT ".$key." FROM ".$this->table." WHERE ".$key." = '".$value."'"; 
		 $results = $this->query($sql,'OBJ');
	  }
	  return $results;
	}
	/*Retonar o id pelo chave e valor informado*/
	public function id($key,$value)
    {
		$sql = 'SELECT '.$this->primary.' FROM '.$this->table.' WHERE '.$key.' = "'.$value.'"';
		$results = $this->query($sql,'OBJ');
	    if($results){
			$id = $this->primary;
            return intval($results->$id);      
		}else return 0;
    }
	/*Retonar o valor de uma linha pelo ID infomado*/
	public function rowId($key,$id)
    {
		$sql = 'SELECT '.$key.' FROM '.$this->table.' WHERE '.$this->primary.' = '.$id;
		$results = $this->query($sql,'OBJ');
	    if($results){
			    return $results->$key;
		}else return false;
    }
	/*Verfica se existe o registro pela chave e valor infomado*/
	public function have($key,$value,$id=null)
    {
	  $value = is_string($value) ? "'{$value}'" : $value;
	  
	  if(!is_null($id) && is_numeric($id)){
		 $sql = "SELECT ".$key." FROM ".$this->table." WHERE ".$key." = ".$value." AND ".$this->primary." != ".$id; 
	  }else{
		$sql = "SELECT ".$key." FROM ".$this->table." WHERE ".$key." = ".$value;  
	  }

	  $result =  $this->query($sql,'ASSOC');
	  if($result) return true;
	  else return false;
    } 
	
	/*Verfica se existe o registro pela chave e valor infomado*/
	public function exists($values,$id=null)
    {
	  
	  if(is_array($values) && count($values) >= 1){
		  $where = null;
		  
		  foreach($values as $key=>$val){
			 $val = is_string($val) ? '\''.$val.'\'' : $val;
			 if(!is_null($val)) $where .= $key.' = '.$val.' AND ';
		  }
		  
		  $where = trim(substr($where, 0, -4));
		  
		  $where = (!is_null($id) && is_numeric($id)) ? $where.' AND '.$this->primary.' != '.$id : $where;
		  
		  $where = trim('SELECT '.$this->primary.' FROM '.$this->table.' WHERE '.$where);
		 
		  $where = $this->query($where);
		  
		  if($where){
            return intval($where[0][$this->primary]);
		  }else return false;
	  }else return false;
    }
	
	/*faz a união total de duas consultas*/
	public function unionAll($query)
    {
	  $this->union = 'UNION ALL '.$query;
	  return $this;
    }
	
	public function __toString()
    {
        return $this->mounted();
    }
	private function mounted()
    {
		$table = $this->table;

		$select = is_null($this->select) ? '*' : $this->select;
		
		$join = $this->innerJoin;
		$left = $this->leftJoin;
		$right = $this->rightJoin;
		
		$where = $this->where;
		$whereOr = $this->whereOr;
		
		$orderBy = $this->orderBy;
		
		$groupBy = $this->groupBy;
		
		$limit = $this->limit;
		
		$having = $this->having;

        
		if(!is_null($where)) $where = ' WHERE '.$where;
		if(is_null($where) && !is_null($whereOr)){
			$whereOr = (is_null($where)) ? ' WHERE '.$whereOr : null;
		}elseif(!is_null($where) && !is_null($whereOr)){
			$whereOr = ' OR '.$whereOr;
		}

		$sql = 'SELECT '.$select.' FROM '.$table.' '.$join.$left.$right.$where.$whereOr.$groupBy.$having.$orderBy.$limit;
		
		$this->sql = trim($sql.$this->union);
		
        return $this->sql;
    }
	public function get(){
		return $this->query($this->mounted());
	}
	public static function connect(){
		return self::getConn(null);
	}
   /*metodo para executar a query*/
    public function query($sql, $re = null) {
		
        $d = self::getConn($this->connect);
		
        $query = $d->prepare($sql);
        $re = is_null($re) ? "ASSOC-ALL" : strtoupper($re);
        $rows = null;
        if ($query->execute()) {
            switch ($re) {
                case "ASSOC" : $rows = $query->fetch(PDO::FETCH_ASSOC);
                    break;
                case "ASSOC-ALL" : $rows = $query->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case "OBJ" : $rows = $query->fetch(PDO::FETCH_OBJ);
                    break;
                case "OBJ-ALL" : $rows = $query->fetchAll(PDO::FETCH_OBJ);
                    break;
                case "NUM" : $rows = $query->fetchAll(PDO::FETCH_NUM);
                    break;
                case "BOTH" : $rows = $query->fetchAll(PDO::FETCH_BOTH);
                    break;
                default : $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            }
            return $rows;
        } else {
            return false;
        }
    }
	public function execute($sql) {
        $self = self::getConn($this->connect);
        if ($self->exec($sql)) {
            return true;
        }else {
              return false;
        }
    }
  /*Metodo para paginação*/
  public function paginate($total=10,$btns=false){

   $page = isset($_GET['page']) ? $_GET['page'] : 1;
   
   if(empty($page) || !is_numeric($page)){
	   $pc = 1;
   }else{ $pc = $page; } 
	 
	$inicio = $pc - 1;
	$inicio = $inicio * $total;
	 
	$this->limit($inicio,$total);
	$query = $this->mounted();
	
	/*Contagem*/
	$this->total = $this->count();
	
	/*Total de registros por páginas*/
	$tp = ceil($this->total / $total); 
	$previous = $pc -1;
    $next =  $pc +1;
	 
	$base = new URI();
	$uri = $base->uri();
	$uri = explode('?',$uri);
	$uri = $uri[0];
	
	/*numero de botões*/
	
	if(is_bool($btns) && $btns == true){
	$btns = array();
    if($tp > 1){
        for($i=1; $i<=$tp;$i++){
	      if($i == $pc) $btns[$i] = $uri.'?page='.$i;
          else $btns[$i] = $uri.'?page='.$i;
       }  
     }
	}	
	$previous = ($pc>1) ? $uri.'?page='.$previous : null;
	$next = ($pc<$tp) ? $uri.'?page='.$next : null;

    $results = $this->query($query);
	 
	$results = array(
	'count'=>(int)$this->total,
	'page'=>(int)$pc,
	'total'=>(int)$tp,
	'next'=>$next,
	'numbers'=>$btns,
	'previous'=>$previous,
	'results'=>$results
	);
	
	return (object) $results;
	
  }
  //Contagem de registros
  public function count($key='*'){
	$count = $this->mounted();
	$count = explode('FROM',$count);
	$count = 'SELECT COUNT('.$key.') AS total FROM'.$count[1];
	$count = explode('LIMIT',$count);
	$count = trim($count[0]);
	$count = $this->query($count,'OBJ');
	return (int)$count->total;
  }
  //Soma
public  function sum($key){
	$count = $this->mounted();
	$count = explode('FROM',$count);
	$count = 'SELECT SUM('.$key.') AS total FROM'.$count[1];
	$count = explode('LIMIT',$count);
	$count = trim($count[0]);
	$count = $this->query($count,'OBJ');
	return (float)$count->total;
 }
//Média
public function avg($key){
	$count = $this->mounted();
	$count = explode('FROM',$count);
	$count = 'SELECT AVG('.$key.') AS total FROM'.$count[1];
	$count = explode('LIMIT',$count);
	$count = trim($count[0]);
	$count = $this->query($count,'OBJ');
	return (float)$count->total;
 }
//Máximo
public function max($key){
	$count = $this->mounted();
	$count = explode('FROM',$count);
	$count = 'SELECT MAX('.$key.') AS total FROM'.$count[1];
	$count = explode('LIMIT',$count);
	$count = trim($count[0]);
	$count = $this->query($count,'OBJ');
	return (float)$count->total;
 }
//Minimo
public function min($key){
	$count = $this->mounted();
	$count = explode('FROM',$count);
	$count = 'SELECT MIN('.$key.') AS total FROM'.$count[1];
	$count = explode('LIMIT',$count);
	$count = trim($count[0]);
	$count = $this->query($count,'OBJ');
	return (float)$count->total;
 }
 /*Metodo para inserção de dados*/
  public function insert($values,$id=false) {
        $table = $this->table;
        //Sepera os indices pela chave e valor
        foreach ($values as $key => $val) {
            $k[] = htmlspecialchars($key, ENT_QUOTES);
			$val = is_string($val) ? "'" . htmlspecialchars($val, ENT_QUOTES) . "'" : $val;
			if(is_null($val)) $val = "'".$val."'";
            $v[] = $val;
        }
        $k = implode(", ", $k);
        $v = implode(", ", $v);
        //Monta a query
        $sql = "INSERT INTO {$table} ({$k}) VALUES ({$v})";
        //Retornar V ou F 
        if ($this->execute($sql)){
			if($id) return $this->max($this->primary);
			else return true;
		}else return false;
    }
  /*Metodo para deletar*/
    public function delete($id = null) {
        $table = $this->table;
        //Monta a condição de atualização da tabela
        if ($id == null) {
            $w = null;
        } elseif (is_numeric($id)) {
            $w = "WHERE ".$this->primary." = ".$id;
        } else {
            $w = " WHERE {$id}";
        }
        //Monta a query
        $sql = trim("DELETE FROM {$table} {$w}");
        //Retornar V ou F 
        if ($this->execute($sql))
            return true;
        else
            return false;
    }
  //Metodo do tipo booleano para montar e execultar uma query do tipo UPDATE
    public function update($values, $id = null) {
        $table = $this->table;
        //Sepera os indices pela chave e valor
        foreach ($values as $key => $value) {
            $t[] = htmlspecialchars($key, ENT_QUOTES) . " = '" . htmlspecialchars($value, ENT_QUOTES) . "'";
        }
        $t = implode(", ", $t);
        //Monta a condição de atualização da tabela
        if ($id == null) {
            $w = null;
        } elseif (is_numeric($id)) {
            $w = "WHERE ".$this->primary." = ".$id;
        } else {
            $w = " WHERE {$id}";
        }
        //Monta a query
        $sql = trim("UPDATE {$table} SET {$t}{$w}");
        if ($this->execute($sql))
            return true;
        else
            return false;
    }
}