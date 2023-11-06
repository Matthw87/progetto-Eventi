<?php
namespace Marion\Support\Form;
use Marion\Core\Marion;
use Marion\Support\Attachment\Attachment;

class FormData{
    /** @var array $fields = [
     *     'id' => 1234, // unique id in DB
     *     'activityDt' => new DateTime,
     *     'sales' => [SaleApp::createSale()], // array of one or more sales client had with us over the years
     *     'agentReviews' => [
     *          ['agent' => 'Steeve', 'rating' => 9.5, 'comment' => 'Does his job well'],
     *          ['agent' => 'Tiffany', 'rating' => 6.3, 'comment' => 'Could try harder'],
     *     ],
     *     'soapData' => (object)[ // the data we got from external system
     *         'LoanPast' => 'GOOD',
     *         'Job' => 'WEB DESIGNER',
     *     ],
     * ] */
    public array $fields;
    public array $errors;
    public array $error_fields;
    public array $data;
    public array $validated_data;
    public bool $validated = false;


    function __construct()
    {
        $this->error_fields = [];
        $this->errors = [];
    }

    /**
     * Holds configuration settings for each field in a model.
     * @param array $fields {
     *     @var bool   $required Whether this element is required
     *     @var string $label    The display name for this element
     *}
     * @return Object A new editor object.
     **/
    function setFields(array $fields): self{
        $this->fields = $fields;
        return $this;
    }

    /*
    'id' => [
				'type' => 'hidden',
				'validation'=> 'integer',
			],
			'name' => [
				'type' => 'text',
				'label' => 'Name',
				'translate' => true,
				'multilang' => true,
				'validation'=> 'min:10|max:200|required',
			],
			'label' => [
				'type' => 'text',
				'label' => 'Name',
				'validation'=> 'min:10|max:200|required',
			]

    */
    
