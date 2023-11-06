<?php
namespace Marion\Support\Form;

use Closure;
use DOMElement;
use Faker\Provider\Uuid;
use Marion\Controllers\Controller;
use Marion\Controllers\Interfaces\TabAdminInterface;
use Marion\Core\Marion;
use Marion\Support\Form\FormData;

class FormHelper {
    /**
     * Form container layout
     *
     * @var string
     */
    public string $container_layout = '@core/layouts/base_form.htm';
    
    /**
     * form id
     *
     * @var string
     */
    private string $id;

    /**
     * instance id
     *
     * @var string
     */
    private string $instance_id;

    /**
     * model id
     *
     * @var string
     */
    private string $form_id = 'id'; //id del model del form
    
    
    /**
     * confirm delete message
     *
     * @var string
     */
    private string $confirm_delete_message = 'Are you sure you want to delete this item?';
    
    /**
     * hide delete button on action "edit"
     *
     * @var boolean
     */
    private bool $hide_delete_button = true;


    /**
     * Form parent id
     *
     * @var string
     */
    private string $parent_id;

    /**
     * controller associate to FormHelper
     *
     * @var Controller
     */
    public Controller $ctrl;

    
    /**
     * Form fields
     *
     * @var array
     */
    public array $fields; 

    /**
     * Form errors
     *
     * @var array
     */
    public array $errors; 

    /**
     * Form error fields
     *
     * @var array
     */
    public array $error_fields; 

    /**
     * Text submit button
     *
     * @var string
     */
    private string $text_submit_button;

    /**
     * Icon submit button
     *
     * @var string
     */
    private string $icon_submit_button;


    /**
     * Contiene gli eventi triggerati dal form tramite ajax
     *
     * @var array
     */
    private array $ajax_trigger_events = [];



    private array $on_change_regex_functions = [];
    private array $on_change_functions = [];
    private array $on_event_functions = [];
    private array $changed_fields = [];
    private array $hidden_elements = [];
    private array $showed_elements = [];
    private array $disabled_fields = [];
    private array $enabled_fields = [];

    private string $focused_field;

    private array $fragments = [];
    private array $added_ajax_fragments = [];
    private array $deleted_ajax_fragments = [];
    private array $replaced_ajax_fragments = [];


    /**
     * layout form string
     *
     * @var string
     */
    private string $layout = '<xml></xml>';

    /**
     * Path layout file
     *
     * @var string
     */
    private string $layout_file;
    public array $updates = [];
    public static $elements = [];

    private array $validated_data;

    public static array $instances = []; 

    


    public Closure $validation_function;
    public Closure $after_submit_function;
    public Closure $init_function;
    public Closure $delete_function;
    public Closure $confirm_delete_message_function;


    public Formdata $formData;


    public function __call($method, $args)
	{
        //debugga($method);exit;
        if( $method === 'onChange'){
            if( isset($this->parent_id) ){
                self::$instances[$this->parent_id]->on_change_regex_functions[$args[0]] = [ 
                    'form' => $this,
                    'closure' => $args[1]
                ];
            }else{
                $this->on_change_regex_functions[$args[0]] = [ 
                    'form' => $this,
                    'closure' => $args[1]
                ];
            }
            
            return $this;
        }elseif( preg_match('/onChange/',$method)){
            $field = explode('onChange',$method)[1];
            $field = lcfirst($field);
            $field = preg_replace("/[A-Z]/", '_' . "$0", $field);
            $field = strtolower($field);
           
            if( isset($this->parent_id) ){
                self::$instances[$this->parent_id]->on_change_functions[$field] = [ 
                    'form' => $this,
                    'closure' => $args[0]
                ];
            }else{
                $this->on_change_functions[$field] = [ 
                    'form' => $this,
                    'closure' => $args[0]
                ];
            }
            return $this;
        }else{
            if( preg_match('/on/',$method)){
                $event = preg_replace('/^on/','',$method);
                $event = lcfirst($event);
                $event = preg_replace("/[A-Z]/", '_' . "$0", $event);
                $event = strtolower($event);
                
                if( isset($this->parent_id) ){
                    self::$instances[$this->parent_id]->on_event_functions[$event] = [ 
                        'form' => $this,
                        'closure' => $args[0]
                    ];
                }else{
                    $this->on_event_functions[$event] =[ 
                        'form' => $this,
                        'closure' => $args[0]
                    ];
                }
            return $this;
            }
            throw new \Exception("Method not exists");
        }


		
	}

    public function __construct(){
        $this->fields = [];
        $this->errors = [];
        $this->error_fields = [];
        $this->formData = new FormData;
        $this->instance_id = _var('form_instance_id')?_var('form_instance_id'):uniqid();
    }

    function setId(string $id){
        $this->id = $id;
    }

    function setFormId(string $form_id): self{
        $this->form_id = $form_id;
        return $this;
    }

    function setFields(array $fields): self{
        $this->fields = $fields;
        return $this;
    }


    /**
     * Set text submit button
     *
     * @param string $text
     * @return self
     */
    function setTextSubmitButton(string $text): self{
        $this->text_submit_button = $text;
        return $this;
    }

    /**
     * Set icon submit button
     *
     * @param string $text
     * @return self
     */
    function setIconSubmitButton(string $icon): self{
        $this->icon_submit_button = $icon;
        return $this;
    }


    function hideDeleteButton(): self{
        $this->hide_delete_button = true;
        return $this;
    }
    function showDeleteButton(): self{
        $this->hide_delete_button = true;
        return $this;
    }


    /**
     * Validation function
     *
     * @param Closure $function
     * @return self
     */
    function validate(Closure $function): self{
        $this->validation_function = $function;
        return $this;
    }

    function process(Closure $function): self{
        $this->after_submit_function = $function;
        return $this;
    }

    function next($prams = null){
        debugga($this);exit;
    }

    function init(Closure $function): self{
        $this->init_function = $function;
        return $this;
    }

