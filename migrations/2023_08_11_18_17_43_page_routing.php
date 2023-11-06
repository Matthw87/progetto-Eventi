<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Migration;
class PageRoutingMigration extends Migration{
            
    public function up(){
        DB::schema()->create('routes',function(Blueprint $table){
            $table->id();
            $table->string('route');
            $table->string('params');
            $table->string('methods');
            $table->string('action');
            $table->integer('priority')->default(1);
        });

       
        
        DB::schema()->table('pages',function(Blueprint $table){
            //
            $table->bigInteger('route_id')->unsigned(true)->nullable(true);
            $table->boolean('enable_routing')->nullable()->default(false);
            $table->foreign('route_id')->references('id')->on('routes');
        });
        DB::statement( 'ALTER TABLE pages_langs MODIFY COLUMN url varchar(100)');
      
    }
    
    public function down(){
      

        DB::schema()->table('pages',function(Blueprint $table){
            $table->dropForeign('pages_route_id_foreign');
        });
        DB::schema()->table('pages',function(Blueprint $table){
            $table->dropColumn('route_id');
            $table->dropColumn('enable_routing');
        });
        DB::schema()->dropIfExists('routes');
    }
}
?>