    function prepare(){
        $dataform = [];
        
        foreach($this->fields as $key => $data){
            $validation = null;
            $options = null;
            if( isset($data['validation']) ){
                $validation = $this->getValidation($data['validation']);
            }
            
            if( array_key_exists('options',$data) ){
                $options = $data['options'];
                if( is_callable($options) ){
                    $options = $options();
                }
            }
            $type = $data['type'];
            $switch = false;
            $class = '';
            $tipo = '';
            $date_type ='';
            $name = "formdata[{$key}]";
            $name_autocomplete = "formdata[{$key}_autocomplete]";
            $unique_value = false;
            $widget = '';
            $js_libraries = [];
            $tipo_file = '';
            $accepted_files = '';
            $resize_image = 'thumbnail,small,medium,large';
            switch($type){
                case 'autocomplete':
                    $type = 'text';
                    $js_libraries[] = 'autocomplete';
                    $widget = 'buildAutocomplete';
                    break;
                case 'multiselect:search':
                    $type = 'multiselect';
                    $name .= "[]";
                    break;
                case 'multiselect:tabs':
                    $type = 'multiselect';
                    $widget = 'buildMultiselect';
                    $js_libraries[] = 'multiselect';
                    $multiselect_available_options_label = isset($data['available_options_label'])?$data['available_options_label']:'selectable values';
                    $multiselect_selected_options_label = isset($data['selected_options_label'])?$data['selected_options_label']:'selected values';
                    $name .= "[]";
                    break;
                case 'media':
                    $type = 'hidden';
                    $widget = 'buildFilemanager';
                    break;
                case 'media:image':
                    $type = 'hidden';
                    $widget = 'buildFilemanager:image';
                    break;
                case 'image:small':
                    $widget = 'buildUploadImageSmall';
                case 'image':
                    $type = 'hidden';
                    if( !isset($widget) ){
                        $widget = 'buildUploadImage';
                    }
                    if( $validation && array_key_exists('resizeimage',$validation)){ 
                        $resize = $validation['resizeimage'];
                        $resize_list = explode(',',$resize);
                        $resize = '';
                        foreach($resize_list as $r){
                            switch($r){
                                case 'th':
                                    $resize .= "thumbnail,";
                                    break;
                                case 'sm':
                                    $resize .= "small,";
                                    break;
                                case 'md':
                                    $resize .= "medium,";
                                    break;
                                case 'lg':
                                    $resize .= "large,";
                                    break;
                            }
                        }
                        $resize_image = preg_replace('/\,$/','',$resize);
                    }
                    if( $validation && array_key_exists('acceptedfiles',$validation)){
                        $accepted_files = $validation['acceptedfiles'];
                        $mimes = explode(',',$accepted_files);
                        $accepted_files = '';
                        foreach($mimes as $mime){
                            switch($mime){
                                case 'image/jpeg':
                                    $accepted_files .= "jpeg,jpg,";
                                    break;
                                case 'image/png':
                                    $accepted_files .= "png,";
                                    break;
                                case 'image/gif':
                                    $accepted_files .= "gif,";
                                    break;
                            }
                        }
                        $accepted_files = preg_replace('/\,$/','',$accepted_files);
                    }
                    break;
                case 'files':
                    $type = 'hidden';
                    $tipo_file = 'attachment';
                    $widget = 'dropzone';
                    if( $validation && array_key_exists('acceptedfiles',$validation)){
                        $accepted_files = $validation['acceptedfiles'];
                    }
                    break;
                case 'images':
                    $type = 'hidden';
                    $widget = 'dropzone';
                    $tipo_file = 'img';
                    if( $validation && array_key_exists('resizeimage',$validation)){ 
                        $resize = $validation['resizeimage'];
                        $resize_list = explode(',',$resize);
                        $resize = '';
                        foreach($resize_list as $r){
                            switch($r){
                                case 'th':
                                    $resize .= "thumbnail,";
                                    break;
                                case 'sm':
                                    $resize .= "small,";
                                    break;
                                case 'md':
                                    $resize .= "medium,";
                                    break;
                                case 'lg':
                                    $resize .= "large,";
                                    break;
                            }
                        }
                        $resize_image = preg_replace('/\,$/','',$resize);
                    }
                    $accepted_files ="image/jpeg,image/png,image/gif";
                    if( $validation && array_key_exists('acceptedfiles',$validation)){
                        $accepted_files = $validation['acceptedfiles'];
                    }
                    
                    break;
                case 'palette':
                    $type = 'text';
                    $widget = 'buildPalette';
                    $js_libraries[] = 'spectrum';
                    break;
                case 'switch':
                    $switch = true;
                    $unique_value = true;
                    $type = 'checkbox';
                    $switch_true_label = isset($data['true_label'])? $data['true_label']: strtoupper(_translate('general.yes'));
                    $switch_false_label = isset($data['false_label'])? $data['false_label']: strtoupper(_translate('general.no'));
                    $switch_true_value = isset($data['true_value'])? $data['true_value']: 1;
                    $switch_false_value = isset($data['false_value'])? $data['false_value']: 0;
                    break;
                case 'date':
                    $type = 'text';
                    $date_type = 'datetimepicker_date';
                    $tipo = 'Date';
                    break;
                case 'datetime':
                    $type = 'text';
                    $date_type = 'datetimepicker';
                    $tipo = 'DateTime';
                    break;
                case 'editor':
                    $type = 'textarea';
                    $class = 'cke-editor-advanced';
                    break;
                case 'multiselect':
                    $name .= "[]";
                    break;
                case 'checkbox:multiple':
                    $name .= "[]";
                    $type = 'checkbox';
                    break;
                /*case 'checkbox:unique':
                    $type = 'checkbox';
                    $name .= "[]";
                    break;*/
                /*case 'time':
                    $type = 'text';
                    $class = 'datetimepicker';
                    $tipo = 'DateTime';
                    break;*/
            }
                
            $class .= in_array($key,$this->error_fields)?' validation_error':'';

            if( isset($data['multilang']) ){
                foreach( Marion::getConfig('locale','supportati') as $loc){	
                    $class_locale = $class;
                    $class_locale .= in_array($key."_".$loc,$this->error_fields)?' validation_error':'';
                    $dataform[$key]['id'] = $key;

                    if( isset($widget) && preg_match('/buildFilemanager/',$widget) ){
                        $dataform[$key]['widget'] = $widget;
                    }
                  
					$dataform[$key]['locales'][$loc] = [
                        'name' => $name."[$loc]",
                        'name_autocomplete' => $name_autocomplete."[$loc]",
                        'id' => $key."_".$loc,
                        'type' => $type,
                        'etichetta' => isset($data['label'])?$data['label']:'',
                        'descrizione' => isset($data['description'])?$data['description']:'',
                        'placeholder' => isset($data['placeholder'])?$data['placeholder']:'',
                        'obbligatorio' => ($validation && array_key_exists('required',$validation)),
                        'checklunghezza' => ($validation && (array_key_exists('min',$validation) || array_key_exists('max',$validation))),
                        'lunghezzamin' => ($validation && array_key_exists('min',$validation))?$validation['min']:'',
                        'lunghezzamax' => ($validation && array_key_exists('max',$validation))?$validation['max']:'',
                        'options' => isset($options)?$options: null,
                        'switch' => $switch,
                        'unique_value' => $unique_value,
                        'post_function_array' => ['trim'],
                        'codice' => $key,
                        'class' => $class_locale,
                        'tipo' => $tipo,
                        'date_type' => $date_type,
                        'value' => isset($this->data[$key][$loc])?$this->data[$key][$loc]:null,
                        'widget' => $widget,
                        'js_libraries' => $js_libraries,
                        'true_label' => isset($data['true_label'])?$data['true_label']: '',
                        'false_label' => isset($data['false_label'])?$data['false_label']: '',
                        'tipo_file' => $tipo_file,
                        'acceptedFiles' => $accepted_files,
                        'resize_image' => $resize_image
                    ];
                    $this->getInfoAttachment($dataform[$key]['locales'][$loc]);
                }
            }else{
                $value = isset($this->data[$key])?$this->data[$key]:null;
               
                if( $tipo == 'Date' && !$this->validated){
                    if( $value ){
                        if( preg_match('/\//',$value) ){
                            $explode_date = explode('/',$value);
                            $value = $explode_date[2]."-".$explode_date[1]."-".$explode_date[0];
                        }
                        $value = date("d/m/Y",strtotime($value));
                    }
                }
               
                $dataform[$key] = [
                    'name' => $name,
                    'name_autocomplete' => $name_autocomplete,
                    'id' => $key,
                    'type' => $type,
                    'etichetta' => isset($data['label'])?$data['label']:'',
                    'descrizione' => isset($data['description'])?$data['description']:'',
                    'placeholder' => isset($data['placeholder'])?$data['placeholder']:'',
                    'obbligatorio' => ($validation && array_key_exists('required',$validation)),
                    'checklunghezza' => ($validation && (array_key_exists('min',$validation) || array_key_exists('max',$validation))),
                    'lunghezzamin' => ($validation && array_key_exists('min',$validation))?$validation['min']:'',
                    'lunghezzamax' => ($validation && array_key_exists('max',$validation))?$validation['max']:'',
                    'options' => isset($options)?$options: null,
                    'js_options' => isset($options)?json_encode($options): '',
                    'switch' => $switch,
                    'unique_value' => $unique_value,
                    'post_function_array' => ['trim'],
                    'codice' => $key,
                    'class' => $class,
                    'tipo' => $tipo,
                    'date_type' => $date_type,
                    'value' => $value,
                    'widget' => $widget,
                    'js_libraries' => $js_libraries,
                    'true_label' => isset($data['true_label'])?$data['true_label']: '',
                    'false_label' => isset($data['false_label'])?$data['false_label']: '',
                    'tipo_file' => $tipo_file,
                    'acceptedFiles' => $accepted_files,
                    'resize_image' => $resize_image
                ];
                $this->getInfoAttachment($dataform[$key]);
                if( isset($multiselect_available_options_label) ){
                    $dataform[$key]['available_options_label'] = $multiselect_available_options_label;
                }
                if( isset($multiselect_selected_options_label) ){
                    $dataform[$key]['selected_options_label'] = $multiselect_selected_options_label;
                }
                if( isset($switch_false_label) ){
                    $dataform[$key]['false_label'] = $switch_false_label;
                }
                if( isset($switch_true_label) ){
                    $dataform[$key]['true_label'] = $switch_true_label;
                }
                if( isset($switch_false_value) ){
                    $dataform[$key]['false_value'] = $switch_false_value;
                }
                if( isset($switch_true_value) ){
                    $dataform[$key]['true_value'] = $switch_true_value;
                }
            }
            
        }

        /*$c['checklunghezza']);
					unset($c['lunghezzamin']);
					unset($c['lunghezzamax']*/
        //debugga($dataform);exit;
        return $dataform;
    }

