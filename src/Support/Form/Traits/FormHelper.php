<?php
namespace Marion\Support\Form\Traits;
use Marion\Support\Form\Form;
trait FormHelper{
    /**
	 * Check data form 
	 *
	 * @param string $nameform
	 * @param array|null $data
	 * @param array|null $override
	 * @param Controller|null $ctrl
	 * @return array
	 */
	function checkDataForm(
        string $nameform,
        array $data = null,
        array $override = null,
        Object $object = null
    ): array{
        $dataform = null;
        if( $nameform ){
            $form = new Form($nameform);
            
            if( is_object($form) ){
                if( okArray($override)){
                    $form->addElements($override);
                }
                if(!$object){
                    $dataform = $form->checkData($data,$this);
                }else{
                    $dataform = $form->checkData($data,$object);
                }
            }
        }

        return $dataform;
    }

    /**
     * Get data for form
     *
     * @param string $nameform
     * @param array|null $data
     * @param Object|null $ctrl
     * @param array|null $override
     * @return array
     */
    function getDataForm(
        string $nameform,
        $data=null,
        Object $object=null,
        array $override=null
        ): array{
        $dataform = null;
        if( $nameform ){
            
            $form = new Form($nameform);
            if( is_object($form) ){
                if( okArray($override)){
                    
                    $form->addElements($override);
                }
                if(!$object){
                    $dataform = $form->prepareData($data,$this);
                }else{
                    $dataform = $form->prepareData($data,$object);
                }
                
            }
            
        }

        return $dataform;
    }

    /**
     * Metodo che restituisce i dati del form sottomesso
     *
     * @param [type] $num
     * @return mixed
     */
	function getFormdata($num=null){
		if(	$num ){
			if( $this->_ajax ){
				$formdata = _formdata($num);
			}else{
				$formdata = _var('formdata'.$num);
			}
		}else{
			if( $this->_ajax ){
				$formdata = _formdata();
			}else{
				$formdata = _var('formdata');
			}
		}		
		
		return $formdata;
	}

    /**
	* Metodo che verifica se il form è stato sottomesso
	*
	*@return boolean
	*/
	function isSubmitted(){
		
		return okArray($this->getFormdata());
	}
}