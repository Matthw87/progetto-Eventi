<?php
use Marion\Controllers\Controller;
use FormBuilder\FormBuilder;
use Marion\Core\Marion;
use Marion\Support\Form\Traits\FormHelper;

class EditorController extends Controller{
	use FormHelper;
	public $_auth = 'cms';

	

	function display(){
		$id = _var('id');
		
		//require('../modules/widget_developer/classes/FormBuilder.class.php');
		//require('../modules/widget_developer/classes/FormField.class.php');

		//debugga($id);exit;
		$database = Marion::getDB();
		$form = FormBuilder::withId($id);
		$this->setVar('id',$id);
		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$struttura = $formdata['editor'];
			$form->struttura = serialize(array_values($struttura));
			$form->save();
			$this->displayMessage('Dati salati con successo!','success');
			/*$array = $this->checkDataForm('widget_sliderfull',$formdata);
			if( $array[0] == 'ok'){
				unset($array[0]);
				
				$data = array();
				foreach($array as $k => $v){
					if( $k != '_locale_data'){
						$data[$k] = $v;
					}
				}
				foreach($array['_locale_data'] as $k =>$v){
					foreach($v as $k1 => $v1){
						$data[$k1][$k] = $v1;
					}
				}
		
				
				$dati = serialize($data);
				
				$database->update('composition_page_tmp',"id={$this->id_box}",array('parameters'=>$dati));
			
				$this->displayMessage('Dati salati con successo!','success');
			}else{
				$this->errors[]= $array[1];
			}*/
			$dati = $formdata;
			
		}else{
			$struttura = $form->struttura;

			
		}
		foreach($struttura as $k => $v){
			$num = count($v);
			switch($num){
				case 1:
					$col = '12';
					break;
				case 2:
					$col = '6';
					break;
				case 3:
					$col = '4';
					break;
			}
			$griglia[$k]['col'] = $col;
			$griglia[$k]['items'] = $v;
		}
		$campi = $form->getFields();
		$this->setVar('campi',$campi);
		$this->setVar('struttura',$griglia);
		$this->setVar('tot',count($griglia));
		
		
		//$dataform = $this->getDataForm('widget_sliderfull',$dati);
		
		//$this->setVar('dataform',$dataform);
		$this->output('@form_builder/admin/editor_template.htm');
	}


	







	function ajax(){
		
		
		$num = _var('num');
		$id = _var('id');
		$next = (int)_var('next');
		
		$form = FormBuilder::withId($id);
		

		$campi = $form->getFields();
		$this->setVar('campi',$campi);

		$this->setVar('j',$next);


		for( $k=0;$k<$num;$k++ ){
			$list[] = $k;	
		}

		switch($num){
			case 1:
				$col = '12';
				break;
			case 2:
				$col = '6';
				break;
			case 3:
				$col = '4';
				break;
		}

		$this->setVar('num',$col);
		$this->setVar('list',$list);
		ob_start();
		$this->output('@form_builder/admin/riga.htm');
		$html = ob_get_contents();
		ob_end_clean();
		$risposta = array(
			'result' => 'ok',
			'html' => $html
		);
		echo json_encode($risposta);
	}


}



?>