    function getValidation($validation): array{
        if( is_array($validation)){
           $list = $validation;
        }else{
            $list = explode('|',$validation);
        }
        $data = [];
        foreach($list as $v){
            if( $v instanceof Rule){
                $data['rules'][] = $v;
            }else{
                $params = explode(':',$v);
                if( count($params) > 1){
                    $data[$params[0]] = $params[1];
                }else{
                    $data[$params[0]] = 1;
                }
            }
            
        }
        return $data;
    }




    public function validate(array $data): bool{
        $this->validated = true;
        $validated_data = [];
        $this->data = $data;
        foreach($this->fields as $key => $field){
            $value = ( array_key_exists($key,$data))?$data[$key]:'';
            if(!is_array($value)){
                $value = trim($value);
            }
           
            switch($field['type']){
                case 'date':
                    if( $value ){
                        list($d,$m,$y) = explode('/',$value);
                        $value = "{$y}-{$m}-{$d}";
                    }
                    break;
                case 'switch':
                    if(!$value){ 
                        if( isset($field['false_value']) ){
                            $value = $field['false_value'];
                        }else{
                            $value = 0;
                        }
                    }
                    break;
                case 'file':
                    //debugga($key);exit;
                    if( okArray($_FILES) ){
                        if( array_key_exists($key,$_FILES['formdata']['name']) && $_FILES['formdata']['name'][$key]){
                            $value = [
                                'name' => $_FILES['formdata']['name'][$key],
                                'type' => $_FILES['formdata']['type'][$key],
                                'tmp_name' => $_FILES['formdata']['tmp_name'][$key],
                                'error' => $_FILES['formdata']['error'][$key],
                                'size' => $_FILES['formdata']['size'][$key],
                            ];
                        }
                    }
                    break;
            }
            if( isset($field['validation']) ){
                $validation = $this->getValidation($field['validation']);
                $this->validation($key,$value,$validation);
            }
            
            $validated_data[$key] = $value;
        }
        
        if( okArray($this->errors) ){
            return false;
        }
        $this->validated_data = $validated_data;
        return true;
    }


