<?php
use Marion\Core\{Marion,Module};
use Marion\Support\Form\Form;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
class Eventi extends Module
{

    function install(): bool{
        if( parent::install() ){ 
            DB::schema()->create('events',function(Blueprint $table){
                $table->id();
                $table->date('date');

            });
            DB::schema()->create('event_langs',function(Blueprint $table){
                $table->string('name');
                $table->string('location');
                $table->text('description');
                $table->string('lang',3)->default('it');
                $table->bigInteger('event_id')->unsigned(true);
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            });

            DB::schema()->create('tickets',function(Blueprint $table){
                $table->id();
                $table->string('user');
                $table->integer('ticket_numbers');
                $table->bigInteger('event_id')->unsigned(true)->nullable(true);
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');

            });
            
            return true;
        }else{
            return false;

        }
    }

    function uninstall(): bool
    {
        if( parent::uninstall() ){
            DB::schema()->dropIfExists('tickets');
            DB::schema()->dropIfExists('event_langs');
            DB::schema()->dropIfExists('events');
            




            
            return true;
        }else{
            return false;
        }
        
    }
}