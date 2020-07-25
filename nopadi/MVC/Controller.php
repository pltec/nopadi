<?php
namespace Nopadi\MVC;

class Controller
{
	protected function objectWriteNot($m) 
	{
	
         $class = get_class($this);
		 
		 hello(style());
		 hello('#controller.error','danger');
		 hello($m,'danger');
		 hello($class,'danger');
 
	}
    /* Recurso para exibir a página principal */

    public function index() 
	{
        $this->objectWriteNot("index");
    }

    /* Recurso para exibir o formulário de tarefas create */

    public function create() 
	{
        $this->objectWriteNot("create");
    }

    /* Recurso para mostrar um única tarefa */

    public function show() 
	{
        $this->objectWriteNot("show");
    }

    /* Recurso para editar uma única tarefa */

    public function edit()
	{
        $this->objectWriteNot("edit");
    }

    /* Recurso para criação via metodo post */

    public function store() 
	{
        $this->objectWriteNot("store");
    }

    /* Recurso para deletar */

    public function destroy() 
	{
        $this->objectWriteNot("destroy");
    }

    /* Recurso para atualizar */

    public function update()
	{
        $this->objectWriteNot("update");
    }
	
	/* Recurso para ajuda */

    public function help() 
	{
        $this->objectWriteNot("help");
    }
   /* Recurso para saída do tipo json */
   
	public function json($value=null)
	{
		if(is_numeric($value) || is_string($value)){
			$value = array($value);
		}
        elseif(is_object($value)){
			$value = get_object_vars($value);
		}
		
		header('Content-Type: application/json;charset=utf-8');
		echo json_encode($value);
    }
	
	
	/* Recurso para pegar o id do recurso atual via parametro*/
	public function id()
	{
	   $resource = $_SERVER['REQUEST_URI'];
	   
	   $resource = explode('/',$resource);
	   $total = count($resource) - 1;
	   $last = $resource[$total];
	   
	    if($last == 'edit'){
			$last = isset($resource[$total - 1]) ? $resource[$total - 1] : $last;
		}
		
		return trim(htmlspecialchars($last, ENT_QUOTES));
    }
}
