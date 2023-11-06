<?php
namespace Marion\Controllers;
use Marion\Controllers\FrontendController;
use Marion\Core\Base;
use Marion\Entities\Cms\MenuItem;

class BackendController extends FrontendController{
	public $_auth = 'base';
	public $_required_access = true;

	function initTwingTemplate(){
		parent::initTwingTemplate();
		$this->getSideMenu();
	}


	function getSideMenu(){

		$query = MenuItem::prepareQuery()
			->where('scope','frontend')
			->where('active',1)
			->orderby('priority','ASC');		
		$list = $query->get();
		
		foreach($list as $k => $v){
			if( isset($v->showLabel) && $v->showLabel ){
				$function = $v->labelFunction;
				
				if(function_exists($function) ){
					$v->labelText = $function();
				}
				
			}
		}
		$toreturn = Base::buildtree($list);
		foreach($toreturn as $k => $v){
			if( $v->children ){
				uasort($toreturn[$k]->children,function($a,$b){
					if ($a->priority==$b->priority) return 0;
					return ($a->priority<$b->priority)?-1:1;
				});
			}
		}
		uasort($toreturn,function($a,$b){
			if ($a->priority==$b->priority) return 0;
			return ($a->priority<$b->priority)?-1:1;
		});
		
	
		$this->setVar('menu_forntend_items',$toreturn);
		
	}

	function setMenu($tag){
		
		$item = MenuItem::prepareQuery()->where('tag',$tag)->getOne();
		
		if( is_object($item) ){
			
			
			if( $item->parent ){
				$parent = $item->getParent();
				$this->setVar('current_frontend',$parent->tag);
				$this->setVar('current_frontend_child',$item->tag);
			}else{
				$this->setVar('current_frontend',$item->tag);
			}

		}

	}
}
?>