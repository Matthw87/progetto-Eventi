<?php
namespace Marion\Support\Form;

use Marion\Controllers\Controller;
use Marion\Core\Marion;
use Marion\Support\Form\FormData;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

class Fragment {

    private $id;
    private string $template_string;
    private string $template_file;
    private array $fields;
    public FormData $formData;
    private array $data = [];
    private $ctrl;
    private array $_template_vars = [];
    private array $_template_global_vars = [];
    private array $_twig_functions = [];

    public $js_assets = [];
    public $css_assets = [];

    /**
     * Form error fields
     *
     * @var array
     */
    public array $error_fields; 

    function __construct($id, Controller $ctrl)
    {
        $this->id = $id;
        $this->ctrl = $ctrl;
        $this->formData = new FormData();
        
    }


    public function setTemplate(string $template): self{
        $this->template_string = $template;
        return $this;
    }

    public function getTemplateString(): string{
        return $this->template_string;
    }

    public function setTemplateFile(string $template): self{
        $this->template_file = $template;
        return $this;
    }

    public function setFields(array $fields): self{
        $this->fields = $fields;
        return $this;
    }
    public function getFields(): array{
        return $this->fields;
    }

    public function getId(): string{
        return $this->id;
    }

    public function setDataForm(array $data): self{
        $this->data = $data;
        return $this;
    }

    public function setVar($key,$val): self{
        $this->_template_vars[$key] = $val;
        return $this;
    }

    public function setGlobalVar($key,$val): self{
        $this->_template_global_vars[$key] = $val;
        return $this;
    }

    public function addTemplateFunction(TwigFunction $function){
        $this->_twig_functions[] = $function;
    }

    public function prepareForm(){
        $this->formData->setFields($this->fields);
        $this->formData->data = $this->data;
    }

    public function buildWithoutRenderForm(FormHelper $form){
       
        
        $_html = $this->getHtml();
        $templates = array(
            'form' => $_html
        );
        $env = new Environment(new ArrayLoader($templates));
        ob_get_contents();
        echo $env->render('form', $this->_template_vars);
        //$this->ctrl->outputString($html);
        $html_fragment = ob_get_contents();
        ob_end_clean();
       
        foreach($this->_twig_functions as $function){
            $this->ctrl->addTemplateFunction($function);
        }
        
        $form->formData->data = array_merge($form->formData->data,$this->data);
        $form->fields = array_merge($form->fields,$this->fields);
        return $_html;
            
        
    }

    private function getAssets(array $dataform){
        $css = [];
        $js = [];
        foreach( $dataform as $_field => $_options ){

        
            $js_libreries = isset($dataform[$_field]['js_libraries'])?$dataform[$_field]['js_libraries']:[];
            
            if(okArray($js_libreries) ){
                foreach($js_libreries as $lib){
                    switch($lib){
                        case 'multiselect':
                            $js[] = [
                                'type' => 'content',
                                'lib_name' => $lib, 
                                'content' => file_get_contents('../assets/plugins/lou-multi-select/js/jquery.multi-select.js')
                            ];

                            $css[] = [
                                'type' => 'url',
                                'lib_name' => $lib, 
                                'url' => $this->ctrl->getBaseUrl().'assets/plugins/lou-multi-select/css/multi-select.css'
                            ];
                            break;
                        case 'spectrum':
                            $js[] = [
                                'type' => 'content',
                                'lib_name' => $lib, 
                                'content' => file_get_contents('../assets/plugins/spectrum/spectrum.js')
                            ];
                            $css[] = [
                                'type' => 'url',
                                'lib_name' => $lib, 
                                'url' => $this->ctrl->getBaseUrl().'assets/plugins/spectrum/spectrum.css'
                            ];
                            break;
                    }
                }
                
            }
            
        }
        $this->js_assets = $js;
        $this->css_assets = $css;
    }

    public function build(){
        
        $_html = $this->getHtml();
        

        $this->prepareForm();
        //debugga($this->formData);exit;
        $dataform = $this->formData->prepare();
        $this->getAssets($dataform);
        $html = "{% import 'macro' as form %} \n";
        $html .= $_html;
        $templates = array(
            'macro' => file_get_contents(_MARION_ROOT_DIR_."src/Twig/macro/form.htm"),
            'form' => $html
        );

        $this->loadTemplateVariables();
        $this->loadGlobalVariables();
        $this->loadTemplateFunctions();
        $this->setVar('dataform',$dataform);
        ob_start();
        $env = new Environment(new ArrayLoader($templates));
        foreach($this->_template_global_vars as $key => $value){
            $env->addGlobal($key,$value);
        }
        
        foreach($this->_twig_functions as $function){
            $env->addFunction($function);
        }
        ob_get_contents();
        echo $env->render('form', $this->_template_vars);
        $html_fragment = ob_get_contents();
        ob_end_clean();
        return $html_fragment;
    }

