<?php
namespace Marion\Entities;
use Marion\Core\Base;
use Marion\Core\Marion;
use Illuminate\Database\Capsule\Manager as DB;
class Profile extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'profiles'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = ''; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
    
    //public $permissions = [];

    public $permissions = [];
    public $id;
    public string $name;
    public bool $superadmin;

    function setPermissions($array = null){
        $this->permissions = $array;
    }


    function getPermissions(){
        $select = DB::table('profile_permissions')->where('profile_id',$this->id)->get(['permission_id'])->toArray();
       
       
        if( okArray($select) ){
            foreach($select as $v){
                $this->permissions[] = "".$v->permission_id;
            }
        }
       
    }

    function afterLoad(): void{
        parent::afterLoad();
        $this->getPermissions();
    }


    function afterSave(): void{
        parent::afterSave();
        $this->saveComposition();
    }



    function saveComposition(){
        DB::table('profile_permissions')->where('profile_id',$this->id)->delete();
        if( okArray($this->permissions) ){
            foreach($this->permissions as $v){
                $toinsert = array(
                    'permission_id' => $v,
                    'profile_id' => $this->id
                );
                DB::table('profile_permissions')->insert($toinsert);
            }
        }
    }

	function removeUser($id_user=NULL){
		if( $id_user ){
			
            DB::table('users')
                ->where('profile_id',$this->id)
                ->where('id',$id_user)
                ->update(
                    ['profile_id'=>0]
                );

			
		}
	}


	function delete(): void{
        DB::table('profile_permissions')->where('profile_id',$this->id)->delete();
		parent::delete();
		
	}

}


?>