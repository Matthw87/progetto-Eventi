<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Catalogo\{Attribute,AttributeValue};
class AttributeAdminController extends AdminModuleController{
	public $_auth = 'catalog';


	function displayForm(){
		$this->setMenu('manage_attributes');
		

		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();
		$locales = Marion::getConfig('locale','supportati');
		
		if( $this->isSubmitted() ){

			$dati = $this->getFormdata();
			


			$array = $this->checkDataForm('attribute',$dati);
			
			
			
			if($array[0] == 'ok'){
				
				$valori = $dati['valori'];

				
				foreach($valori as $v){
					/*if( !isMultilocale() ){
						$v['value'] = $v['value_'.getConfig('locale','default')];
					}*/
					$check_valore = $this->checkDataForm('attributeValue',$v);
					
					
					if($check_valore[0] == 'ok'){
						unset($check_valore[0]);
						$array_valore[] = $check_valore;	
					}else{
						$array[0] = 'nak';
						$array[1] = $check_valore[1];
					}
				}

				if( $action == 'edit'){
					$attributi_old = AttributeValue::prepareQuery()->where('attribute',$array['id'])->get();
					if(okArray($attributi_old)){
						foreach($attributi_old as $k => $v){
							$da_eliminare[$v->id] = $v;
						}
					}
				}
				
			}
			

			
			$database = Marion::getDB();;
			if($array[0] == 'ok'){
				if( $array['id'] ){
					$old_obj = Attribute::withId($array['id']);
					$list_check = $database->select('distinct attributeSet','attributeSetComposition',"attribute={$array['id']} AND attributeSet in (select id from attributeSet AND deleted=0)");
					/*if( okArray($list) ){

					}*/
				}
				//debugga($list);exit;

			}

			if( $array[0] == 'ok' ){

				if(	$action == 'add'){
					$obj = Attribute::create();
				}else{
					$obj = Attribute::withId($array['id']);
				}
				$obj->set($array);
				$res = $obj->save();
				
				
				if(is_object($res)){

					if(okArray($array_valore)){
						foreach($array_valore as $v){
							unset($da_eliminare[$v['id']]);
							$v['attribute'] = $id;
							$valore = AttributeValue::create()->set($v);
							if( !$v['id'] ){
								//debugga($valore);exit;
							}else{
								//debugga($valore);exit;
							}
							$valore->save();
						}
					
					}
					if(okArray($da_eliminare)){
						foreach($da_eliminare as $v){
							$v->delete();
						}
					}
					$this->redirectToList(array('saved'=>1));
				}
					

					



				$this->errors[] = $res;
			
				
			}else{
				
				$this->errors[] = $array[1];
				
				
			}
			
			
			/*foreach($dati['valori'] as $k => $v){
				$valori_attributo[$k] = $v;
				
				foreach( getConfig('locale','supportati')  as $loc){
					$valori_attributo[$k]['_localeData'][$loc]['value'] = $v["value"][$loc];
					unset($valori_attributo[$k]["value"][$loc]);
				}
				$valori_attributo[$k] = (object)$valori_attributo[$k];
				
			}
			if( $array['_locale_data'] ){
				foreach($array['_locale_data']  as $loc => $values){
					foreach($values as $k => $v){
						$array[$k."_{$loc}"] = $v;

					}
				}
				unset($array['_locale_data']);
			}*/
			foreach($dati['valori'] as $k => $v){
					$valori_attributo[$k] = $v;
					
					$valore_tmp = array(
						'id' => 'valore_'.$k,

					);
					foreach($locales as $lo){
						$valore_tmp['locales'][$lo] = array(
							'name' => 'formdata[valori]['.$k.'][value]['.$lo.']',
							'id' => "valore_{$k}_{$lo}",
							'type' => 'text',
							'value' => $v['value'][$lo],
							'other' => array(
								'locale' => $lo
							)


						);
					}
					$valori_attributo[$k]['valore'] = $valore_tmp;
			}

			//debugga($dati['valori']);exit;
		
			$this->setVar('valori_attributo',$valori_attributo);
			$this->setVar('cont_valori_attributo',count($valori_attributo));
		
			

			
		}else{
			
			$dati = NULL;
			if( $action != 'add'){
				$obj = Attribute::withId($id);
				if(is_object($obj) ){
					$dati = $obj->getDataForm();

					$query = AttributeValue::prepareQuery()->where('attribute',$id)->orderBy('orderView','ASC');//->get();
		
					$valori_obj = $query->get();
					//debugga($valori_obj);exit;
					//debugga($valori_obj);
					foreach($valori_obj as $k => $v){
						$valore_tmp = array(
							'id' => 'valore_'.$k,

						);
						foreach($locales as $lo){
							$valore_tmp['locales'][$lo] = array(
								'name' => 'formdata[valori]['.$k.'][value]['.$lo.']',
								'id' => "valore_{$k}_{$lo}",
								'type' => 'text',
								'value' => $v->get('value',$lo),
								'other' => array(
									'locale' => $lo
								)


							);
						}

						$valori_obj[$k]->valore = $valore_tmp;
						
						

						/*foreach($v->_localeData as $_loc => $v1){
							
							if(!in_array($_loc,getConfig('locale','supportati'))){
								unset($valori_obj[$k]->_localeData[$_loc]);
							}

						}
					
						foreach( getConfig('locale','supportati') as $loc){
							if(!array_key_exists($loc,$v->_localeData)){
								$valori_obj[$k]->_localeData[$loc]['value']='';
							}
						}*/
					}

					
					
					
					
					$this->setVar('valori_attributo',$valori_obj);
					$this->setVar('cont_valori_attributo',count($valori_obj));
					
				}
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}

		}

		$dataform = $this->getDataForm('attribute',$dati);
		$this->setVar('dataform',$dataform);
		

		
		$valore_input = array(
			'id' => 'valore',

		);
		foreach($locales as $lo){
			$valore_input['locales'][$lo] = array(
				'name' => 'valore_'.$lo,
				'id' => 'valore_'.$lo,
				'type' => 'text',
				'other' => array(
					'locale' => $lo
				)


			);
		}

		
		$this->setVar('valore_input',$valore_input);
		

		$this->setVar('num_col',count( getConfig('locale','supportati') )+3);
		//get_form2($elements,'attribute',$action,$dati);	
		$this->output('@catalogo/attribute/form.htm');	

		

	}


