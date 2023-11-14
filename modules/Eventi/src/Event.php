<?php
namespace Eventi;
use Marion\Core\Base;
class Event extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'events'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'event_langs'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'event_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault

	const LOG_ENABLED = true; //abilita i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore

}