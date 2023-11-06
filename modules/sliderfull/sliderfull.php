<?php
use Marion\Core\Module;
use Marion\Support\Form\Form;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
class Sliderfull extends Module{

	function install(): bool{
		
		

		Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"widget_sliderfull\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"704\",\"campo\":\"id_slider\",\"etichetta\":\"id slider\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"0\",\"function_template\":\"array_sliderfull\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}}]}");

		

		DB::schema()->create('sliderfull_sliders',function(Blueprint $table){
			$table->id();
			$table->string('name',150);
		});

		DB::schema()->create('sliderfull_slides',function(Blueprint $table){
			$table->id();
			$table->bigInteger('slider_id')->unsigned(true)->nullable(false);
			$table->string('image')->nullable(false);
			$table->string('mobile_image')->nullable(true);
			$table->string('webp_image')->nullable(true);
			$table->string('webp_mobile_image')->nullable(true);
			$table->date('date_start')->nullable(true);
			$table->date('date_end')->nullable(true);
			$table->integer('order_view')->nullable(true);
			$table->string('locales',20)->default('#000000')->nullable(true);
			$table->string('color_subtitle',20)->nullable(true)->default('#000000');
			$table->string('color_title',20)->nullable(true)->default('#000000');

			$table->foreign('slider_id')
				->references('id')
				->on('sliderfull_sliders')
				->onDelete('CASCADE');

		});

		DB::schema()->create('sliderfull_slides_langs',function(Blueprint $table){
			$table->bigInteger('slide_id')->unsigned(true)->nullable(false);
			$table->string('link_slide',300)->nullable(true);
			$table->string('title',200)->nullable(true);
			$table->string('subtitle',200)->nullable(true);
			$table->string('lang',3)->nullable(true)->default('it');

			$table->foreign('slide_id')
				->references('id')
				->on('sliderfull_slides')
				->onDelete('CASCADE');
		});

		return parent::install();
	}



	function uninstall(): bool{
		DB::schema()->dropIfExists('sliderfull_slides_langs');
		DB::schema()->dropIfExists('sliderfull_slides');
		DB::schema()->dropIfExists('sliderfull_sliders');
		
		//Form::delete('module_sliderfull_slide');
		//Form::delete('module_sliderfull_slider');
		Form::delete('widget_sliderfull');
	
		return parent::uninstall();
	}

}



?>