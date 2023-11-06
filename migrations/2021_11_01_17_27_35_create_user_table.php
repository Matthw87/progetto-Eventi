<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Marion\Core\Migration;
class CreateUserTableMigration extends Migration{
    
    public function up(){
        DB::schema()->create('user_categories',function(Blueprint $table){
            $table->id();
            $table->string('label',100)->nullable(true);
            $table->boolean('locked')->default(false)->nullable(true);
        });

        DB::schema()->create('user_categories_langs',function(Blueprint $table){
            $table->bigInteger('user_category_id')->unsigned(true);
            $table->string('name',100)->nullable(true);
            $table->string('locale',3)->nullable(true);
            $table->text('note')->nullable(true);
            $table->foreign('user_category_id')->references('id')->on('user_categories');
        });

        DB::schema()->create('profiles',function(Blueprint $table){
            $table->id();
            $table->string('name',50);
            $table->boolean('superadmin')->default(false)->nullable(true);
        });

        DB::schema()->create('users',function(Blueprint $table){
            $table->id();
            $table->string('username',100);
            $table->string('password',100);
            $table->string('email',100)->nullable(true);
            //$table->string('typeBuyer',50)->nullable(true);
            //$table->string('company',300)->nullable(true);
            //$table->boolean('restricted')->nullable(true)->default(false);
            $table->bigInteger('profile_id')->nullable(true)->unsigned(true);
            $table->bigInteger('user_category_id')->nullable(true)->unsigned(true);
            $table->string('name',100)->nullable(true);
            $table->string('surname',100)->nullable(true);
            $table->string('gender',1)->nullable(true);
            $table->boolean('active')->nullable(true)->default(false);
            $table->boolean('deleted')->nullable(true)->default(false);
            $table->string('phone',50)->nullable(true);
            $table->string('cellular',50)->nullable(true);
            $table->string('country',3)->nullable(true);
            $table->string('province',3)->nullable(true);
            $table->string('postal_code',60)->nullable(true);
            $table->string('city',200)->nullable(true);
            $table->string('address',200)->nullable(true);
            //$table->string('fiscalCode',16)->nullable(true);
           // $table->string('vatNumber',11)->nullable(true);
            //$table->string('token',500)->nullable(true);
            //$table->string('sidebarThemeAdmin',50)->nullable(true);
            $table->string('color_theme',50)->nullable(true);
            $table->string('locale',3)->nullable(true);
            $table->dateTime('created_at')->nullable(true)->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->nullable(true);
            //$table->bigInteger('default_address')->unsigned(true)->nullable(true);
            //$table->string('pec',100)->nullable(true);
            //$table->string('codice_univoco',50)->nullable(true);
        });

       

       


        DB::schema()->create('permissions',function(Blueprint $table){
            $table->id();
            $table->string('label',50)->nullable(true);
            $table->bigInteger('module_id')->nullable(true)->unsigned(true);
            $table->boolean('active')->default(false)->nullable(true);
            $table->integer('order_view')->nullable(true);
        });

        DB::schema()->create('permissions_langs',function(Blueprint $table){
            $table->bigInteger('permission_id')->unsigned(true);
            $table->string('name',50)->nullable(true);
            $table->text('description')->nullable(true);
            $table->string('locale',3)->nullable(true);
        });


        DB::schema()->create('profile_permissions',function(Blueprint $table){
            $table->bigInteger('profile_id')->nullable(true)->unsigned(true);
            $table->bigInteger('permission_id')->nullable(true)->unsigned(true);
            $table->foreign('profile_id')->references('id')->on('profiles');
            $table->foreign('permission_id')->references('id')->on('permissions');
            
        });
    }
    
    public function down(){
        DB::schema()->dropIfExists('permissions_langs');
        DB::schema()->dropIfExists('permissions');
        DB::schema()->dropIfExists('profile_permissions');
        DB::schema()->dropIfExists('user_categorie_langs');
        DB::schema()->dropIfExists('user_categories');
        DB::schema()->dropIfExists('profiles');
        DB::schema()->dropIfExists('users');
    }
}
?>