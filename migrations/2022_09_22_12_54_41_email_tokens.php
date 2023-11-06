<?php
    use Illuminate\Database\Capsule\Manager as DB;
    use Marion\Core\Migration;
    use Illuminate\Database\Schema\Blueprint;

    class emailTokensMigration extends Migration{

        public function up(){
            DB::schema()->create('email_tokens',function(Blueprint $table){
                $table->id();
                $table->string('email');
                $table->string('token');
                $table->dateTime('expiration_date');
            });
        }

        public function down(){
            DB::schema()->dropIfExists('email_token_activations');
        }
    }
?>