    public function getHtml(): string{
        if( isset($this->template_file) ){
            $xml = file_get_contents($this->template_file);
        }else{
            $xml = $this->template_string;
        }
        
        
        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);
        $dom_xpath = new \DOMXPath($dom);

        $sheet = $dom->getElementsByTagName("fragment")[0];
       
        if( $sheet->childNodes->count() > 1){
            throw new \Exception('FRAGMENT: a single container can be defined in a fragment');
        }
        $sheet->childNodes->item(0)->setAttribute('fragment-id',$this->getId());

        $depth = 2;
        for( $k=$depth; $k >= 0; $k--){
            $query_row = $this->getQueryRowXpath($k);
            
            $xpaths = $dom_xpath->query($query_row);
            foreach($xpaths as $current){
                $parent = $current->parentNode;
                $columns = $this->getElementsByTagName($current,'col');
                $num = count($columns);
                foreach($columns as $col){
                    $col->setAttribute('size',(int)(12/$num));
                }
                $html = "<div class='row' {$this->stringfyAttributes($current)}>";
                $html .= $this->getContetHmtl($current);
                $html .= "</div>";
                
                $fragment = $dom->createDocumentFragment();
                $fragment->appendXML($html);
                $parent->replaceChild($fragment,$current);
            }
        }
        for( $k=$depth; $k >= 0; $k--){
            $query_col = $this->getQueryColumnXpath($k);
            $xpaths = $dom_xpath->query($query_col);
            foreach($xpaths as $current){
                $parent = $current->parentNode;
                
                $size = $current->getAttribute('size');
                if( !$size ) $size = 12;
                $html = "<div class='col-md-{$size}' {$this->stringfyAttributes($current)}>";
                $html .= $this->getContetHmtl($current);
                $html .= "</div>";
            
                $fragment = $dom->createDocumentFragment();
                $fragment->appendXML($html);
                $parent->replaceChild($fragment,$current);
            }
        }

       
        $xpaths = $dom_xpath->query("//field");
        foreach($xpaths as $current){
            $parent = $current->parentNode;
           
            $name = $current->getAttribute('name');
            $hidden = $current->getAttribute('hidden');
           
            if( strtolower($hidden) == 'true' ){
                $html = "{{form.build(dataform['{$name}'])}}";
            }else{
                $html = "{{form.buildCol(dataform['{$name}'])}}";
            }
           
            
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($html);
            $parent->replaceChild($fragment,$current);
        }
        
        $xpaths = $dom_xpath->query("//tabs");
        foreach($xpaths as $current){
            $parent = $current->parentNode;
            $tabs_name = $current->getAttribute('name');
            
            $tabs = $this->getElementsByTagName($current,'tab');

            $html = PHP_EOL.'<div class="tabcordion"'." {$this->stringfyAttributes($current)}>".PHP_EOL.'<ul id="'.$tabs_name.'" class="nav nav-tabs">'.PHP_EOL;
            foreach($tabs as $ind => $tab){
                $module = $this->ctrl->_module;
               
                $name = $tab->getAttribute('name');
                $name_slug = Marion::slugify($name);
                $active = ($ind == 0)?'active':'';
                if($module){
                    $translated_name = _translate($name,$module);
                }else{
                    $translated_name = _translate($name);
                }
                
                $html.= "<li class='{$active}' {$this->stringfyAttributes($tab)}><a  data-toggle='tab' href='#{$name_slug}'>{$translated_name}</a></li>".PHP_EOL;
            }
            $html .= '</ul>'.PHP_EOL.'<div id="'.$tabs_name.'Content" class="tab-content">'.PHP_EOL;

            foreach($tabs as $ind => $tab){
                $active = ($ind == 0)?'active':'';
                $name = Marion::slugify($tab->getAttribute('name'));
                $content = $this->getContetHmtl($tab);
                $html .= '<div class="tab-pane '.$active.' in" id="'.$name.'">'.$content."</div>".PHP_EOL;
            }
            $html .= "</div>".PHP_EOL."</div>".PHP_EOL;
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($html);
            $parent->replaceChild($fragment,$current);
        }
        
