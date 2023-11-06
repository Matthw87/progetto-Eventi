<?php
namespace Marion\Entities\Cms;
use Marion\Core\Base;
use Marion\Core\Marion;
class Footer extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'footers'; // nome della tabella a cui si riferisce la classe
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
	
    public $page_id;

    private function createDinamicPage(): int{
        $id = 0;
		$database = Marion::getDB();
        $data = $database->select('*','composed_page_layouts',"label='footer'");
        if( okArray($data) ){
            $toinsert = array(
                'layout_id' => $data[0]['id'],
            );
            $id =$database->insert('composed_pages',$toinsert);
        }
		
		return $id;
	}


    function beforeSave(): void
    {
        if( !$this->page_id ){
            $this->page_id = $this->createDinamicPage();
        }
        
        parent::beforeSave();
    }
	
}


?>