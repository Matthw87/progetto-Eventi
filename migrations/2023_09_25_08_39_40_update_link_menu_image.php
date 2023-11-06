<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Migration;
class UpdateLinkMenuImageMigration extends Migration{
            
    public function up(){
        DB::schema()->table('link_menu_frontends',function( Blueprint $table){
            $table->string('image',500)->nullable(true)->change();
        });
        //DB::statement( 'ALTER TABLE link_menu_frontends MODIFY COLUMN image varchar(500)');
    }
    
    public function down(){
        DB::schema()->table('link_menu_frontends',function( Blueprint $table){
            $table->string('image',500)->change();
        });
    }
}
?>