<?php

use Eventi\Event;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Migration;
class UpdateTableTicketMigration extends Migration{
            
    public function up(){
        $event = Event::create()->set([
            'name' => 'Evento di appoggio',
            'description' => 'Evento di appoggio',
            'location' => 'Napoli',
            'date' => '2023-05-20',
        ])->save();
        DB::table('tickets')->update(['event_id' => $event->id]);
        DB::schema()->table('tickets',function(Blueprint $table){
            $table->bigInteger('event_id')->unsigned(true)->nullable(false)->change();
        });
    }
    
    public function down(){
        DB::schema()->table('tickets',function(Blueprint $table){
            $table->bigInteger('event_id')->unsigned(true)->nullable(true)->change();
        });
    }
}
?>