	function getList(){
		$database = Marion::getDB();;
		
		$condizione = "locale = '{$GLOBALS['activelocale']}' AND ";
		
		
		$limit = $this->getListOption('per_page');
		
		if( $name = _var('name') ){
			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','attribute as a join attributeLocale as l on l.attribute=a.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('a.id,l.name','attribute as a join attributeLocale as l on l.attribute=a.id',$condizione);
		
		
		$this->setListOption('total_items',$tot[0]['tot']);
		$this->setDataList($list);
		
	}

	function displayList(){
			$this->setMenu('manage_attributes');

			if( _var('saved') ){
				$this->displayMessage(_translate('attribute_saved'));
			}
			if( _var('deleted') ){
				$this->displayMessage(_translate('attribute_deleted'),'success');
			}


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
					'name' => 'Nome',
					'field_value' => 'name',
					'function_type' => 'value',
					'function' => 'strtoupper',
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
				),

			);

			
			$this->setTitle(_translate('Attributes','catalogo'));
			$this->setListOption('fields',$fields);
			$this->getList();
			
			
			/*$limit = $this->getLimitList();
			$offset = $this->getOffsetList();


			$user = Marion::getUser();
			$query = Attribute::prepareQuery()
				->offset($offset)
				->limit($limit);
				
			
			
			
			$list = $query->get();
			
			
			
			$tot = $tot[0]['cont'];
			
			
			$pager_links = $this->getPagerList($tot);

			
			$this->setVar('list',$list);
			$this->setVar('links',$pager_links);
			
			$this->output('@catalogo/attribute/list.htm');*/

			parent::displayList();
	}


	function delete(){
		$id = $this->getID();
		//$parameters = array();
		if( $this->checkAttributeSets($id)){

			$obj = Attribute::withId($id);
			if( is_object($obj) ){
				$obj->delete();
			}
		}else{
			$obj = Attribute::withId($id);
			if( is_object($obj) ){
				$this->errors[] = "L'attributo <b>{$obj->get('name')}</b> è parte della composizione di uno o più insieme attributi. Prima di procedere con questa operazione rimuovilo dalla composizione degli insieme attributi.";
			}
		}
		parent::delete();
		

		
	}


	function checkAttributeSets($id){
		$database = Marion::getDB();;
		$select = $database->select('*','attributeSetComposition',"attribute='{$id}'");
		return !okArray($select);
	}

}



?>