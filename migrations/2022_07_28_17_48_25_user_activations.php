<?php
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Migration;
use Illuminate\Database\Schema\Blueprint;
class UserActivationsMigration extends Migration{
            
    public function up(){
        DB::schema()->create('user_token_activations',function(Blueprint $table){
            $table->id();
            $table->bigInteger('user_id')->unsigned(true);
            $table->string('token');
            $table->dateTime('expiration_date');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
    
    public function down(){
        DB::schema()->dropIfExists('user_token_activations');
    }
}
?>