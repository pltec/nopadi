<?php

namespace App\Http\Middlewares;

use Nopadi\Http\Auth;
use Nopadi\Http\Middleware;

class Authenticate extends Middleware
    {
	 public function handle($role)
	 {
		 $role = is_null($role) ? null : $role;
		 
		 if(!Auth::check($role)){
			 $this->redirect('login');
		 } 

	 }
}
