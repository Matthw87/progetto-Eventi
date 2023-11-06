<?php

use Marion\Core\Context;
use Marion\Entities\Cms\{Page,PageComposer};
use Marion\Core\Marion;
class IndexController extends \Marion\Controllers\FrontendController{
	public $page;


	function page(){
		$route = Context::getRoute();

		$_page_obj = Page::prepareQuery()->where('route_id',$route->getId())->getOne();
		if(is_object($_page_obj)){

		
			
			if( $_page_obj->advanced ){
				
				$composer = new PageComposer($_page_obj->composed_page_id,_var('pc_preview'));
				
				$composer->addDataToCtrl($this);
				$this->setVar('pagina',$_page_obj);
				$this->setVar('id_pagecomposer',$_page_obj->composed_page_id);
				$this->setVar('layout','layouts/composer/'.$composer->template_page);
				$this->page = 'pagecomposer';
			
			}else{

				
				$this->setVar('url_edit',$this->editPageUrl());
				$this->setVar('pagina',$_page_obj);
				$this->page = 'template_page';
			
			}
		}
		$this->output($this->page.'.htm');

		
	}

	function getPage($page=null){
		
		$this->page = $page;
		$_page_obj = null;
		if( $this->page ){
			$_page_obj = Page::getByUrl($this->page);
		}

		
		
		if(is_object($_page_obj)){

		
			
			if( $_page_obj->advanced ){
				
				$composer = new PageComposer($_page_obj->composed_page_id,_var('pc_preview'));
				
				$composer->addDataToCtrl($this);
				$this->setVar('pagina',$_page_obj);
				$this->setVar('id_pagecomposer',$_page_obj->composed_page_id);
				$this->setVar('layout','layouts/composer/'.$composer->template_page);
				$this->page = 'pagecomposer';
			
			}else{

				
				$this->setVar('url_edit',$this->editPageUrl());
				$this->setVar('pagina',$_page_obj);
				$this->page = 'template_page';
			
			}
			
		}else{
			

			if( !$this->page ){
					
				$database = Marion::getDB();;
				$now = date('Y-m-d');
				$homepage = $database->select('*','homepages',"active=1 OR (timer=1 AND start_date <= '{$now}' AND end_date >= '{$now}') order by active DESC");
				
				

			
				
				if( okArray($homepage) ){
					$candidata = $homepage[0];
					
					foreach($homepage as $v){
						if($v['timer']){
							$candidata = $v;
							$database->update('homepages',"1=1",array('active' => 0));
							$database->update('homepages',"id={$v['id']}",array('active' => 1,'timer'=>0));
							break;
						}
					}
					

					$id_page = $candidata['page_id'];
					if( is_object($_page_obj) ){
						$_page_obj = Page::prepareQuery()->where('composed_page_id',$id_page)->getOne();
						$this->setVar('page_title',$_page_obj->get('title'));
					}
					
					
					$composer = new PageComposer($id_page);
					$composer->addDataToCtrl($this);
					$this->setVar('id_pagecomposer',$id_page);
					//debugga($composer);exit;
					$this->setVar('layout','layouts/composer/'.$composer->template_page);
					$this->page = 'pagecomposer';
				}else{
					$this->page = '404';
					
				}
			}else{
				$this->page = '404';
				
			}
		}


		$this->output($this->page.'.htm');
		
	}





	function editPageUrl($return_location=false){
	   if( $return_location ){
			return $this->getBaseUrlBackend()."index.php?ctrl=PageAdmin&action=edit_page&url=".$this->page."&return_location=".urlencode($this->return_location);
	   }else{
			return $this->getBaseUrlBackend()."index.php?ctrl=PageAdmin&action=edit_page&url=".$this->page;
	   }
   }



  





	



	





	
}


?>