    function setConfirmDeleteMessage(Closure $function): self{
        $this->confirm_delete_message_function = $function;
        return $this;
    }

    function onDelete(Closure $function): self{
        $this->delete_function = $function;
        return $this;
    }


    public static function create(string $id, Controller $ctrl){
        $obj = new FormHelper();
        $obj->setId($id);
        $obj->ctrl=$ctrl;
        self::$instances[$id] = $obj;
        Marion::do_action('action_extend_form_'.$id);
        return $obj;
    }

    public static function get(string $id): ?FormHelper{
        if( isset(self::$instances[$id]) ){
            return self::$instances[$id];
        }
        return null;
        
    }

    public static function update(string $id){
        $obj = new FormHelper();
        $obj->parent_id = $id;
        $obj->instance_id = self::$instances[$id]->instance_id;
        self::$instances[$id]->updates[] = $obj;
        return $obj;
    }


    public function getDataForm(): array{
       
        $this->formData->setFields($this->getAllFields());
		$dataform = $this->formData->prepare();
        return $dataform;
    }


    public function getAllFields(): array{
        $fields = $this->fields;
        if( okArray($this->updates) ){

            foreach($this->updates as $form){
                $fields = array_merge($fields,$form->fields);
            }
        }
        return $fields;
    }


    public function layoutFile(string $file): self{
        $this->layout_file = $file;
        return $this;
    }

    public function layoutString(string $layout): self{
        $this->layout = $layout;
        return $this;
    }


    private function getDOMDocument(): \DOMDocument{
        $dom = new \DOMDocument;
        $dom->encoding = 'utf-8';
        if( isset($this->layout_file) ){
            if( !file_exists($this->layout_file) ) throw new \Exception($this->layout_file. " not exists");
            
            $dom->load($this->layout_file);
        }else{
            $dom->loadXML($this->layout);
        }
       
        
        return $dom;
    }

    /**
     * add field to changed fields for redraw
     *
     * @param string|array $field
     * @return void
     */
    function changeField($field){
        
        $form_id = isset($this->parent_id)? $this->parent_id: $this->id;
        if( is_array($field) ){
            foreach($field as $_field){
                $_SESSION['override_form_fields'][$form_id][$this->instance_id][$_field] = $this->fields[$_field];
            }
            $this->changed_fields = array_merge($this->changed_fields,$field);
        }else{
            $_SESSION['override_form_fields'][$form_id][$this->instance_id][$field] = $this->fields[$field];
            $this->changed_fields[] = $field;
        }

    }

    /**
     * add field to hide fields for redraw
     *
     * @param string|array $field
     * @return void
     */
    function hideElement($element_id){
        if( is_array($element_id) ){
            $this->hidden_elements = array_merge($this->hidden_elements,$element_id);
        }else{
            $this->hidden_elements[] = $element_id;
        }
 
        if( in_array($element_id,$this->showed_elements) ){
            $this->showed_elements = array_filter($this->showed_elements, static function ($element) use ($element_id) {
                return $element !== $element_id;
            });
        }
        $_SESSION['form_hidden_elements'][$this->id] = $this->hidden_elements;
        $_SESSION['form_showed_elements'][$this->id] = $this->showed_elements;
    }

    /**
     * add field to hide fields for redraw
     *
     * @param string|array $field
     * @return void
     */
    function showElement($element_id){
        if( is_array($element_id) ){
            $this->showed_elements = array_merge($this->showed_elements,$element_id);
        }else{
            $this->showed_elements[] = $element_id;
        }
        if( in_array($element_id,$this->hidden_elements) ){
            $this->hidden_elements = array_filter($this->hidden_elements, static function ($element) use ($element_id) {
                return $element !== $element_id;
            });
        }
        
        $_SESSION['form_hidden_elements'][$this->id] = $this->hidden_elements;
        $_SESSION['form_showed_elements'][$this->id] = $this->showed_elements;
    }

    /**
     * add field to hide fields for redraw
     *
     * @param string|array $field
     * @return void
     */
    function hideField($field){
        $field = "div_".$field;
        $this->hideElement($field);
    }

     /**
     * add field to hide fields for redraw
     *
     * @param string|array $field
     * @return void
     */
    function showField($field){
        $field = "div_".$field;
        $this->showElement($field);
    }

    /**
     * add field to hide fields for redraw
     *
     * @param string|array $field
     * @return void
     */
    function focusField($field){
        $this->focused_field = $field;
    }


    /**
     * add field to disabled fields for redraw
     *
     * @param string|array $field
     * @return void
     */
    function disableField($field){
        if( is_array($field) ){
            $this->disabled_fields = array_merge($this->disabled_fields,$field);
        }else{
            $this->disabled_fields[] = $field;
        }
 
        if( in_array($field,$this->enabled_fields) ){
            $this->enabled_fields = array_filter($this->enabled_fields, static function ($element) use ($field) {
                return $element !== $field;
            });
        }
        $_SESSION['form_disabled_fields'][$this->id] = $this->disabled_fields;
        $_SESSION['form_enabled_fields'][$this->id] = $this->enabled_fields;
    }

    /**
     * add field to disabled fields for redraw
     *
     * @param string|array $field
     * @return void
     */
    function enableField($field){
        if( is_array($field) ){
            $this->enabled_fields = array_merge($this->enabled_fields,$field);
        }else{
            $this->enabled_fields[] = $field;
        }
 
        if( in_array($field,$this->disabled_fields) ){
            $this->disabled_fields = array_filter($this->disabled_fields, static function ($element) use ($field) {
                return $element !== $field;
            });
        }
        $_SESSION['form_disabled_fields'][$this->id] = $this->disabled_fields;
        $_SESSION['form_enabled_fields'][$this->id] = $this->enabled_fields;
    }



    public function isSubmitted(): bool{
        return _var('_sumbitted_form')?true:false;
    }

