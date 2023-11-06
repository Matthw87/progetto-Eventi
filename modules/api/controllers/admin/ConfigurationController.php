<?php
use Marion\Controllers\Controller;
use Marion\Support\Form\FormHelper;
use Marion\Core\Marion;
use Marion\Controllers\Elements\UrlButton;

class ConfigurationController extends Controller{


    function display()
    {

        $this->setTitle('Setting API');
        $this->setMenu('manage_modules');
        $this->addToolButton(
            (new UrlButton('back'))
            ->setUrl($this->getBaseUrlBackend()."index.php?ctrl=ModuleAdmin&action=list")
            ->setText(_translate('list.back'))
            ->setIcon('fa fa-arrow-left')
        );
        $fields = [
            'swagger_enabled_modules' => [
                'label' => _translate('setting.swagger_enabled_modules','api'),
                'type' => 'multiselect',
                'options' => function(){
                    $modules = Marion::$modules;
                    $options = [];
                    foreach($modules as $m){
                        $options[$m['directory']] = $m['directory'];
                    }
                    uasort($options,function($a, $b){
                        if ($a == $b) {
                            return 0;
                        }
                        return ($a < $b) ? -1 : 1;
                    });
                    return $options;
                }
                
            ]
            
        ];
        
        FormHelper::create('api_setting',$this)
            ->layoutFile(_MARION_MODULE_DIR_."api/templates/admin/forms/configuration.xml")
            ->setFields($fields)
            ->init(function(FormHelper $form){

                if( !$form->isSubmitted() ){
                    $data =  Marion::getConfig('api_configuration');
                    //$data['test_multilang_field'] = unserialize($data['test_multilang_field']);
                    $form->formData->data = $data?$data:[];
                }
            })
            ->process(function( FormHelper $form){
                $data = $form->getValidatedData();
                foreach($data as $k => $v){
                    if( $k == 'swagger_enabled_modules' ){
                        $v = serialize($v);
                    }
                    Marion::setConfig('api_configuration',$k,$v);
                
                }
                Marion::refresh_config();
                $this->displayMessage("Dati salvati con successo");
            })->display();
    }
}
?>