<?php
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Migration;
use Illuminate\Database\Schema\Blueprint;
class UserPasswordMigration extends Migration{
            
    public function up(){
        DB::schema()->create('user_passwords',function(Blueprint $table){
            $table->id();
            $table->bigInteger('user_id')->unsigned(true);
            $table->string('password');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        }); 
        
        DB::schema()->create('password_tokens',function(Blueprint $table){
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
        DB::schema()->dropIfExists('user_passwords');
        DB::schema()->dropIfExists('password_tokens');
    }
}
?>