    public function validation(){
        if( $this->isSubmitted() ){

            //validation form
            $this->formData->setFields($this->getAllFields());
            foreach($this->fragments as $key => $fragments){
                foreach($fragments as $f){
                    $f->prepareForm();
                    
                    $this->formData->fields = array_merge($this->formData->fields,$f->formData->fields);
                }
            }
            if( $this->formData->validate($this->getSubmittedData()) ){
                $this->validated_data = $this->formData->validated_data;

                if( isset($this->validation_function) ){
                    call_user_func($this->validation_function,$this);
                }
                foreach($this->updates as $form){
                    if( isset($form->validation_function) ){
                        call_user_func($form->validation_function,$this,$form);
                    }
                }
                $errors = $this->getErrors();
                if( !okArray($errors) ){
                    if( isset($this->after_submit_function) ){
                        call_user_func($this->after_submit_function,$this);
                    }
                    foreach($this->updates as $form){
                        if( isset($form->after_submit_function) ){
                            call_user_func($form->after_submit_function,$this,$form);
                        }
                    }
                    foreach($this->fragments as $wrapper_id => $fragments){
                        foreach($fragments as $ind => $f){
                            $this->fragments[$wrapper_id][$ind]->setDataForm($this->getSubmittedData()); 
                        }
                    }
                }
            }else{
                $this->errors = $this->formData->errors;
                $this->error_fields = $this->formData->error_fields;
                foreach($this->fragments as $wrapper_id => $fragments){
                    
                    foreach($fragments as $ind => $f){
                        $this->fragments[$wrapper_id][$ind]->setDataForm($this->getSubmittedData()); 
                        $this->fragments[$wrapper_id][$ind]->formData->error_fields = $this->error_fields;
                    }
                }
            }

            
        }
    }

    private function eventForm(){
        $event = _var('event');
        $formdata = _formdata();

        $this->formData->data = $formdata;
        $_params = _var('params');
        $params = [];
        if( $_params ){
            foreach($_params as $p){
                if( is_string($p) ){
                  
                    $decode = json_decode($p,true);
                    if( json_last_error() === JSON_ERROR_NONE ){
                        $params[] = $decode;
                    }else{
                        $params[] = $p;
                    }
                }else{
                    $params[] = $p;
                }
            }
        }
        

        
       
        if( array_key_exists($event,$this->on_event_functions) ){
            $callable = $this->on_event_functions[$event]['closure'];
            $form = $this->on_event_functions[$event]['form'];
            call_user_func($callable,$form,$params);
        }
        $this->toJson();
    }

    private function changeForm(){
        
        $field = _var('field');
        $value = _var('value');
        
        $other_data = [];
        if( isset($_GET['checked'] )){
            $other_data['checked'] = $_GET['checked'];
        }
        $regex = _var('regex');
        $formdata = _formdata();
        
        $this->formData->data = $formdata;

        if( $regex ){
            if( okArray($this->on_change_regex_functions) ){
                foreach(array_keys($this->on_change_regex_functions) as $_regex ){
                    if( preg_match("/{$_regex}/",$field) ){
                        $callable = $this->on_change_regex_functions[$_regex];
                        call_user_func($callable,$this,$field,$value,$formdata,$other_data);
                    }
                }
            }
        }else{
            if( array_key_exists($field,$this->on_change_functions) ){
                $callable = $this->on_change_functions[$field]['closure'];
                $form =  $this->on_change_functions[$field]['form'];
                call_user_func($callable,$form,$value,$formdata,$other_data);
            }
        }
        
        $this->toJson();
       
    }


