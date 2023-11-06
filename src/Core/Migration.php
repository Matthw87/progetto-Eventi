<?php
namespace Marion\Core;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
class Migration{
    
    private $module;
    private $filename;

    public function __construct(string $module=null)
    {
        $this->module = $module;
        $class = get_class($this);
        $reflector = new \ReflectionClass($class);

        $basename = basename($reflector->getFileName());
        $filename = explode('.',$basename);
        $this->filename = $filename[0];
    

    }

    public function upgrade(){
       
        if( !DB::schema()->hasTable('migrations') ){
            DB::schema()->create("migrations",function(Blueprint $table){
                $table->id(); 
                $table->string("name",200);
                $table->string("module",200)->nullable(true);
                $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));

            });
        }
       
        
        $query = DB::table('migrations')->where('name',$this->filename);
        if( $this->module ){
            $query->where('module',$this->module);
        }
      
        if( !$query->exists() ){
            
            $this->up();
            $this->store($this->filename);
            return true;
        }
        return false;
    }

    public function downgrade(){
        $this->down();
        
        $query = DB::table('migrations')->where('name',$this->filename);
        if( $this->module ){
            $query->where('module',$this->module);
        }

        if( $query->exists() ){
            $query->delete();
            return true;
        }
        return false;
    }

    /**
     * richiamata quando viene effettutata una migrazione
     *
     * @return void
     */
    public function up(){
        //insert code for mogration up
    }
    /**
     * richiamata quando viene effettuato il rollback
     *
     * @return void
     */
    public function down(){
        //insert code for mogration down
    }

    /**
     * memorizza la migation nel dabatase
     *
     * @param string $name
     * @return void
     */
    private function store(string $name): void{
        DB::table('migrations')->insert(
            [
                'name' => $name,
                'module' => $this->module
            ]
        );
    }
}

?>