<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Catalogo\{Attribute,AttributeSet};
class AttributeSetAdminController extends AdminModuleController{
	public $_auth = '';

	public static function getTitleTab(){
		return _translate('taxes');
	}
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Insieme attributi salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Insieme attributi eliminato con successo','success');
		}
	}

	function getList(){
		$database = Marion::getDB();;
		
		$condizione = "deleted = 0 AND ";
		
		
		$limit = $this->getListOption('per_page');
		
		if( $label = _var('label') ){
			$condizione .= "label LIKE '%{$label}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','attributeSet',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('*','attributeSet',$condizione);

		$total_items = $tot[0]['tot'];
		$this->setListOption('total_items',$total_items);
		$this->setDataList($list);
		
	}

	function displayList(){
		$this->setMenu('manage_attributeSets');
		$this->showMessage();

		$fields = array(
			0 => array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			),
			1 => array(
				'name' => 'Etichetta',
				'field_value' => 'label',
				'function_type' => 'value',
				'function' => 'strtoupper',
				'sortable' => true,
				'sort_id' => 'label',
				'searchable' => true,
				'search_name' => 'label',
				'search_value' => _var('label'),
				'search_type' => 'input',
			),

		);

		$this->setTitle(_translate('Attribute Sets','catalogo'));
		$this->setListOption('fields',$fields);
		$this->getList();


		parent::displayList();

	}

	
	function displayForm(){
		$this->setMenu('manage_attributeSets');
		$action = $this->getAction();
		$id = $this->getID();
		
		$attributi = Attribute::prepareQuery()->orderBy('label')->get();
		$this->setVar('attributi',$attributi);
		if( $this->isSubmitted() ){
			$dati = $this->getFormdata();
			$array = $this->checkDataForm('attributeSet',$dati);
			

			if( $array[0] == 'ok' ){
		
				$check = false;
				foreach( $dati['attributi'] as $v ){
					if( $v['id'] ){
						$check = true;
					}
				}
				if( !$check ){
					$array[0] = 'nak';
					$array[1] = 'Specificare almeno un attributo';
				}
				
			}
			if( $array[0] == 'nak'){
				$this->errors[] = $array[1];
			}else{

				if( $action == 'add' ){
					$obj =  AttributeSet::create();
				}else{
					$obj = AttributeSet::withId($array['id']);
					
				}
				
				if(is_object($obj)){
					
					$obj->setLabel($array['label']);

					$obj->clear();
					

					if(okArray($dati['attributi'])){
						foreach($dati['attributi'] as $v){
							$obj->addAttributeWithId($v['id']);
							$obj->setOrderForAttributeWithId($v['id'],(int)$v['orderView']);
							$obj->setTypeForAttributeWithId($v['id'],$v['type']);
							$obj->setImgForAttributeWithId($v['id'],$v['img']);
						}
					}
		
					$res = $obj->save();
					
				}
				$this->redirectToList(array('saved'=>1));
			}
			
		}else{

			if( $action != 'add' ){

				$obj = AttributeSet::withId($id);
		
				if($obj){
					foreach($obj as $k => $v){
						$dati[$k] = $v;
					}
					$composizione = $obj->getComposition();
					
					$this->setVar('composizione',$composizione);
				}
				
				if( $action == 'duplicate'){
					$action = 'add';
					unset($dati['id']);
				}
			}else{
				$dati = NULL;
			}

		}
		
		

		$dataform = $this->getDataForm('attributeSet',$dati);
		$this->setVar('dataform',$dataform);
		//get_form2($elements,'attributeSet',$action,$dati);
		

		$this->output('@catalogo/attribute_set/form.htm');
	}



	function bulk(){
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();
		switch($action){
			case 'delete':

				break;
		}
	}


	







	function delete(){
		$id = $this->getID();


		
		$obj = AttributeSet::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
	}



	

}

?>