    private function toJson(){
        $dataform = $this->getDataForm();
        $data_html = [];
        $data_events = [];
        $new_fields = [];
        $data_fragments = [];
        
        foreach($this->changed_fields as $_field){
            //$new_fields[] = $_field; //per i campi modificati occorre ricaricare i listener per l change
            $fragment = new Fragment(uniqid(),$this->ctrl);
            $fragment->setFields([
                $_field => $this->fields[$_field]
            ]);
            $fragment->setTemplate("<fragment><field name='{$_field}'/></fragment>");
            $fragment->setDataForm($this->formData->data);

            $html_element = $fragment->build();
            

            $js_libreries = $dataform[$_field]['js_libraries'];
            if(
                ($dataform[$_field]['type'] == 'multiselect' && !okArray($js_libreries)) ||
                $dataform[$_field]['type'] == 'select'
            ){
                $html_element .= "<script>$('#{$_field}').selectpicker();</script>";
            }
            

            $data_html[$_field]['html'] = $html_element;
            $data_html[$_field]['js'] = $fragment->js_assets;
            $data_html[$_field]['css'] = $fragment->css_assets;
        }
        if( okArray($this->updates) ){
            foreach( $this->updates as $update){
                foreach($update->changed_fields as $_field){
                    //$new_fields[] = $_field; //per i campi modificati occorre ricaricare i listener per l change
                    $fragment = new Fragment(uniqid(),$this->ctrl);
                    $fragment->setFields([
                        $_field => $update->fields[$_field]
                    ]);
                    $fragment->setTemplate("<fragment><field name='{$_field}'/></fragment>");
                    if( isset($update->formData->data) ){
                        $fragment->setDataForm($update->formData->data);
                    }            
        
                    $html_element = $fragment->build();
                    
        
                    $js_libreries = $dataform[$_field]['js_libraries'];
                    if(
                        ($dataform[$_field]['type'] == 'multiselect' && !okArray($js_libreries)) ||
                        $dataform[$_field]['type'] == 'select'
                    ){
                        $html_element .= "<script>$('#{$_field}').selectpicker();</script>";
                    }
                    $data_html[$_field]['html'] = $html_element;
                    $data_html[$_field]['js'] = $fragment->js_assets;
                    $data_html[$_field]['css'] = $fragment->css_assets;
                }
            }
        }

        //debugga($this->updates);exit;
        
       
        
        foreach($this->added_ajax_fragments as $wrapper_id => $_fragments){
                foreach($_fragments as $f){
                    $new_fields = array_merge($new_fields,array_keys($f->getFields()));
                    $_html = $f->build();
                    $data_fragments[] = [
                        'action' => 'append',
                        'wrapper_id' => $wrapper_id,
                        'html' => $_html,
                        'js' => $f->js_assets,
                        'css' => $f->css_assets,
                    ];
            }
        }
        
        if( okArray($this->updates) ){
            foreach( $this->updates as $update){
                foreach($update->added_ajax_fragments as $wrapper_id => $_fragments){
                    foreach($_fragments as $f){
                        $new_fields = array_merge($new_fields,array_keys($f->getFields()));
                        $_html = $f->build();
                        $data_fragments[] = [
                            'action' => 'append',
                            'wrapper_id' => $wrapper_id,
                            'html' => $_html,
                            'js' => $f->js_assets,
                            'css' => $f->css_assets,
                        ];
                    }
                }
            }
        }
        
        foreach($this->replaced_ajax_fragments as $fragment_id => $_fragments){
            foreach($_fragments as $f){
                $new_fields = array_merge($new_fields,array_keys($f->getFields()));
                $_html = $f->build();
                $data_fragments[] = [
                    'action' => 'replace',
                    'wrapper_id' => $fragment_id,
                    'html' => $_html,
                    'js' => $f->js_assets,
                    'css' => $f->css_assets,
                ];
            }
        }
        if( okArray($this->updates) ){
            foreach( $this->updates as $update){
                foreach($update->replaced_ajax_fragments as $fragment_id => $_fragments){
                    foreach($_fragments as $f){
                        $new_fields = array_merge($new_fields,array_keys($f->getFields()));
                        $_html = $f->build();
                        $data_fragments[] = [
                            'action' => 'replace',
                            'wrapper_id' => $fragment_id,
                            'html' => $_html,
                            'js' => $f->js_assets,
                            'css' => $f->css_assets,
                        ];
                    }
                }
            }
        }

        $field_listeners = array_filter(array_keys($this->on_change_functions), function($field) use ($new_fields){
            if( in_array($field, $new_fields) ) return true;
            return false;
        });
       
        $regex_listeners = array_keys($this->on_change_regex_functions);
       
        if( okArray($regex_listeners)){
            foreach($regex_listeners as $regex){
                foreach(array_unique($new_fields) as $field ){
                    $match = "/{$regex}/";
                    if( preg_match($match,$field) ){
                       $field_change_listeners[] = $field;
                    }
                }
            }
        }
        if( okArray($this->updates) ){
            foreach( $this->updates as $update){
                $regex_listeners = array_keys($update->on_change_regex_functions);
       
                if( okArray($regex_listeners)){
                    foreach($regex_listeners as $regex){
                        foreach(array_unique($new_fields) as $field ){
                            $match = "/{$regex}/";
                            if( preg_match($match,$field) ){
                            $field_change_listeners[] = $field;
                            }
                        }
                    }
                }
            }
        }

       


        foreach($this->ajax_trigger_events as $_data){
            $_params = $_data['params'];
            $data_events[] = [
                'event' => $_data['event'],
                'params' => $_params,
                'parent_form' =>  $_data['parent_form']
            ];
        }

        foreach($this->deleted_ajax_fragments as $_fragment_id){    
            $data_fragments[] = [
                'action' => 'delete',
                'fragment_id' => $_fragment_id
            ];
        }

        if( okArray($this->updates) ){
            foreach( $this->updates as $update){
                foreach($update->deleted_ajax_fragments as $_fragment_id){    
                    $data_fragments[] = [
                        'action' => 'delete',
                        'fragment_id' => $_fragment_id
                    ];
                }
            }
        }
        
        
        
        
        echo json_encode([
            'data'=> [
                    'focused_field' => isset($this->focused_field)?$this->focused_field: '',
                    'override_fields' => $data_html,
                    'hidden_elements' => $this->hidden_elements,
                    'showed_elements' => $this->showed_elements,
                    'enabled_fields' => $this->enabled_fields,
                    'disabled_fields' => $this->disabled_fields,
                    'fragments' => $data_fragments,
                    'field_change_listeners' => isset($field_change_listeners)?$field_change_listeners:[],
                    'field_listeners' => isset($field_listeners)?$field_listeners:[],
                    'events' => $data_events
                ]
            ]
        );
        exit;
    }

