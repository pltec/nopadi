<?php

namespace App\Http\Providers;

use Nopadi\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
       
      if(is_url('users{loop}')){
		  
		 hello('<h1>Web</h1><hr>');
		 var_dump(csrf_token());
	  }
	  if(is_url('api/users{loop}')){
		  
		 hello('<h1>API</h1><hr>');
		var_dump(csrf_token());
		 
	  }
	  
	  #[^api]
	  
	  if(is_url('{loop}')){
	
		 
	  }
	   
    }
	
}
