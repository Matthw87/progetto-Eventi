<?php
use Marion\Controllers\FrontendController;
use Marion\Core\Marion;
use Catalogo\Catalog;
class CronController extends FrontendController{
	



	function display(){
		$action = $this->getAction();
		switch($action){
			case 'prices':
				$this->prices();
				break;
			case 'availability':
				$this->availability();
				break;
			case 'search':
				$this->search();
				break;
		}

		debugga($action);
	}

    /**
     * funzione che calcola per ogni prodotto il valore stock che contiene la quantità totale di scorte per ogni prodotto
     * @return void
     */

	function availability(){

		$db = Marion::getDB();
		$sel = $db->select('id,parent,stock','product',"deleted =0 AND visibility=1");
		$quan = $db->select('*','product_inventory');
		$giace = [];
		foreach($quan as $v){
			$giac[$v['id_product']] = $v['quantity'];
		}
		$update = [];
		
		foreach($sel as $v){
			$old[$v['id']] = $v['stock'];
			if( $v['parent'] ){
				$update[$v['parent']] += $giac[$v['id']];
			}else{
				$update[$v['id']] += $giac[$v['id']];
			}
		}
		
		foreach($update as $id => $s){
			if( $s != $old[$id] ){
				$db->update('product',"id={$id}",array('stock' => $s));
			}
		}

	}
    /**
     * funzione che calcola i prezzi per la ricerca. Da eseguire una volta al giorno
     * @return void
     */

	function prices(){
        Catalog::loadPrices();
	}

    /**
     * function che aggiunge alla tabella degli indici di ricerca le chiavi di ricerca
     * @return void
     */

	function search(){
		Catalog::buildSearchIndexes();
	}

}