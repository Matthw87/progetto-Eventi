<?php
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Migration;
use Illuminate\Database\Schema\Blueprint;

class createBaseTablesMigration extends Migration{
    
    public function up(){
      
        DB::schema()->create('locale',function(Blueprint $table){
            $table->string('code',10)->primary();
            $table->string('description',300)->nullable(true);
            $table->string('icon',50)->nullable(true);
            $table->string('time',20)->nullable(true);
        });
        
        DB::schema()->create('country',function(Blueprint $table){
            $table->string('id',3)->primary();
            $table->string('continent',3)->nullable(true);
        });

        DB::schema()->create('countryLocale',function(Blueprint $table){
            $table->string('country',3)->nullable(false);
            $table->string('name',100)->nullable(false);
            $table->string('locale',3)->nullable(false);

            $table->foreign('country')->references('id')->on('country');
        });


        DB::schema()->create('regione',function(Blueprint $table){
            $table->string('codice',3)->primary();
            $table->string('nome',100)->nullable(true);
            $table->string('capoluogo',100)->nullable(true);
        });

        DB::schema()->create('provincia',function(Blueprint $table){
            $table->string('sigla',2)->primary();
            $table->string('codice',3)->nullable(true);
            $table->string('nome',100)->nullable(true);
            $table->string('capoluogo',100)->nullable(true);
            $table->string('regione',2)->nullable(true);
        });
        
        DB::schema()->create('attachment',function(Blueprint $table){
            $table->id();
            $table->string('filename',100);
            $table->string('path',200);
            $table->string('type',100);
            $table->double('size')->default(0);
            $table->timestamp('date_insert')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        DB::schema()->create('image',function(Blueprint $table){
            $table->id();
            $table->string('filename',300);
            $table->string('filename_original',300);
            $table->string('path',500);
            $table->string('path_webp',500)->nullable(true);
            $table->string('mime',20);
            $table->string('ext',10)->nullable(true);
            $table->integer('width')->default(0)->nullable(true);
            $table->integer('height')->default(0)->nullable(true);
        });

        DB::schema()->create('imageComposed',function(Blueprint $table){
            $table->id();
            $table->integer('original')->nullable(true);
            $table->integer('thumbnail')->nullable(true);
            $table->integer('small')->nullable(true);
            $table->integer('medium')->nullable(true);
            $table->integer('large')->nullable(true);
            $table->timestamp('date_insert')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable(true);
            
        });
        
        DB::schema()->create('link_menu_frontends',function(Blueprint $table){
            $table->id();
            $table->string('url',500)->nullable(true);
            $table->boolean('visibility')->nullable(true);
            $table->integer('orderView')->nullable(true);
            $table->bigInteger('parent')->nullable(true)->unsigned(true);
            $table->string('id_url_page',100)->nullable(true);
            $table->boolean('target_blank',50)->nullable(true);
            $table->string('url_type',100)->nullable(true);
            $table->bigInteger('image')->nullable(true)->unsigned(true);
            $table->boolean('static_url')->nullable(true)->default(false);
        });

        DB::schema()->create('link_menu_frontends_langs',function(Blueprint $table){
            $table->bigInteger('link_menu_frontend_id')->unsigned(true);
            $table->string('title',300)->nullable(true);
            $table->string('url_dinamic',300)->nullable(true);
            $table->string('locale',3)->nullable(true);

            $table->foreign('link_menu_frontend_id')->references('id')->on('link_menu_frontends')->onDelete('cascade');
        });

        DB::schema()->create('home_buttons',function(Blueprint $table){
            $table->id();
            $table->string('module_id',50)->nullable(true);
            $table->string('url',300)->nullable(true);
            $table->string('icon_image',300)->nullable(true);
            $table->integer('order_view')->nullable(true)->default(10);
            $table->boolean('active')->nullable(true)->default(true);
        });

        DB::schema()->create('home_buttons_langs',function(Blueprint $table){
            $table->bigInteger('home_button_id')->unsigned(true);
            $table->string('name',100)->nullable(true);
            $table->string('locale',3)->nullable(true)->default('it');

            $table->foreign('home_button_id')->references('id')->on('home_buttons')->onDelete('cascade');
        });


        DB::schema()->create('menu_items',function(Blueprint $table){
            $table->id();
            $table->bigInteger('module_id')->unsigned(true)->nullable(true);
            $table->bigInteger('parent')->unsigned(true)->nullable(true);
            $table->boolean('active')->nullable(true)->default(true);
            $table->string('url',200)->nullable(true);
            $table->string('tag',50)->nullable(true);
            $table->string('scope',50)->nullable(true);
            $table->string('permission',50)->nullable(true);
            $table->string('icon',100)->nullable(true);
            $table->string('icon_image',100)->nullable(true);
            $table->integer('priority')->nullable(true);
            $table->string('label_type',50)->nullable(true);
            $table->boolean('show_label')->nullable(true)->default(false);
            $table->string('label_text',50)->nullable(true);
            $table->string('label_function',100)->nullable(true);
            $table->boolean('target_blank')->nullable(true)->default(false);
            
            
        });

        DB::schema()->create('menu_items_langs',function(Blueprint $table){
            $table->bigInteger('menu_item_id')->unsigned(true)->nullable(true);
            $table->string('name',100)->nullable(true);
            $table->string('locale',3)->nullable(true);

            $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('cascade');
        });
        

        DB::schema()->create('notifications',function(Blueprint $table){
            $table->id();
            $table->bigInteger('sender_id')->unsigned(true)->nullable(true);
            $table->bigInteger('receiver_id')->unsigned(true)->nullable(true);
            $table->boolean('view')->nullable(true)->default(false);
            $table->dateTime('date')->nullable(true);
            $table->integer('priority')->nullable(true)->default(1);
            $table->string('type',20)->nullable(true);
            $table->string('custom',100)->nullable(true);
            
        });

        DB::schema()->create('notifications_langs',function(Blueprint $table){
            $table->text('text')->nullable(true);
            $table->string('locale',3)->nullable(true);
            $table->bigInteger('notification_id')->unsigned(true);
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
        });
        
        DB::schema()->create('pages',function(Blueprint $table){
            $table->id();
            $table->boolean('visibility')->nullable(true)->default(true);
            $table->boolean('locked')->nullable(true)->default(false);
            $table->boolean('widget')->nullable(true);
            $table->boolean('advanced')->nullable(true)->default(false);
            $table->bigInteger('composed_page_id')->unsigned(true)->nullable(true);
            $table->string('theme',150)->nullable(true);
            $table->timestamp('created_at')->nullable(true)->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable(true)->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        DB::statement("ALTER TABLE pages AUTO_INCREMENT = 2;");

        DB::schema()->create('pages_langs',function(Blueprint $table){
            $table->bigInteger('page_id')->unsigned(true)->nullable(true);
            $table->string('url',100);
            $table->string('title',100)->nullable(true);
            $table->text('content')->nullable(true);
            $table->string('meta_title',60)->nullable(true);
            $table->string('meta_description',160)->nullable(true);
            $table->string('locale',3)->nullable(true);

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });

    

   

        DB::schema()->create('footers',function(Blueprint $table){
            $table->id();
            $table->bigInteger('page_id')->unsigned(true)->nullable(true);
            $table->string('name',100)->nullable(true);
            $table->boolean('active')->nullable(true)->default(false);
        });

        DB::schema()->create('homepages',function(Blueprint $table){
            $table->id();
            $table->bigInteger('page_id')->unsigned(true)->nullable(true);
            $table->string('name',100)->nullable(true);
            $table->boolean('active')->nullable(true)->default(false);
            $table->boolean('timer')->nullable(true)->default(false);
            $table->date('start_date')->nullable(true);
            $table->date('end_date')->nullable(true);
        });
        

       
    }
    
    public function down(){
        DB::schema()->dropIfExists('locale');
        DB::schema()->dropIfExists('provincia');
        DB::schema()->dropIfExists('regione');
        DB::schema()->dropIfExists('image');
        DB::schema()->dropIfExists('imageComposed');
        DB::schema()->dropIfExists('attachment');
        DB::schema()->dropIfExists('pages_langs');
        DB::schema()->dropIfExists('pages');
        DB::schema()->dropIfExists('notifications_langs');
        DB::schema()->dropIfExists('notifications');
        DB::schema()->dropIfExists('link_menu_frontends_langs');
        DB::schema()->dropIfExists('link_menu_frontends');
        DB::schema()->dropIfExists('menu_items_langs');
        DB::schema()->dropIfExists('menu_items');
        DB::schema()->dropIfExists('home_button_langs');
        DB::schema()->dropIfExists('home_buttons');
        DB::schema()->dropIfExists('footers');
        DB::schema()->dropIfExists('homepages');
    }
}
?>