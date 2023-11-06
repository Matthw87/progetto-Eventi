<?php
use Marion\Core\Module;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
class WidgetRevslider extends Module{

	

	function install(): bool{
		$res = parent::install();
		if( $res ){

			DB::schema()->create('revolution_slider',function(Blueprint $table){
				$table->id();
				$table->string('title',200)->nullable(true);
				$table->longText('js')->nullable(true);
				$table->longText('css')->nullable(true);
				$table->longText('content')->nullable(true);
				
			});
		}


		return $res;
	}



	function uninstall(): bool{
		$res = parent::uninstall();
		if( $res ){
			DB::schema()->dropIfExists('revolution_slider');
		}	
		return $res;
	}

}



?>