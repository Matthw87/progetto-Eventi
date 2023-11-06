<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Migration;
class UpdateSliderMigration extends Migration{
            
    public function up(){
        DB::schema()->table('sliderfull_sliders',function(Blueprint $table){
			$table->string('arrows_direction',150)->default('horizontal');
            
            
           
		});

        DB::schema()->table('sliderfull_slides',function(Blueprint $table){
            $table->string('allowed_langs',500)->nullable(true);
            $table->string('allowed_user_categories',500)->nullable(true);
            $table->string('url',500)->nullable(true);
		});
    }
    
    public function down(){
        DB::schema()->table('sliderfull_sliders',function(Blueprint $table){
			$table->dropColumn('arrows_direction');
        });

        DB::schema()->table('sliderfull_slides',function(Blueprint $table){
            $table->dropColumn('url');
            $table->dropColumn('allowed_langs');
            $table->dropColumn('allowed_user_categories');
        });
    }
}
?>