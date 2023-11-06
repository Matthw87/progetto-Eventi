<?php
namespace Marion\Support\ListWrapper;
use Closure;
use Marion\Core\Base;
use stdClass;

class SortableListHelper extends ListCore {

    protected string $row_parent = 'parent';
    private int $max_depth = 10;

	protected string $html_template = '@core/layouts/list/sortable_list.htm';

    public Closure $content_function;
    public Closure $class_function;
    public Closure $change_function;
    public Closure $active_class_function;
    

    protected $tree;
   
    public function setMaxDepth(int $depth): static{
        $this->max_depth = $depth;
        return $this;
    }

    public function setTree($tree = null){
		$this->tree = $tree;
		return $this;
	}


    public function setRowParent(string $id = null): static{
		$this->row_parent = $id;
		return $this;
	}


    function build(): void{
        $this->data_source->setEnbaleLimit(false);
        parent::build();
        $this->buildList();
    }
		
	
    function display(): void{
		$this->build();
        $this->ctrl->setVar('changeCallbackUrl',$this->ctrl->getUrlCurrent());
        $this->ctrl->setVar('max_depth',$this->max_depth);
		if( _var('confirm_message') ){
			$this->ctrl->setVar('confirm_message_list',_var('confirm_message'));
			$this->ctrl->setVar('url_confirm_action_list',_var('confirm_url'));
		}
        
		parent::display();
		
    }

    function buildList(){

        $current_url = $this->ctrl->getUrlCurrent();
        $row_actions = [];
        if( okArray($this->actionRowButtons)){
            foreach($this->actionRowButtons as $btn){
                $row_actions[$btn->getAction()] = $btn->getData();
            }
        }

        $items = [];
        
        foreach($this->data_list as $row){
            $field_id = $this->row_id;
            $parent_id = $this->row_parent;
            $item = new stdClass;
            $item->id = $row->$field_id;
            $item->parent = $row->$parent_id;
            if( isset($this->content_function) ){
                $item->content = call_user_func($this->content_function,$row);
            }
            $actions = [];
            foreach( $row_actions as $action_key => $v){
                if( array_key_exists('enable_value',$v) ){
                    $check = 1;
                    $check_value = $v['enable_value'];
                    if( is_object($row) ){
                        if( $check_value && property_exists($row,$check_value)){
                            $check = $row->$check_value;
                        }
                            
                    }else{
                        if( array_key_exists($check_value,$row) ){
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
                    //debugga($v);exit;
                    if( $v['url'] ){
                       
                        if(  $v['url'] instanceof Closure ){	
                            $url = call_user_func($v['url'],$row);
                        }else{
                            $url = $v['url'];
                        }
                        //$url = preg_replace("/{{field_id}}/",$field_id,$url);
                        //$url = preg_replace("/{{script_url}}/",$this->ctrl->getUrlScript(),$url);
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
                $item->actions = $actions;
            }
            $items[] = $item;
        }

        //debugga($items);exit;
       
        $tree = Base::buildtree($items);
        
        $this->ctrl->setVar('items',$tree);


    }


    /**
     * Change callback
     *
     * @param Closure $function
     * @return static
     */
    function onChange(Closure $function): static{
		$action = _var('changed');
        
		$ids = _var('ids');
		if( $action ){
            call_user_func($function,$ids);
            echo json_encode(1);
            exit;
		}
        return $this;
    }

    /**
     * set Content Row
     *
     * @param Closure $function
     * @return static
     */
    function setContent(Closure $function): static{
        $this->content_function = $function;
        return $this;
    }

    /**
     * set CSS classes to row
     *
     * @param Closure $function
     * @return static
     */
    function setClass(Closure $function): static{
        $this->class_function = $function;
        return $this;
    }
}