    function validation($key,$value,$validation){
        $label = isset($this->fields[$key]['label'])?$this->fields[$key]['label']:'';
        $type = $this->fields[$key]['type'];
        $multilang = false;
        if( array_key_exists('multilang',$this->fields[$key]) ){
            $multilang = $this->fields[$key]['multilang'];
        }
       
        $errors = [];
        $options = [];
        if( array_key_exists('options',$this->fields[$key]) ){
            $options = $this->fields[$key]['options'];
            if( is_callable($options) ){
                $options = $options();
            }
        }
        if( in_array($type,['select','checkbox:mulitple','radio'])){
            if( !empty($value) ){
                if(!array_key_exists($value,$options)){
                    $errors[] = _translate(['form_validation.errors.invalid_field',$label]);
                    $this->error_fields[] = $key;
                }
            }
        }
        
        if( $type == 'switch' ){
            
            if( !empty($value) ){
                $available_values = [];
                if( isset($this->fields[$key]['true_value'])){
                    $available_values[] = $this->fields[$key]['true_value'];
                }else{
                    $available_values[] = 1;
                }
                if( isset($this->fields[$key]['false_value'])){
                    $available_values[] = $this->fields[$key]['false_value'];
                }else{
                    $available_values[] = 0;
                }
                if( !in_array($value,$available_values) ){
                    $errors[] = _translate(['form_validation.errors.invalid_field',$label]);
                    $this->error_fields[] = $key;
                    
                }
               
            }
        }
       
        if( in_array($type,['multiselect','checkbox'])){
            if( !empty($value) ){
                if(!array_intersect($value,array_keys($options))){
                    $errors[] = _translate(['form_validation.errors.invalid_field',$label]);
                    $this->error_fields[] = $key;
                }
            }
        }
        if( $multilang ){
            $locales = Marion::getConfig('locale','supportati');
            foreach($locales as $loc){
                $value_loc = '';
                if( $value ) $value_loc = $value[$loc];
                $errors_field_locale = $this->validateField($validation,$value_loc,$label." ({$loc})",$type);
                if( okArray($errors_field_locale) ){
                    $errors = array_merge($errors,$errors_field_locale);
                    $this->error_fields[] = trim($key."_".$loc);
                }
                
            }
        }else{
            $errors_field = $this->validateField($validation,$value,$label,$type);
            if( okArray($errors_field) ){
                $errors = array_merge($errors,$errors_field);
                $this->error_fields[] = $key;
            }
        }

        if( count($errors) > 0){
            $this->errors = array_merge($this->errors,$errors);
        }
        
    }