        $xpaths = $dom_xpath->query("//tab");
        foreach($xpaths as $current){
            
            $parent = $current->parentNode;
            $fragment = $dom->createDocumentFragment();
            $html = $this->getContetHmtl($current);
            
            $fragment->appendXML($html);
            $parent->replaceChild($fragment,$current);
        }

        $xpaths = $dom_xpath->query("//kanbans");
        foreach($xpaths as $current){
            $parent = $current->parentNode;
            $columns = $this->getElementsByTagName($current,'kanban');
            $num = count($columns);
            foreach($columns as $col){
                $col->setAttribute('size',(int)(12/$num));
            }

            $classes = $this->getClasses($current,'kanbans row');
            $html = "<div class='{$classes}' {$this->stringfyAttributes($current)}>";
            $html .= $this->getContetHmtl($current);
            $html .= "</div>";
            
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($html);
            $parent->replaceChild($fragment,$current);
        }
        
        $xpaths = $dom_xpath->query("//kanban");
        $module = $this->ctrl->_module;
        foreach($xpaths as $current){
            $parent = $current->parentNode;
            $size = $current->getAttribute('size');
            $name = $current->getAttribute('name');
            $translated_name = null;
            if( $name ){
                if($module){
                    $translated_name = _translate($name,$module);
                }else{
                    $translated_name = _translate($name);
                }
            }
            
            if( !$size ) $size = 12;
            $classes = $this->getClasses($current,"col-md-{$size} kanban");
            $html = "<div class='{$classes}' {$this->stringfyAttributes($current)}><div class='kanban-content'>";
            if( $translated_name ){
                $html .= "<h2>{$translated_name}</h2>";
            }
            $html .= $this->getContetHmtl($current);
            $html .= "</div></div>";
        
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($html);
            $parent->replaceChild($fragment,$current);
        }

      
        $sheet = $dom_xpath->query("//fragment")[0];
        return $this->getContetHmtl($sheet);
    }

    private function getElementsByTagName(\DOMElement $element, string $tag): array{
        $children = $element->childNodes;
        $toreturn = [];
        foreach ($children as $child) { 
            if($child->tagName == $tag ){
               $toreturn[] = $child;
            }
        }
        return $toreturn;
    }

    /**
     * Get content in format html from DOMEement
     *
     * @param \DOMElement $element
     * @return string
     */
    private function getContetHmtl(\DOMElement $element): string{
        $html = '';
        $children = $element->childNodes; 
        foreach ($children as $child) { 
            $html .= $element->ownerDocument->saveXML( $child ); 
        }
        return $html;
    }

    private function getClasses($node,$otherclass=''){
        $class = $node->getAttribute('class');
        if( $class ){
            $class .= " ".$otherclass;
        }else{
            $class = $otherclass;
        }
        return $class;
    }

    private function stringfyAttributes($node){
        $attributes = $node->attributes;
        $_attr = "";
        if( $attributes->length > 0 ){
            foreach($attributes as $attr){
                if( $attr->name != 'class' ){
                    $_attr .= "{$attr->name}='{$attr->value}' ";
                }
                
            }
        }
        return $_attr;
    }

    private function getQueryRowXpath(int $depth): string{
        $query = '';
        switch($depth){
            case 0:
                $query = '//row';
                break;
            case 1:
                $query = '//row/*/row';
                break;
            case 2:
                $query = '//row/*/row/*/row';
                break;
        }
        return $query;
    }

    private function getQueryColumnXpath(int $depth): string{
        $query = '';
        switch($depth){
            case 0:
                $query = '//col';
                break;
            case 1:
                $query = '//col/*/col';
                break;
            case 2:
                $query = '//col/*/col/*/col';
                break;
        }
        return $query;
    }
    function loadTemplateVariables(): void{
       //to do
    }

    function loadGlobalVariables(): void{
        $this->setGlobalVar('locales',Marion::getConfig('locale','supportati'));
        $this->setGlobalVar('activelocale',_MARION_LANG_);
     }

    function loadTemplateFunctions(): void{
		$this->_twig_functions[] = new TwigFunction('debugga', function ($var,string $label='') {
			debugga($var,$label);
		});
        $this->_twig_functions[] = new TwigFunction('auth', function ($type) {
			return Marion::auth($type);
		});
		$this->_twig_functions[] = new TwigFunction('okArray', function ($array) {
			return okArray($array);
		});
		$this->_twig_functions[] = new TwigFunction('tr', function ($string,$module=null) {
			return _translate($string,$module);
		});
		$this->_twig_functions[] = new TwigFunction('getConfig', function ($group=NULL,$key=null,$value=null) {	
			return Marion::getConfig($group,$key,$value);
		});
	}
}