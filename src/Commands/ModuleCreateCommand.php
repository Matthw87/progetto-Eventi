<?php

namespace Marion\Commands;
use Marion\Core\Marion;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ModuleCreateCommand extends Command 
{
    protected function configure()
    {
        $this->setName('module:create')
        ->setDescription('Create module')
        ->setHelp('This command create a new module')
        ->addArgument('module', InputArgument::REQUIRED, 'Specific a module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

       
       
        Marion::read_config();
        $module_name = $input->getArgument('module');

        if( file_exists(_MARION_MODULE_DIR_.$module_name) ){
            $output->writeln('<error>The '.$module_name.' module already exists</error>');
            return 1;
        }
        $path_module = _MARION_MODULE_DIR_.$module_name;

        
        mkdir($path_module,0755);
        mkdir($path_module."/controllers",0755);
        mkdir($path_module."/controllers/front",0755);
        mkdir($path_module."/controllers/admin",0755);
        mkdir($path_module."/templates",0755);
        mkdir($path_module."/translations",0755);
        mkdir($path_module."/templates/front",0755);
        mkdir($path_module."/templates/admin",0755);
        mkdir($path_module."/templates/admin/forms",0755);
        mkdir($path_module."/assets",0755);
        mkdir($path_module."/src",0755);
        mkdir($path_module."/assets/js",0755);
        mkdir($path_module."/assets/css",0755);
        $marion_version = _MARION_VERSION_;
        $config_xml = <<<EOD
        <?xml version="1.0"?>
        <module>
            <info> 
                <author>Author name</author>
                <name>Module name</name>
                <permission>superadmin</permission>
                <tag>$module_name</tag>
                <kind>cms</kind>
                <compatibility>
                    <min>$marion_version</min>
                </compatibility>
                <version>0.0.1</version>
                <scope></scope> 
                <description><![CDATA[Module description]]></description> 
            </info>
            <linkSetting>
                index.php?mod=$module_name&amp;ctrl=Configuration
            </linkSetting>
        </module>
        EOD;

        $class_module_name = $this->getModuleClassName($module_name);
        $module_php =<<<EOD
        <?php
        use Marion\Core\{Marion,Module};
        use Marion\Support\Form\Form;
        use Illuminate\Database\Capsule\Manager as DB;
        use Illuminate\Database\Schema\Blueprint;
        class $class_module_name extends Module
        {

            function install(): bool{
                /**
                 * INSERT INSTALL CODE
                 * 
                 * 
                 * DB::schema()->create('table_name',function(Blueprint \$table){
                 *		\$table->id();
                 *		\$table->string('field_name');
                 *		.....
                 *	});
                 *  .......
                 */
                \$res = parent::install();
                return \$res;
            }

            function uninstall(): bool{
                /**
                 * INSERT UNINSTALL CODE
                 * 
                 * 
                 * DB::schema()->dropIfExists('table_name');
                 * .....
                 */
                \$res = parent::uninstall();
                return \$res;
            }
        }
        EOD;
        $composer_file =<<<EOD
        {
            "autoload": {
                "psr-4": {"$class_module_name\\\": "src/"}
            }
        }
        EOD;
        $routes_file =<<<EOD
        <?php
        use Marion\Router\Route;
        Route::get('/test','$module_name:IndexController:test');
        ?>
        EOD;

        $ctrl_front =<<<EOD
        <?php
        use Marion\Controllers\FrontendController;
        use Illuminate\Database\Capsule\Manager as DB;

        class IndexController extends FrontendController{

            function test(): void{
                \$this->output('@$module_name/test.html');
            }
        }
        ?>
        EOD;
        
        $ctrl_admin_conf =<<<EOD
        <?php
        use Marion\Controllers\Controller;
        use Marion\Support\Form\FormHelper;
        use Marion\Core\Marion;
        use Marion\Controllers\Elements\UrlButton;

        class ConfigurationController extends Controller{


            function display()
            {

                \$this->setTitle('Setting Module');
                \$this->setMenu('manage_modules');
                \$this->addToolButton(
                    (new UrlButton('back'))
                    ->setUrl(\$this->getBaseUrlBackend()."index.php?ctrl=ModuleAdmin&action=list")
                    ->setText(_translate('list.back'))
                    ->setIcon('fa fa-arrow-left')
                );
                \$fields = [
                    'test_field' => [
                        'type' => 'text',
                        'label' => 'Name field',
                    ],
                    'test_multilang_field' => [
                        'type' => 'text',
                        'label' => 'Name field multilang',
                        'multilang' => true
                    ],
                    
                ];
                
                FormHelper::create('restricted_app_form',\$this)
                    ->layoutFile(_MARION_MODULE_DIR_."$module_name/templates/admin/forms/configuration.xml")
                    ->setFields(\$fields)
                    ->init(function(FormHelper \$form){

                        if( !\$form->isSubmitted() ){
                            \$data =  Marion::getConfig('$module_name._configuration');
                            \$data['test_multilang_field'] = unserialize(\$data['test_multilang_field']);
                            \$form->formData->data = \$data?\$data:[];
                        }
                    })
                    ->process(function( FormHelper \$form){
                        \$data = \$form->getValidatedData();
                        foreach(\$data as \$k => \$v){
                            if( in_array(\$k,['test_multilang_field'])){
                                \$v = serialize(\$v);
                            }
                            Marion::setConfig('$module_name._configuration',\$k,\$v);
                        
                        }
                        Marion::refresh_config();
                        \$this->displayMessage("Dati salvati con successo");
                    })->display();
            }
        }
        ?>
        EOD;


        $form_xml =<<<EOD
        <xml>
            <sheet>
                <row>
                    <col>
                        <field name="test_field"/>
                    </col>
                </row>
                <row>
                    <col>
                        <field name="test_multilang_field"/>
                    </col>
                </row>
            </sheet>
        </xml>
        EOD;

        $test_page_html =<<<EOD
        {% extends "layouts/page.htm" %}
        {% block content %}
            <h2>$module_name works</h2>
        {% endblock %}
        EOD;


        

        file_put_contents($path_module."/config.xml",$config_xml);
        file_put_contents($path_module."/{$module_name}.php",$module_php);
        file_put_contents($path_module."/routes.php",$routes_file);
        file_put_contents($path_module."/composer.json",$composer_file);
        file_put_contents($path_module."/controllers/front/IndexController.php",$ctrl_front);
        file_put_contents($path_module."/controllers/admin/ConfigurationController.php",$ctrl_admin_conf);
        file_put_contents($path_module."/templates/admin/forms/configuration.xml",$form_xml);
        file_put_contents($path_module."/templates/front/test.html",$test_page_html);
        $output->writeln('<info>'.$module_name.'</info>');
        return 0;
        
    
    }
    function getModuleClassName(string $string):string{
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        return $str;
    
    }
}