<?php
use Marion\Core\Marion;
use Marion\Support\Cache;
use Marion\Controllers\Controller;
use Marion\Support\Form\Traits\FormHelper;

class SettingAdminController extends Controller{
    use FormHelper;
    public $_auth = '';
	


	
	function display()
	{
		$action = $this->getAction();
		switch($action){
            case 'general':
                $this->displayConf();
            break;
            default:
            
                $this->displayConfBackend();
            break;
		}
    }
    

    function displayConf(){
        $this->setMenu('setting_general');
        $database = Marion::getDB(); 
        

        if( $this->isSubmitted()){
            $dati = $this->getFormdata();
           
            $array = $this->checkDataForm('conf_general',$dati);
            
            
            if( $array[0] == 'ok' ){
                //debugga($array);exit;
                if( !in_array($array['default'],unserialize($array['supportati'])) ){
                    $array[0] = 'nak';
                    $array[1] = "Lingua di default non presente nelle lingue supportate";
                }
            }
        
            if($array[0] == 'ok' ){
                
                unset($array[0]);

                
                $array['site_name'] = $array['nomesito'];
                foreach($array as $k => $v){
                    
                    if( $k == 'default' || $k == 'supportati' ){
                        Marion::setConfig('locale',$k,$v);
                        
                    }else{
                        Marion::setConfig('general',$k,$v);
                        
                    }
        
                }
               
        
                if( Cache::exists("setting") ){
                    Cache::remove('setting');
                }
                if( Cache::exists("setting_locale") ){
                    Cache::remove('setting_locale');
                }
                $this->displayMessage('Dati salvati con successo!');


                $dati['supportati'] = serialize($array['supportati']);
                
            }else{
                $dati['supportati'] = serialize($dati['supportati']);
                $this->errors[] = $array[1];
            }

        }else{
            $database->update('locale',"code='it'",array('code'=>'it'));

            $select = $database->select('*','settings',"gruppo='general' order by ordine");
            $select2 = $database->select('*','settings',"gruppo='locale' order by ordine");
    
            
           
            
            //$locales_array = array_supported_locales();
            
            if( okArray($select)) {
                foreach($select as $v){
                    $dati[$v['chiave']] = $v['valore']; 
                }
            }

            if( okArray($select2)) {
                foreach($select2 as $v){
                    $dati[$v['chiave']] = $v['valore']; 
                }
            }
            
           
        }
	

        $dataform = $this->getDataForm('conf_general',$dati);
        $this->setVar('dataform',$dataform);
        $this->output('@core/admin/conf_general.htm');

    }


	function displayConfBackend(){
		$current_user = Marion::getUser();
        //debugga($current_user);exit;
        if( $this->isSubmitted()){
            $dati = $this->getFormdata();
            
            $array = $this->checkDataForm('conf_marion',$dati);
            if($array[0] == 'ok' ){
                $this->displayMessage('Dati salvati con successo!');
                $current_user->set(
                    array('color_theme' => $array['color_theme'],'locale'=>$array['locale'])
                );
                $current_user->save();
                $current_user->save();

                Marion::setUser($current_user);
            }else{
                $this->errors[] = $array[1];
            }
           
            
            
        }else{
            $dati = array();
            $dati['color_theme'] = $current_user->color_theme;
            $dati['locale'] = $current_user->locale;
        }

        
        $dataform = $this->getDataForm('conf_marion',$dati);
        $this->setVar('dataform',$dataform);
        $this->output('@core/admin/conf_marion.htm');
        
    }
    


    function array_backend_languages(){
        $locales = Marion::getConfig('locale','supportati');
        foreach($locales as $v){
            $toreturn[$v] = $v;
        }
        return $toreturn;
    }

  
    function array_supported_locales(){
		$database = Marion::getDB(); 
		$locales = $database->select('code','locale',"1=1 order by code");
		foreach($locales as $loc){
			$toreturn[$loc['code']] = $loc['code'];
		}

		return $toreturn;

	}
  
}


?>