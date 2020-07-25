<?php
namespace Nopadi\Base;
class Select
{
	private $query;
	
    public function __construct($select='*'){
		
		$this->query = $select;
		
	}
   public function results(){
	   return $this->query;
   }
}