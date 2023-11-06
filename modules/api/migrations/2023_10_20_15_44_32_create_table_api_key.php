<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Migration;
use Marion\Entities\Cms\MenuItem;

class CreateTableApiKeyMigration extends Migration{
            
    public function up(){
        DB::schema()->create('api_keys',function(Blueprint $table){
            $table->id();
            $table->string('api_key');
            $table->text('enabled_modules');
            $table->bigInteger('token_duration')->unsigned(true);
            $table->boolean('active')->default(true);
        });

        $parent = MenuItem::prepareQuery()->where('tag','setting')->getOne();
        MenuItem::create()->set(
            [
				'tag' => 'api_keys',
                'parent' => $parent->id,
				'permission' => 'superadmin',
				'scope' => 'admin',
				'url' => 'index.php?ctrl=Key&mod=api&action=list',
				'active' => 1,
				'priority' => 999,
				'show_label' => 0,
				'target_blank' => 0,
				'name' => 'Api keys'
			]
        )->save();
    }
    
    public function down(){
       DB::schema()->dropIfExists('api_keys');
       $menu = MenuItem::prepareQuery()->where('tag','api_keys')->getOne();
       if( $menu ){
          $menu->delete();
       }
    }
}
?>