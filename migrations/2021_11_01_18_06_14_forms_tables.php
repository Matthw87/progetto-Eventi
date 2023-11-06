<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Migration;
class FormsTablesMigration extends Migration{
    
    public function up(){
        DB::schema()->create('form',function(Blueprint $table){
            $table->id('codice');
            $table->bigInteger('gruppo')->unsigned(true)->nullable(true);
            $table->string('nome',100);
            $table->string('commenti',200)->nullable(true);
            $table->string('action',100)->nullable(true);
            $table->text('url',300)->nullable(true);
            $table->string('method',6)->nullable(true);
            $table->boolean('captcha')->nullable(true)->default(false);
            
        });

       
        DB::schema()->create('form_tipo',function(Blueprint $table){
            $table->id('codice');
            $table->string('etichetta',100);
            $table->string('valore',100);
            $table->integer('ordine')->unsigned(true)->default(10);
            $table->string('sql',20)->nullable(true);
            
        });

        DB::schema()->create('form_tipo_data',function(Blueprint $table){
            $table->id('codice');
            $table->string('etichetta',100);
            $table->string('class',100);
            $table->integer('ordine')->unsigned(true)->default(10);
            
        });

        DB::schema()->create('form_tipo_file',function(Blueprint $table){
            $table->id('codice');
            $table->string('etichetta',100);
            $table->string('valore',100);
            $table->integer('ordine')->unsigned(true)->default(10);
            
        });

        DB::schema()->create('form_tipo_time',function(Blueprint $table){
            $table->id('codice');
            $table->string('etichetta',100);
            $table->string('class',100);
            $table->integer('ordine')->unsigned(true)->default(10);
            
        });

        DB::schema()->create('form_tipo_timestamp',function(Blueprint $table){
            $table->id('codice');
            $table->string('etichetta',100);
            $table->string('class',100);
            $table->integer('ordine')->unsigned(true)->default(10);
            
        });

        DB::schema()->create('form_tipo_textarea',function(Blueprint $table){
            $table->id('codice');
            $table->string('etichetta',100);
            $table->string('class',300);
            $table->integer('ordine')->unsigned(true)->default(10);
            
        });

        DB::schema()->create('form_type',function(Blueprint $table){
            $table->id('codice');
            $table->string('etichetta',20);
        });

        DB::schema()->create('form_gruppo',function(Blueprint $table){
            $table->id('codice');
            $table->string('nome',100);
            $table->boolean('bloccato')->nullable(true)->default(false);
        });

        DB::schema()->create('form_valore',function(Blueprint $table){
            $table->id('codice');
            $table->bigInteger('campo')->unsigned(true);
            $table->string('etichetta',100);
            $table->string('valore',100);
            $table->string('locale',5)->nullable(true)->default('it');
            $table->integer('ordine')->unsigned(true)->default(10)->nullable(true);
        });

        DB::schema()->create('form_campo',function(Blueprint $table){
            $table->id('codice');
            $table->bigInteger('form')->unsigned(true);
            $table->string('campo',100);
            $table->string('etichetta',100)->nullable(true);
            $table->boolean('gettext')->nullable(true)->default(false);
            $table->boolean('checklunghezza')->nullable(true)->default(false);
            $table->integer('lunghezzamin')->unsigned(true)->nullable(true);
            $table->integer('lunghezzamax')->unsigned(true)->nullable(true);
            $table->bigInteger('type')->unsigned(true);
            $table->bigInteger('tipo')->unsigned(true)->nullable(true);
            $table->boolean('obbligatorio')->default(false);
            $table->boolean('valuezero')->default(false);
            $table->string('default_value',100)->nullable(true);
            $table->text('codice_php')->nullable(true);
            $table->boolean('unique_value')->default(false);
            $table->boolean('globale')->default(false);
            $table->boolean('attivo')->default(true);
            $table->boolean('multilocale')->default(false);
            $table->integer('ordine')->unsigned(true)->default(10)->nullable(true);
            $table->boolean('tipo_valori')->default(false)->nullable(true);
            $table->string('function_template',100)->nullable(true);
            $table->boolean('tipo_textarea')->default(false)->nullable(true);
            $table->boolean('tipo_data')->default(false)->nullable(true);
            $table->boolean('tipo_time')->default(false)->nullable(true);
            $table->boolean('tipo_file')->default(false)->nullable(true);
            $table->boolean('tipo_timestamp')->default(false)->nullable(true);
            $table->string('ext_image',200)->nullable(true);
            $table->string('resize_image',200)->nullable(true);
            $table->boolean('dimension_resize_default')->default(false)->nullable(true);
            $table->string('dimension_image',250)->nullable(true);
            $table->string('ext_attach',200)->nullable(true);
            $table->integer('number_files')->default(0)->nullable(true);
            $table->string('class',300)->nullable(true);
            $table->string('post_function',300)->nullable(true);
            $table->string('pre_function',300)->nullable(true);
            $table->boolean('ifisnull')->default(false)->nullable(true);
            $table->string('value_ifisnull',100)->nullable(true);
            $table->boolean('dropzone')->default(false)->nullable(true);
            $table->text('descrizione')->nullable(true);
            $table->string('placeholder',100)->nullable(true);
            
            

           

        });
    }
    
    public function down(){
        DB::schema()->dropIfExists('form_tipo_data');
        DB::schema()->dropIfExists('form_tipo_time');
        DB::schema()->dropIfExists('form_tipo_timestamp');
        DB::schema()->dropIfExists('form_tipo_file');
        DB::schema()->dropIfExists('form_tipo_textarea');

        DB::schema()->dropIfExists('form_tipo');
        DB::schema()->dropIfExists('form_type');

        DB::schema()->dropIfExists('form_valore');
        DB::schema()->dropIfExists('form_campo');
        
        DB::schema()->dropIfExists('form');
        
        
       
      
       
       
        
        
    }
}
?>