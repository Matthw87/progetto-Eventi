<?php
namespace Marion\Support\ListWrapper;
use Marion\Controllers\Controller;
use JasonGrimes\Paginator;
use Marion\Support\Pdf;
use Closure;

class SimpleListHelper extends ListCore{
    

	protected string $html_template = '@core/layouts/list/simple_list.htm';

    public Closure $content_function;
    public Closure $class_function;
    public Closure $active_class_function;
    

    function  build(): void{
        parent::build();
        $this->buildList();
    }

    function display(): void{
		$this->build();
	
		if( _var('confirm_message') ){
			$this->ctrl->setVar('confirm_message_list',_var('confirm_message'));
			$this->ctrl->setVar('url_confirm_action_list',_var('confirm_url'));
		}
        parent::display();
    }

    function buildList(){
        $current_url = $this->ctrl->getUrlCurrent();

		$this->ctrl->setVar('_current_url_list',$current_url);
		$current_url = preg_replace('/&orderBy=(.*)&orderType=DESC/','',$current_url);
		$current_url = preg_replace('/&orderBy=(.*)&orderType=ASC/','',$current_url);
		
		$this->ctrl->setVar('_current_url_ordered_list',$current_url);
        $data = [];
        if( okArray($this->data_list)){
            foreach($this->data_list as $row){
                $item = [];
                if( isset($this->content_function) ){
                    $item['label'] = call_user_func($this->content_function,$row);
                }
                if( isset($this->active_class_function) ){
                    if(call_user_func($this->active_class_function,$row)){
                        $item['active'] = true;
                    }
                }
                if( isset($this->class_function) ){
                    $item['classes'] = call_user_func($this->class_function,$row);
                }
                $actions = array();
               
                foreach( $this->template_data['row_actions'] as $action_key => $v){
                    if( array_key_exists('enable_value',$v) ){
                        $check = 1;
                        $check_value = $v['enable_value'];
                        if( is_object($row) ){
                            if($check_value && property_exists($row,$check_value)){
                                $check = $row->$check_value;
                            }
                                
                        }else{
                            if( $check_value && array_key_exists($check_value,$row) ){
                                $check = $row[$check_value];
                            }
                            
                        }
                        if( !$check ){
                            continue;
                        }
                    }
                    if( array_key_exists('enable_function',$v) ){
                        if( is_callable($v['enable_function'])){
                           
                            $check_function = $v['enable_function'];
                            $check = $check_function($row);
                            if( !$check ){
                                continue;
                            }
                        }else{
                            if( $v['enable_function'] && method_exists($this,$v['enable_function']) ){
                                $check_function = $v['enable_function'];
                                $check = $this->$check_function($row);
                                if( !$check ){
                                    continue;
                                }
                            }
                        }
                    }
                    
                    if( array_key_exists('url_function',$v) && $v['url_function'] ){
                        if( is_callable($v['url_function'])){
                            $url_function = $v['url_function'];
                            $url = $url_function($row);
                        }else{
                            if( method_exists($this,$v['url_function']) ){
                                $url_function = $v['url_function'];
                                $url = $this->$url_function($row);
                                
                            }
                        }
                    }else{
                        if( $v['url'] ){
                           
                            if(  $v['url'] instanceof Closure ){	
                                $url = call_user_func($v['url'],$row);
                            }else{
                                $url = $v['url'];
                            }
                        }else{
                            $row_id = $this->row_id;
                            
                            if(is_object($row) ){
                                $id_row = $row->$row_id;
                            }else{
                                $id_row = $row[$row_id];
                            }
                            $url_action = $current_url."&action_list=".$action_key."&id_row_list=".$id_row;
                            
                            if($v['confirm']){
                                if( $v['confirm_message'] instanceof Closure ){	
                                    $confirm_message = call_user_func($v['confirm_message'],$row);
                                }else{
                                    $confirm_message = $v['confirm_message'];
                                }
                                $url = $current_url."&confirm_message=".urlencode($confirm_message)."&confirm_url=".urlencode($url_action);
                            }else{
                                $url = $url_action;
                            }
        
                        }
                        
                    }
                    $v['url'] = $url;
                    $actions[] = $v;
                }
                $item['actions'] = $actions;
                $data[] = $item;
            }
        }
        
        $this->ctrl->setVar('list',$data);

    }



    function setContent(Closure $function): static{
        $this->content_function = $function;
        return $this;
    }

    function setClass(Closure $function): static{
        $this->class_function = $function;
        return $this;
    }
    function setActiveClass(Closure $function): static{
        $this->active_class_function = $function;
        return $this;
    }
}