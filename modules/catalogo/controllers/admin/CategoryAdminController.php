<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Catalogo\{Section,Product};
use Catalogo\Catalog;
class CategoryAdminController extends AdminModuleController{
	public $_auth = 'catalog';
	

	function displayForm(){
		$this->setMenu('manage_sections');
		
		$action = $this->getAction();
		
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			
			
			$array = $this->checkDataForm('section',$dati);
			if( $array[0] == 'ok'){

				if( $action == 'add' || $action == 'duplicate'){
					$obj = Section::create();
				}else{
					$obj = Section::withId($array['id']);
				}
				$obj->set($array);
				
				$obj->setRelatedSections($array['relatedSections']);
				

				$res = $obj->save();
				if( is_object($res) ){
					$this->saved();
				}else{
					$this->errors[] = $res;
				}


			}else{
				$this->errors[] = $array[1];
			}

			
			

		}else{
		
			createIDform();
		
			$id = $this->getID();
			
			if($action != 'add'){
				$obj = Section::withId($id);
				
				$dati =  $obj->getDataForm();
				

				if($action == 'duplicate'){
					unset($dati['id']);
					unset($dati['images']);
					$action = "add";
				}
			}else{
				$dati = NULL;
			}
		}
		
		$dataform = $this->getDataForm('section',$dati);
		
		//debugga($dataform);exit;
		$this->setVar('dataform',$dataform);
		$this->output('@catalogo/category/form.htm');
		
		

	}


	function setMedia(){
		if( $this->getAction() == 'list'){
			$this->registerJS('../plugins/jquery-nestable/jquery.nestable.js','end');
			$this->registerJS('../modules/catalogo/js/admin/category.js','end');
		}
	}

	function displayList(){
		$this->setMenu('manage_sections');
		$this->showMessage();


		$database = Marion::getDB();;
		$select = $database->select('count(id) as tot, section','product',"deleted = 0 AND parent = 0 group by section");
		if( okArray($select)) {
			foreach($select as $v){
				$GLOBALS['tot_section'][$v['section']] = $v['tot'];
			}
		}
		

		$this->addTemplateFunction(
			 new \Twig\TwigFunction('numProducts', function ($section=NULL) {
				//$database = Marion::getDB();;
				//$selec = $database->select('count(*)','procu')
				

				$tot = 0;
				$tmp = array($section->id => $section);
				$figlie = [];
				
				
				
				while(count($tmp) > 0 ){
					foreach($tmp as $v){
						if( okArray($v->children) ){
							foreach($v->children as $v1){
								$tmp[$v1->id] = $v1;
							}
							
							//$tot += $GLOBALS['tot_section'][$v->id];
						}
						unset($tmp[$v->id]);
						$figlie[] = $v->id;
					}
				}

				foreach($figlie as $f){
					$tot += $GLOBALS['tot_section'][$f];
				}

				
				if( $tot > 0 ){

					return "<span class='label label-success'>{$tot} prodotti</span>";
				}else{
					return "<span class='label label-warning'>0 prodotti</span>";
				}
			})
		);

		
		$tree = Catalog::getSectionTree(1);
		$this->setVar('items',$tree);
		$this->output('@catalogo/category/list.htm');
			
	}

	
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Categoria salvata con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Categoria eliminata con successo','success');
		}
	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}


	function delete(){
		$id = $this->getID();

		$obj = Section::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
		

		
	}




	function ajax(){
		
		$action = $this->getAction();
		$id = $this->getID();
		switch($action){
			case 'save_order_sections':

				$list = _var('list');
				//debugga($list);exit;
				$update = array('parent' => 0,'orderView' => 0);
				$database = Marion::getDB();;
				$database->update('section',"1=1",$update);
				foreach($list as $k => $v){
					
					$update = array('parent' => 0,'orderView' => $k);
					$database->update('section',"id={$v['id']}",$update);
					if( okArray($v['children']) ){
						foreach( $v['children'] as $k1 => $v1 ){
							$update = array('parent' => $v['id'],'orderView' => $k1); 
							$database->update('section',"id={$v1['id']}",$update);
							
							if( okArray($v1['children']) ){
								foreach( $v1['children'] as $k2 => $v2 ){
									$update = array('parent' => $v1['id'],'orderView' => $k2); 
									$database->update('section',"id={$v2['id']}",$update);
										
									if( okArray($v2['children']) ){
										foreach( $v2['children'] as $k3 => $v3 ){
											$update = array('parent' => $v2['id'],'orderView' => $k3); 
											$database->update('section',"id={$v3['id']}",$update);

											if( okArray($v3['children']) ){
												foreach( $v3['children'] as $k4 => $v4 ){
													$update = array('parent' => $v3['id'],'orderView' => $k4); 
													$database->update('section',"id={$v4['id']}",$update);
													
												}
											}
											
										}
									}
									
								}
							}
							
						}
					}

				}
				$risposta = array('result' => 'ok');

				break;
			case 'change_visibility':
				$obj = Section::withId($id);
				if( is_object($obj) ){
					if( $obj->visibility ){
						$obj->visibility = 0;
					}else{
						$obj->visibility = 1;
					}
					
					$obj->save();
					$risposta = array(
						'result' => 'ok',
						'status' => $obj->visibility
					);
				}else{
					$risposta = array(
						'result' => 'nak'	
					);
				}
				break;
				
		}

		echo json_encode($risposta);
		
	}


	//FORM

	function getSectionsSelect(){
		
		$toreturn[0] = '---SELECT---';
	

		$sezioni = Section::getAll('it');
		
		if( okArray($sezioni) ){
			foreach($sezioni as $k => $v){
				$toreturn[$k] = $v;
			}
		}
		
		
		
		return $toreturn;
	}

	function getSectionsMultiSelect(){
		
		
		$sezioni = Section::getAll('it');
		
		if( okArray($sezioni) ){
			foreach($sezioni as $k => $v){
				$toreturn[$k] = $v;
			}
		}
		
		
		return $toreturn;
	}

}



?>