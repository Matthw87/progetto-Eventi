<?php
namespace Marion\Support\Form;
use Marion\Controllers\Controller;
use Marion\Support\Form\Traits\FormHelper;

class FormContainer{
	use FormHelper;
    private $ctrl;
	private $fields;
	private $html = '';
	public $template = 'layouts/base_form.htm';

    private $form_name;
    private $form_data;
    private $rows;
    private $widgets = [];

    function __construct(Controller $ctrl)
    {
        $this->ctrl = $ctrl;
    }

    public static function factory(Controller $ctrl){
        return new FormContainer($ctrl);
    }


	function setFormName($name): self{
		$this->form_name = $name;
		return $this;
	}

    function setDataForm($data): self{
        $this->form_data = $data;
		return $this;
    }


	function getName(): string{
		return $this->name;
	}


	function setTemplate(string $template): self{
		$this->template = $template;
		return $this;
	}

	function setFields(array $fields){
		$this->fields = $fields;
		return $this;
	}

	function getFields(): array{
		return $this->fields;
	}


	function build(): self{
		$this->html = '';
        $classes = [];
      

        if( $this->form_name ){
            $dataform = $this->getDataForm($this->form_name,$this->form_data,$this->ctrl);
            $this->setFields($dataform);
		    $this->ctrl->setVar('dataform',$dataform);
        }
        
		if( $this->template ){
           
			$this->html .= "{% extends '{$this->template}' %} \n";
			if( okArray($fields = $this->getFields())){
                $this->html .= "{% block edit_page_title %}{$this->ctrl->_title}{% endblock %} \n";
                $this->html .= "{% block content %} \n";
				$this->html .= "{% import 'macro/form.htm' as form %} \n";


                if( okArray($this->rows) ){
                    foreach($this->rows as $row){
                        $cont = count($row);
                        $class = "col-md-".(int)(12/$cont);
                        $this->html .= "<div class='row'> \n";
                        foreach($row as $field){
                            $widget = '';
                            if( array_key_exists($field,$this->widgets)){
                                $widget = $this->widgets[$field];
                            }
                           
                            $data_field = $fields[$field];
                            if($data_field['type'] == 'hidden' ){
                                if( $widget ){
                                    $this->html .= "{{form.{$widget}(dataform.{$field},'{$class}')}} \n";    
                                }else{
                                    $this->html .= "{{form.build(dataform.{$field})}} \n";    
                                }
                            }else{
                                if( $widget ){
                                    $this->html .= "{{form.{$widget}(dataform.{$field},'{$class}')}} \n";    
                                }else{
                                    $this->html .= "{{form.buildCol(dataform.{$field},'{$class}')}} \n";
                                }
                                
                            }
                            unset($fields[$field]);
                        }
                        $this->html .= "</div> \n";
                    }
                }
				foreach($fields as $k => $v1){
                    if($v1['type'] == 'hidden' ){
                        $this->html .= "{{form.build(dataform.{$k})}} \n";    
                    }else{
                        $this->html .= "{{form.buildCol(dataform.{$k},'col-md-12')}} \n";
                    }
				}
                $this->html .= "{% endblock %}";
			}
		}
        return $this;
	}

	function getHtml(): string{
		return $this->html;
	}

    function display(){
        if( !$this->html ) $this->build();
        //debugga($this->html);exit;
        $this->ctrl->outputString($this->getHtml());
    }
	
    /**
     * setta le righe del form
     *
     * @param [type] $rows
     * @return self
     */
    public function setRows($rows): self{
        $this->rows = $rows;
        return $this;
    }

    /**
     * imposta un widget di visualizzazione per un campo
     *
     * @param string $field
     * @param string $widget
     * @return self
     */
    function setWidget(string $field, string $widget): self{
        $this->widgets[$field] = $widget;
        return $this;
    }

    /**
     * imposta i widgets per i campi in bulk
     *
     * @param array $widgets
     * @return self
     */
    function setWidgets(array $widgets): self{
        foreach($widgets as $k => $v){
            $this->setWidget($k,$v);
        }
        return $this;
    }
}