    private function validateField(
        array $validation,
        $value,
        $label,
        $type
        ): array{
        $errors = [];
        foreach($validation as $validation_key => $params){
            switch( $validation_key ){
                case 'required':
                    if( empty($value) && !is_numeric($value) ){
                        $errors[] = _translate(['form_validation.errors.required_field',$label]);
                    }
                    break;
                case 'email':
                    if( $value && !Form::isEmail($value) ){
                        $errors[] = _translate(['form_validation.errors.invalid_email',$label]);
                    }
                    break;
                case 'boolean':
                    break;
                case 'integer':
                    if( $value && !Form::isInteger($value) ){
                        $errors[] = _translate(['form_validation.errors.invalid_integer',$label]);
                    }
                    break;
                case 'float':
                    if( $value && !Form::isFloat($value) ){
                        $errors[] = _translate(['form_validation.errors.invalid_float',$label]);
                    }
                    break;
                case 'numeric':
                    if( $value && !Form::isNumeric($value) ){
                        $errors[] = _translate(['form_validation.errors.invalid_numeric',$label]);
                    }
                    break;
                case 'date':
                    if( $value && !Form::isDate($value) ){
                        $errors[] = _translate(['form_validation.errors.invalid_date',$label]);
                    }
                    break;
                case 'time':
                    if( $value && !Form::isTime($value) ){
                        $errors[] = _translate(['form_validation.errors.invalid_time',$label]);
                    }
                    break;
                case 'alpha':
                    if( $value && !Form::isAlpha($value) ){
                        $errors[] = _translate(['form_validation.errors.invalid_alpha',$label]);
                    }
                    break;
                case 'alphanumeric':
                    if( $value && !Form::isAlphaNumeric($value) ){
                        $errors[] = _translate(['form_validation.errors.invalid_alphanumeric',$label]);
                    }
                    break;
                case 'min':
                    $min = $params;
                    if( $value && strlen($value) < $min ){
                        $errors[] = _translate(['form_validation.errors.min_lenght',$label,$min]);
                    }
                    break;
                case 'max':
                    $max = $params;
                    if( $value && strlen($value) > $max ){
                        $errors[] = _translate(['form_validation.errors.max_lenght',$label,$max]);
                    }
                    break;
                case 'positive':
                    if( $value && Form::isNumeric($value)  ){
                        if( $value < 0){
                            $errors[] = _translate(['form_validation.errors.invalid_positive_number',$label]);
                        }   
                    }
                    break;
                case 'notzero':
                    if( $value == 0 && Form::isNumeric($value) ){
                        $errors[] = _translate(['form_validation.errors.invalid_notzero_number',$label]);
                    }
                    break;
                case 'notempty':
                    if( ($value == 0 && Form::isNumeric($value)) || $value == ''){
                        $errors[] = _translate(['form_validation.errors.invalid_empty',$label]);
                    }
                    break;
                case 'negative':
                    if( $value && Form::isNumeric($value)  ){
                        if( $value > 0){
                            $errors[] = _translate(['form_validation.errors.invalid_negative_number',$label]);
                        }   
                    }
                    break;
                case 'price':
                    if( Form::isPrice($value)  ){
                        if( $value < 0){
                            $errors[] = _translate(['form_validation.errors.invalid_price',$label]);
                        }   
                    }
                    break;
                case 'fiscal_code_ita':
                    if( $value && !Form::isFiscalCodeIta($value)  ){
                        $errors[] = _translate(['form_validation.errors.invalid_fiscalcode',$label]);
                    }
                    break;
                case 'vat_number_ita':
                    if( $value && !Form::isVatNumberIta($value)  ){
                        $errors[] =_translate(['form_validation.errors.invalid_vatnumber',$label]);
                    }
                    break;
                case 'phone':
                    if( $value && !Form::isPhone($value)  ){
                        $errors[] =_translate(['form_validation.errors.invalid_phone',$label]);
                    }
                    break;
                case 'minfiles':
                    $min = $params;
                    if( $value && okArray($value) && count($value) < $min){
                        $errors[] =_translate(['form_validation.errors.min_files',$label,$min]);
                    }
                    break;
                case 'maxfiles':
                    $max = $params;
                    if( $value && okArray($value) && count($value) > $max){
                        $errors[] =_translate(['form_validation.errors.max_files',$label,$max]);
                    }
                    break;
                case 'acceptedfiles':
                    if( $type == 'file' ){
                        $mime = explode(',',$params);
                        if( $value && !in_array($value['type'],$mime) ){
                            $errors[] =_translate(['form_validation.errors.accepted_files',$label,$params]);
                        }
                    }
                    break;
                case 'regex':
                    $pattern = $params;
                    if( $value && !preg_match("/{$pattern}/",$value)){
                        $errors[] = _translate(['form_validation.errors.regex',$label,$pattern]);
                    }
                    break;
                case 'rules':
                    $check = true;
                    if( okArray($params) ){
                        foreach( $params as $rule){
                            if( isset($rule->validation_function) ){
                                $function = $rule->validation_function;
                                $result = call_user_func($function,$value,$this);
                                if( $result ){
                                    $errors[] = $result;
                                }
                            }
                           
                        }
                    }
                    
                    break;
            }
            
        }

        return $errors;
    }


    private function getInfoAttachment(&$data){
		if( $data['tipo_file'] == 'attachment'){
			if( okArray($data['value']) ){
				foreach($data['value'] as $id){
					$attach = Attachment::withId($id);
					if( is_object($attach) ){
						
						$name = explode('.',$attach->filename);
						$ext = $name[count($name)-1];
						$img = '../assets/images/file-icons/512px/'.$ext.".png";
						if( !file_exists($img) ){
							$img = '../assets/images/file-icons/512px/_blank.png';
						}
						$attach->img = $img;
						$data['info'][]= $attach;
					}
				
				}
			}	
		}
	 }
}


