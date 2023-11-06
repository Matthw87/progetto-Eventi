<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Migration;
class CreateModuleTableMigration extends Migration{
    public function up(){
        DB::schema()->create('modules',function(Blueprint $table){
            $table->id();
            $table->string('directory',100);
            $table->string('name',50);
            $table->string('kind',50)->nullable(true);
            $table->string('author',100)->nullable(true);
            $table->text('description')->nullable(true);
            $table->string('permission',50)->nullable(true);
            $table->string('scope',20)->nullable(true);
            $table->string('tag',50)->nullable(true);
            $table->boolean('active')->nullable(true)->default(false);
            $table->string('dependencies',300)->nullable(true);
            $table->string('conflicts',300)->nullable(true);
            $table->string('status',50)->nullable(true);
            $table->string('version')->nullable(true);
            $table->boolean('autoload')->nullable(true)->default(false);
            $table->boolean('theme')->nullable(true)->default(false);
        });

        DB::schema()->create('hooks',function(Blueprint $table){
            $table->id();
            $table->string('name',150);
            $table->string('description',500)->nullable(true);
            $table->string('type',50);
            $table->bigInteger('module_id')->unsigned(true)->nullable(true);
            $table->string('module_name',50)->nullable(true);
            $table->boolean('active')->default(true);
            $table->string('path',300)->nullable(true);
        });

        DB::schema()->create('hook_actions',function(Blueprint $table){
            $table->id();
            $table->bigInteger('hook_id')->unsigned(true);
            $table->string('function',100);
            $table->bigInteger('module_id')->unsigned(true);
            $table->integer('priority')->unsigned(true)->default(10)->nullable(true);
        });

        DB::schema()->create('settings',function(Blueprint $table){
            $table->string('gruppo',100);
            $table->string('chiave',100);
            $table->text('valore')->nullable(true);
            $table->string('etichetta',100)->nullable(true);
            $table->text('descrizione')->nullable(true);
            $table->integer('ordine')->unsigned(true)->default(10)->nullable(true);
            $table->unique(['gruppo','chiave']);
        });

        DB::schema()->create('widgets',function(Blueprint $table){
            $table->id();
            $table->bigInteger('module_id')->unsigned(true);
            $table->string('function',100);
            $table->string('name',100)->nullable(true);
            $table->string('url_conf',200)->nullable(true);
            $table->boolean('repeatable')->default(false)->nullable();
            $table->bigInteger('composed_page_id')->unsigned(true)->nullable(true);
            $table->string('restrictions',500)->nullable(true);
        });
    }
    
    public function down(){
        DB::schema()->dropIfExists('settings');
        DB::schema()->dropIfExists('hook_actions');
        DB::schema()->dropIfExists('hooks');
        DB::schema()->dropIfExists('modules');
        DB::schema()->dropIfExists('widgets');
    }
}
?>