    public function initialize(){

        if(  _var('form_instance_id') ){
            $this->instance_id = _var('form_instance_id');
        }
       
        $_change_value = _var('_change_value');
        $_form_event = _var('_form_event');
       
        
        if( $_change_value || $_form_event ){
            try{
                if( isset($_SESSION['form_fragments'][$this->id][$this->instance_id]) ){
                    
                    foreach($_SESSION['form_fragments'][$this->id][$this->instance_id] as $wrapper_id => $_fragments){
                        foreach($_fragments as $_f){
                            $this->fields = array_merge($this->fields,$_f['fields']);
                        }
                    }
            
                }
                if($_change_value ){
                    $this->changeForm();
                }
                if($_form_event ){
                    $this->eventForm();
                }
            }catch (\Exception $e) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array(
                    'error' => array(
                        'msg' => $e->getMessage(),
                        'code' => $e->getCode(),
                    ),
                ));
                exit;
            }
        }

        
        

        

        
        

        
        
        if($this->isSubmitted()){
            //debugga($this->instance_id);
            //debugga($_SESSION['form_fragments'][$this->id]);exit;
            if( isset($_SESSION['form_fragments'][$this->id][$this->instance_id]) ){
               
                foreach($_SESSION['form_fragments'][$this->id][$this->instance_id] as $wrapper_id => $_fragments){

                   
                    foreach($_fragments as $_f){
                        $fragment = new Fragment($_f['id'],$this->ctrl);
                        $fragment->setTemplate($_f['template_string']);
                        $fragment->setFields($_f['fields']);
                        $this->addFragment($wrapper_id,$fragment);
                    }
                }
               
            }
            
            if( isset($_SESSION['override_form_fields'][$this->id][$this->instance_id]) ){
                foreach($_SESSION['override_form_fields'][$this->id][$this->instance_id] as $field => $value){
                    $this->fields[$field] = $value;
                }
                
            }
            if( isset($_SESSION['form_hidden_elements'][$this->id][$this->instance_id]) ){
                foreach($_SESSION['form_hidden_elements'][$this->id][$this->instance_id] as $value){
                    $this->hidden_elements[] = $value;
                }
            }
            if( isset($_SESSION['form_showed_elements'][$this->id][$this->instance_id]) ){
                foreach($_SESSION['form_showed_elements'][$this->id][$this->instance_id] as $value){
                    $this->showed_elements[] = $value;
                }
            }
            if( isset($_SESSION['form_disabled_fields'][$this->id][$this->instance_id]) ){
                foreach($_SESSION['form_disabled_fields'][$this->id][$this->instance_id] as $value){
                    $this->disabled_fields[] = $value;
                }
            }
            if( isset($_SESSION['form_enabled_fields'][$this->id][$this->instance_id]) ){
                foreach($_SESSION['form_enabled_fields'][$this->id][$this->instance_id] as $value){
                    $this->enabled_fields[] = $value;
                }
            }
        }else{
            if( isset($_SESSION['form_fragments'][$this->id][$this->instance_id])){
                unset($_SESSION['form_fragments'][$this->id][$this->instance_id]);
            }
            
            if( isset($_SESSION['override_form_fields'][$this->id][$this->instance_id]) ){
                unset($_SESSION['override_form_fields'][$this->id][$this->instance_id]);
            }
            if( isset($_SESSION['form_hidden_elements'][$this->id][$this->instance_id]) ){
                unset($_SESSION['form_hidden_elements'][$this->id][$this->instance_id]);
            }
            if( isset($_SESSION['form_showed_elements'][$this->id][$this->instance_id]) ){
                unset($_SESSION['form_showed_elements'][$this->id][$this->instance_id]);
            }
            if( isset($_SESSION['form_enabled_fields'][$this->id][$this->instance_id]) ){
                unset($_SESSION['form_enabled_fields'][$this->id][$this->instance_id]);
            }
            if( isset($_SESSION['form_disabled_fields'][$this->id][$this->instance_id]) ){
                unset($_SESSION['form_disabled_fields'][$this->id][$this->instance_id]);
            }
        }
        
        if( isset($this->confirm_delete_message_function) ){
            if( $this->isSubmitted() ){
                $submitted_data = $this->getSubmittedData();
                $id = array_key_exists($this->form_id,$submitted_data)?$submitted_data[$this->form_id]:null;    
            }else{
                $id = _var('id');
            }
            $this->confirm_delete_message = call_user_func($this->confirm_delete_message_function,$id);
        }
        
        if( isset($this->delete_function) && _var('_delete_action') ){
            call_user_func($this->delete_function,_var('id'));
        }
        if( isset($this->init_function) ){
            call_user_func($this->init_function,$this);
        }

        if( okArray($this->updates) ){
            foreach($this->updates as $form){
                if( isset($form->init_function) ){
                    call_user_func($form->init_function,$this,$form);
                }
            }
        }
    }

    public function getErrors(): array{
        $errors = $this->errors;
        foreach($this->updates as $form){
            if( okArray($form->errors) ){
                $errors = array_merge($errors,$form->errors);
            }
        }
        return $errors;
    }

    public function getErrorFields(): array{
        $error_fields = $this->error_fields;
        foreach($this->updates as $form){
            if( okArray($form->error_fields) ){
                $error_fields = array_merge($error_fields,$form->error_fields);
            }
        }
        return $error_fields;
    }

    public function getSubmittedData(): array{
        return _var('formdata')?_var('formdata'):[];
    }

    public function getValidatedData(): array{
        return $this->validated_data?$this->validated_data:[];
    }

    /**
     * Trigger event ajax
     *
     * @param string $event
     * @param mixed $params
     * @param boolean $parent_form
     * @return void
     */
    public function triggerEvent(string $event,mixed $params = null, bool $parent_form = false){
        $this->ajax_trigger_events[] = [
                'event' => $event,
                'params' =>   $params,
                'parent_form' => $parent_form
        ];
    }

    /**
     * Trigger event "close popup"
     *
     * @param mixed $params
     * @return void
     */
    public function closePopup(mixed $params = null){
        $this->triggerEvent('close_popup_form',$params);
    }

    public function displayPopup(){
        $this->container_layout = '@core/layouts/base_form_widget.htm';
        $this->display();
    }

    public function display(){
        if ($this->ctrl instanceof TabAdminInterface) {
            $this->container_layout = '@core/layouts/tab/base_form.htm';
        }
        $this->initialize();
        
        $this->validation();
        //debugga($this->fields);exit;
        
        $errors = $this->getErrors();
        $error_fields = $this->getErrorFields();
        if( okArray($error_fields) ){
            $this->formData->error_fields = $error_fields;
        }
        $this->ctrl->errors = array_merge($this->ctrl->errors,$errors);
        //aggiungere errori degli altri form
        $html = $this->getTemplateString();
        
        $dataform = $this->getDataForm();

       

        $field_listeners = array_keys($this->on_change_functions);
        $regex_listeners = array_keys($this->on_change_regex_functions);
        
        if( okArray($regex_listeners)){
            foreach($regex_listeners as $regex){
                foreach(array_keys($this->fields) as $field ){
                    
                    $match = "/{$regex}/";
                    
                    if( preg_match($match,$field) ){
                       $field_change_listeners[] = $field;
                    }
                }
            }
        }
        

        if( $this->ajax_trigger_events ){
            $this->ctrl->setVar('events',json_encode($this->ajax_trigger_events));
        }

        if( isset($field_listeners) ){
            $this->ctrl->setVar('field_listeners',json_encode($field_listeners));
        }
        
        if( isset($field_change_listeners) ){
            $this->ctrl->setVar('field_change_listeners',json_encode($field_change_listeners));
        }
        


        $this->loadJS($dataform);
        
        if( isset($this->text_submit_button) ){
            $this->ctrl->setVar('text_submit_button',$this->text_submit_button);
        }

        if( isset($this->icon_submit_button) ){
            $this->ctrl->setVar('icon_submit_button',$this->icon_submit_button);
        }
        $action = _var('action');
        if(!preg_match('/_delete_action/',$this->ctrl->getUrlCurrent())){
           
            if( $this->isSubmitted() ){
                $id = isset($this->formData->data[$this->form_id])?$this->formData->data[$this->form_id]:null;    
            }else{
                $id = _var('id');
            }
            $this->ctrl->setVar('url_confirm_delete',$this->ctrl->getUrlScript()."&action={$action}&id={$id}=&_delete_action=1");
        }
        $this->ctrl->setVar('url_change_field',$this->ctrl->getUrlScript()."&action={$action}&_change_value=1");
        $this->ctrl->setVar('url_form_event',$this->ctrl->getUrlScript()."&action={$action}&_form_event=1");
        if( $action == 'duplicate' ){
            $this->ctrl->setVar('action',$action);
        }
        $this->ctrl->setVar('confirm_delete_message',$this->confirm_delete_message);
        $this->ctrl->setVar('hide_delete_button',$this->hide_delete_button);
        $this->ctrl->setVar('hidden_elements',json_encode($this->hidden_elements));
        $this->ctrl->setVar('showed_elements',json_encode($this->showed_elements));
        $this->ctrl->setVar('enabled_fields',json_encode($this->enabled_fields));
        $this->ctrl->setVar('disabled_fields',json_encode($this->disabled_fields));
        $this->ctrl->setVar('form_instance_id',$this->instance_id);
        $this->ctrl->setVar('dataform',$dataform);
        $this->ctrl->setVar('js_formdata',isset($this->formData->data)?$this->formData->data:[]);
        $this->ctrl->outputString($html);
    }

    private function loadJS(array $dataform){
       
        $js_librieries = [];
        foreach($dataform as $f){
            if( isset($f['js_libraries']) && okArray($f['js_libraries'])){
                $js_librieries = array_merge($js_librieries,$f['js_libraries']);
            }
        }
        if( okArray($js_librieries)){
            $js_librieries = array_unique($js_librieries);
            foreach($js_librieries as $v){
                $this->ctrl->loadJS($v);
            }
        }
       
    }

    public function xml(): string{
        $dom = $this->getDOMDocument();
        if( okArray($this->updates) ){
            foreach($this->updates as $obj){
                $_dom = $obj->getDOMDocument();
                $_dom_xpath = new \DOMXPath($_dom);
                $xpaths = $_dom_xpath->query("//xpath");
                foreach($xpaths as $_xpath){
                    $expr = $_xpath->getAttribute('expr');
                    $position = $_xpath->getAttribute('position');
                    
                    $content = $this->getContetHmtl($_xpath);
                  
                    $fragment = $dom->createDocumentFragment();
                    $fragment->appendXML($content);
                    
                    $xpath = new \DOMXPath($dom);
                    $items = $xpath->query($expr);
                    
                    foreach ($items as $child){
                        $parent = $child->parentNode;
                        switch($position){
                            case 'replace';
                                $parent->replaceChild($fragment,$child);
                                break;
                            case 'inside';
                                $child->appendChild($fragment);
                                break;
                            case 'before';
                                $parent->insertBefore($fragment,$child);
                                break;
                            case 'after';
                                if( $child->nextSibling === null ){
                                    $parent->appendChild( $fragment );
                                }else{
                                    $parent->insertBefore($fragment,$child->nextSibling);
                                }
                                break;
                            case 'remove':
                                $parent->removeChild($child);
                                break;
                        }
                        
                    }
                }
            }
        }
        return $dom->saveXML();
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


    public function getTemplateString(): string{
        $form = $this->getFormHtml();
        $form = preg_replace('/<t-fragment if=\"(.*)\%}">/',' {% if $1 %} ',$form);
        $form = preg_replace('/<\/t-fragment>/',' {% endif %} ',$form);
       
        $html = "{% extends '{$this->container_layout}' %} \n";
        $html .= "{% block content %} \n";
        $html .= "{% import 'macro/form.htm' as form %} \n";
        $html .= $form." \n";
        $html .= "<input type='hidden' name='_sumbitted_form' value='{$this->id}'>";
        $html .= "{% endblock %}";
        return $html;
    }

    public function getFormHtml(): string{
        $xml = $this->xml();
        
        $dom = new \DOMDocument;
        $dom->encoding = 'utf-8';
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($xml);
        $dom_xpath = new \DOMXPath($dom);

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

                $condition_if = $this->getIfConditions($current);
                $html = '';
                if( $condition_if ){
                    $html .= "<t-fragment if='{$condition_if}'>";
                }
                
                $html .= "<div {$this->stringfyAttributes($current,['class'=>'row'])}>";
                $html .= $this->getContetHmtl($current);
                $html .= "</div>";

                if( $condition_if ){
                    $html .= "</t-fragment>";
                }
                
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

                $condition_if = $this->getIfConditions($current);
                $html = '';
                if( $condition_if ){
                    $html .= "<t-fragment if='{$condition_if}'>";
                }
                $html .= "<div {$this->stringfyAttributes($current,['class'=>"col-md-{$size}"])}>";
                $html .= $this->getContetHmtl($current);
                $html .= "</div>";

                if( $condition_if ){
                    $html .= "</t-fragment>";
                }
            
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
                $html = "{{form.build(dataform.{$name})}}";
            }else{
                $html = "{{form.buildCol(dataform.{$name})}}";
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
            
            $html = PHP_EOL.'<div '." {$this->stringfyAttributes($current,['class'=>"tabcordion"])}>".PHP_EOL.'<ul id="'.$tabs_name.'" class="nav nav-tabs">'.PHP_EOL;
            foreach($tabs as $ind => $tab){
                $module = $this->ctrl->_module;
               
                $name = $tab->getAttribute('name');
                $title = $tab->getAttribute('title');
                $name_slug = Marion::slugify($name);
                $active = ($ind == 0)?'active':'';
                if( preg_match('/:/',$title) ){
                    $explode_title = explode(':',$title);
                    $module = $explode_title[0];
                    $title = $explode_title[1];
                }
                if($module){
                    if( $title ){
                        $translated_name = _translate($title,$module);
                    }else{
                        $translated_name = _translate($name,$module);    
                    }
                }else{
                    if( $title ){
                        $translated_name = _translate($title);
                    }else{
                        $translated_name = _translate($name);
                    }
                }
                $condition_if = $this->getIfConditions($tab);
                
                if( $condition_if ){
                    $html.= "<t-fragment if='{$condition_if}'>";
                }
                $html.= "<li {$this->stringfyAttributes($tab,['class'=> $active])}><a  data-toggle='tab' href='#{$name_slug}'>{$translated_name}</a></li>".PHP_EOL;
                if( $condition_if ){
                    $html.= "</t-fragment>";
                }
            }
            $html .= '</ul>'.PHP_EOL.'<div id="'.$tabs_name.'Content" class="tab-content">'.PHP_EOL;

            foreach($tabs as $ind => $tab){
                $active = ($ind == 0)?'active':'';
                $name = Marion::slugify($tab->getAttribute('name'));
                $content = $this->getContetHmtl($tab);
                $condition_if = $this->getIfConditions($tab);
                
                if( $condition_if ){
                    $html .= "<t-fragment if='{$condition_if}'>";
                }
                $html .= '<div class="tab-pane '.$active.' in" id="'.$name.'">'.$content."</div>".PHP_EOL;
                if( $condition_if ){
                    $html .= "</t-fragment>";
                }
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


            $condition_if = $this->getIfConditions($current);
                
            if( $condition_if ){
                $html.= "<t-fragment if='{$condition_if}'>";
            }
            $html = "<div {$this->stringfyAttributes($current,['class' => $classes])}>";
            $html .= $this->getContetHmtl($current);
            $html .= "</div>";
            if( $condition_if ){
                $html.= "</t-fragment>";
            }
            
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
            //$classes = $this->getClasses($current,"col-md-{$size} kanban");
            $classes = $this->getClasses($current,"kanban");
            
            $condition_if = $this->getIfConditions($current);    
            if( $condition_if ){
                $html.= "<t-fragment if='{$condition_if}'>";
            }
            $html = "<div {$this->stringfyAttributes($current,['class'=>$classes])}><div class='kanban-content'>";
            if( $translated_name ){
                $html .= "<h2>{$translated_name}</h2>";
            }
            $html .= $this->getContetHmtl($current);
            $html .= "</div></div>";

            if( $condition_if ){
                $html.= "</t-fragment>";
            }
            
        
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($html);
            $parent->replaceChild($fragment,$current);
        }


        //variabile necessaria per evitare duplicati dei fragments
        $added_fragments = [];
        foreach($this->fragments as $wrapper_id => $_fragments){
            
            $xpaths = $dom_xpath->query("//*[@id='$wrapper_id']");
            foreach($xpaths as $current){
                foreach($_fragments as $f){
                    if( in_array($f->getId(),$added_fragments)) continue;
                    $added_fragments[] = $f->getId();
                    $fragment = $dom->createDocumentFragment();
                    $_html = $f->build();
                    $_html = preg_replace('/\&/','&amp;',$_html);
                    $fragment->appendXML($_html);
                    $current->appendChild($fragment);
                    foreach($f->getFields() as $_id => $_field){
                        $this->fields[$_id]['type'] = $_field['type'];
                    }
                }
                
            }
        }
      
        $sheet = $dom_xpath->query("//sheet")[0];
        return $this->getContetHmtl($sheet);
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
        $html = preg_replace('/\&amp;/','&',$html);
        return $html;
    }


    private function getElementsByTagName(\DOMElement $element, string $tag): array{
        $children = $element->childNodes;
        $toreturn = [];
        foreach ($children as $child) { 
            if(isset($child->tagName) && $child->tagName == $tag ){
               $toreturn[] = $child;
            }
        }
        return $toreturn;
    }

    function getIfConditions($node): string{
        $attributes = $node->attributes;
        if( $attributes->length > 0 ){
            foreach($attributes as $attr){
                if($attr->name == 'if'){
                    return $attr->value." %}";
                }
            }
        }
        return '';
    }

    private function stringfyAttributes($node, $default_attributes = []){
        $attributes = $node->attributes;
        $_attr = "";
        if( $attributes->length > 0 ){
            foreach($attributes as $attr){
                if( array_key_exists($attr->name, $default_attributes) ){
                    $default_value = $default_attributes[$attr->name];
                    $_attr .= "{$attr->name}='{$default_value} {$attr->value}' ";
                    unset($default_attributes[$attr->name]);
                }else{
                    $_attr .= "{$attr->name}='{$attr->value}' ";
                }   
            }
        }
        if( okArray($default_attributes) ){
            foreach($default_attributes as $name => $value){
                $_attr .= "{$name}='{$value}' ";
            }
        }
        return $_attr;
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

    

    /**
     * Add Fragment to element
     *
     * @param string $wrapper_id
     * @param Fragment $fragment
     * @return void
     */
    public function addFragment(string $wrapper_id, Fragment $fragment){
        
        $fields = $fragment->getFields();
        $form_id = isset($this->parent_id)? $this->parent_id:$this->id;
        foreach($fields as $k => $field){
            if( isset($_SESSION['override_form_fields'][$form_id][$k])){
                $fields[$k] = $_SESSION['override_form_fields'][$form_id][$k];
            }
        }
        $fragment->setFields($fields);
        
        
        $this->fragments[$wrapper_id][] = $fragment;
        $this->added_ajax_fragments[$wrapper_id][] = $fragment;

       
       
        $_SESSION['form_fragments'][$form_id][$this->instance_id][$wrapper_id][$fragment->getId()] = [
            'id' => $fragment->getId(),
            'fields' => $fragment->getFields(),
            'template_string' => $fragment->getTemplateString()
        ];
        
    }


    function getCachedFragments(): array{
        $form_id = isset($this->parent_id)? $this->parent_id: $this->id;
        return isset($_SESSION['form_fragments'][$form_id][$this->instance_id])?$_SESSION['form_fragments'][$form_id][$this->instance_id]:[];
    }

    /**
     * Replace Fragment to element
     *
     * @param string $wrapper_id
     * @param Fragment $fragment
     * @return void
     */
    public function replaceFragment(string $fragment_id, Fragment $fragment){
    
        $new_fragments = [];
        foreach($this->getCachedFragments() as $wrapper_id => $_fragments){
            foreach($_fragments as $k => $fr){
                if( $fr['id'] == $fragment_id){
                   //$selected_wrapper_id = $wrapper_id;
                   $new_fragments[$wrapper_id][] = [
                        'id' => $fragment->getId(),
                        'fields' => $fragment->getFields(),
                        'template_string' => $fragment->getTemplateString()
                    ];
                }else{
                    $new_fragments[$wrapper_id][] = $fr;
                }
            }
        }
        $_SESSION['form_fragments'][$this->id][$this->instance_id] = $new_fragments;
        $this->replaced_ajax_fragments[$fragment_id][] = $fragment;

        
    }

    /**
     * Remove Fragment by ID
     *
     * @param string $fragment_id
     * @return void
     */
    public function removeFragmentById(string $fragment_id): void{
        foreach($this->fragments as $wrapper_id => $_fragments){
            foreach($_fragments as $k => $fr){
                if( $fr->getId() == $fragment_id){
                    unset($this->fragments[$wrapper_id][$k]);
                }
            }
        }

        foreach($this->added_ajax_fragments as $wrapper_id => $_fragments){
            foreach($_fragments as $k => $fr){
                if( $fr->getId() == $fragment_id){
                    unset($this->added_ajax_fragments[$wrapper_id][$k]);
                }
            }
            
        }

        $form_id = isset($this->parent_id)? $this->parent_id: $this->id;
        if( isset($_SESSION['form_fragments'][$form_id][$this->instance_id]) ){
            foreach($_SESSION['form_fragments'][$form_id][$this->instance_id] as $wrapper_id => $_fragments){
                foreach($_fragments as $_fragment ){
                    if($_fragment['id'] == $fragment_id ){
                        if( isset($_SESSION['form_fragments'][$form_id][$this->instance_id][$wrapper_id][$fragment_id]['fields']) ){
                            $fields = array_keys($_SESSION['form_fragments'][$form_id][$this->instance_id][$wrapper_id][$fragment_id]['fields']);
                            foreach($fields as $field){
                                if( isset($_SESSION['override_form_fields'][$form_id][$this->instance_id][$field])){
                                    unset($_SESSION['override_form_fields'][$form_id][$this->instance_id][$field]);
                                }
                            }
                        }
                        unset($_SESSION['form_fragments'][$form_id][$this->instance_id][$wrapper_id][$fragment_id]);
                    } 
                }
                
            }
        }
        $this->deleted_ajax_fragments[] = $fragment_id;
        
    }

    /**
     * remove all fragment in Form
     *
     * @return void
     */
    function removeAllFragments(): void{
       
        foreach($this->fragments as $wrapper_id => $_fragments){    
            foreach($_fragments as $k => $fr){
                unset($this->fragments[$wrapper_id][$k]);
                $this->deleted_ajax_fragments[] = $fr->getId();
            }
        }
        foreach($this->added_ajax_fragments as $wrapper_id => $_fragments){
            foreach($_fragments as $k => $fr){
                unset($this->added_ajax_fragments[$wrapper_id][$k]);
            }
        }
        $form_id = isset($this->parent_id)? $this->parent_id: $this->id;
        if( isset($_SESSION['form_fragments'][$form_id][$this->instance_id]) ){
            foreach($_SESSION['form_fragments'][$form_id][$this->instance_id] as $wrapper_id => $_fragments){
                foreach($_fragments as $_fragment ){
                    $this->deleted_ajax_fragments[] = $_fragment['id'];
                    if( isset($_SESSION['form_fragments'][$form_id][$this->instance_id][$wrapper_id][$_fragment['id']]['fields']) ){
                        $fields = array_keys($_SESSION['form_fragments'][$form_id][$this->instance_id][$wrapper_id][$_fragment['id']]['fields']);
                        foreach($fields as $field){
                            if( isset($_SESSION['override_form_fields'][$form_id][$this->instance_id][$field])){
                                unset($_SESSION['override_form_fields'][$form_id][$this->instance_id][$field]);
                            }
                        }
                    }
                    unset($_SESSION['form_fragments'][$form_id][$this->instance_id][$wrapper_id][$_fragment['id']]);
                }
            }
        }

        //debugga($this->deleted_ajax_fragments);exit;
        
    }

    /**
     * return count of fragmentes in wrapper
     *
     * @param string $wrapper_id
     * @return integer
     */
    function getCountFragmentsForWrapper(string $wrapper_id):int{
        if( !isset($_SESSION['form_fragments'][$this->id][$this->instance_id][$wrapper_id])) return 0;
        return count($_SESSION['form_fragments'][$this->id][$this->instance_id][$wrapper_id]);
    }




    public function store(string $key, $value){
        $form_id = isset($this->parent_id)? $this->parent_id: $this->id;
        $_SESSION['form_storage'][$form_id][$this->instance_id][$key] = $value;
    }
    
    public function retrive(string $key){
        
        $form_id = isset($this->parent_id)? $this->parent_id: $this->id;
        if( !isset($_SESSION['form_storage'][$form_id][$this->instance_id][$key]) ) return null;
        return $_SESSION['form_storage'][$form_id][$this->instance_id][$key];
    }

}