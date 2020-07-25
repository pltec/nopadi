<?php 
/*
*Controlador nativo, responsável pelo gerenciamento de usuários. 
*/
namespace App\Controllers; 

use Nopadi\Http\Request;
use App\Models\UserModel;
use Nopadi\MVC\Controller;

class UserController extends Controller
{
   
   /*Retonar o tipo ou função do usuário*/
   public function roles($name=null)
   {
		$roles = [
		'subscriber'=>':subscriber',
		'client'=>':client',
		'affiliated'=>':affiliated',
		'partner'=>':partner',
		'franchise'=>':franchise',
		'collaborator'=>':collaborator',
		'author'=>':author',
		'editor'=>':editor',
		'dev'=>':dev',
		'admin'=>':admin',
		'demo'=>':demo'];
		
		$roles = array_text($roles);
		
		return  is_null($name) ? $roles : $roles[$name];

	}
   
   /*Exibe todos os usuários por meio da paginação*/
   public function index()
   {
	   
	 $list = UserModel::model()
	 ->orderBy('id desc')
	 ->paginate();
	 
     view('dashboard/users/all',[
	             'page_title'=>text(':users'),
	             'list'=>$list]);	
	 
    }
   
   /*Exibe o fomulário para editar o usuário*/
   public function edit()
   {
	  //Busca pelo usuário por meio do ID
	  $find = UserModel::model()->find($this->id());
	   
	  if($find){
		  
	   $roleOptions = options($this->roles(),$find->role);
       view('dashboard/users/edit',[
	       'page_title'=>text(':user.edit'),
	       'find'=>$find,
		   'roleOptions'=>$roleOptions]);
	   
	   }else view('404');
   }
   
	/*Exibe o fomulário para criar um usuário*/
    public function create()
	{
		
	  $roleOptions = options($this->roles());

       view('dashboard/users/add',[
	   'page_title'=>text(':user.create'),
	   'roleOptions'=>$roleOptions]);
	   
   }
   
   /*Cria um usuário*/
    public function store()
	{
		
	   $request = new Request();
	   $request = $request->all();
	   
	   $request = Auth::create($request);

	   if($request){
		   
	      hello(':user.create.success','success'); 
		   
	   }else{
		   
		   hello(':user.create.error','danger');  
	   }  
   }
   
   /*Atuliza um usuário*/
   public function update()
   {
	   $request = new Request();
	   
	   $id = $request->get('id');
	   $values = $request->all('id');
	   
	   $query = UserModel::model()->update($values,$id);
	   
	   if($query) hello(':user.update.success','success');
	   else hello(':user.update.error','danger');
	   
   }
   
   /*Apagar um usuário*/
   public function destroy()
   {
	  
	   $request = new Request();
	   
	   $id = $request->get('id');
	   
	   $query = UserModel::model()->delete($id);
	   
	   if($query) hello('ok');
	   else hello(':user.delete.error','danger');
	   
   }

  /*Retonar o idioma do usuário*/
   public function langs($name=null)
   {
		$langs = [
		 'pt-br'=>'Portugês do Brasil',
		 'en'=>'Inglês'
		];
		
		return  is_null($name) ? $langs : $langs[$name];

	}
} 
