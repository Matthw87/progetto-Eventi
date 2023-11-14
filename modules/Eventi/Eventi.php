<?php
use Marion\Core\{Marion,Module};
use Marion\Support\Form\Form;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
class Eventi extends Module
{

    function install(): bool{
        if( parent::install() ){ 

            DB::schema()->create('Eventi',function(Blueprint $table){
                $table->id();
                $table->string('Event Name');
                $table->string('Location');
                $table->date('date');
                $table->string('description');

            });

            DB::schema()->create('Biglietti',function(Blueprint $table){
                $table->id();
                $table->integer('numero di biglietti');
                $table->bigInteger('id_evento')->unsigned();
                $table->string('nome prenotatore');
                $table->foreign('id_evento')->references('id')->on('Eventi')->onDelete('cascade');
            });
            return true;
        }else{
            return false;

        }
    }

    function uninstall(): bool
    {
        if( parent::uninstall() ){
            DB::schema()->dropIfExists('Biglietti');
            DB::schema()->dropIfExists('Eventi');
            




            
            return true;
        }else{
            return false;
        }
        
    }
}