<?php

use Marion\Core\Module;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Tickets extends Module{


    function install(): bool
    {
        if( parent::install() ){
            //Installazione del database
            DB::schema()->create('ticket_status',function(Blueprint $table){
                $table->id();
                $table->boolean('active')->default(true);
            });

            DB::schema()->create('ticket_status_langs',function(Blueprint $table){
                $table->bigInteger('ticket_status_id')->unsigned(true);
                $table->string('name');
                $table->string('description');
                $table->string('lang',3)->default('it');
                $table->foreign('ticket_status_id')->references('id')->on('ticket_status')->onDelete('cascade');
            });
            return true;
        }else{
            return false;
        }
        
    }

    function uninstall(): bool
    {
        if( parent::uninstall() ){
            DB::schema()->dropIfExists('ticket_status_langs');
            DB::schema()->dropIfExists('ticket_status');
            return true;
        }else{
            return false;
        }
        
    }
}