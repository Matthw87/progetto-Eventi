<?php
namespace Marion\Entities\Cms;
use Marion\Entities\Cms\Interfaces\MenuItemFrontendInterface;
class PageItemFrontend implements MenuItemFrontendInterface{
	
	public static function getGroupName(): string{
		 return _translate('pages.page');
	}


	public static function getUrl(array $params):string{
		$locale = $params['locale'];
		$id = $params['value'];
		
		$page = Page::withId($id);
		if( is_object($page) ){
			return $page->getUrl($locale);
		}
		
		return '';
	}
	
	public static function getPages():array{
		
		$list = Page::prepareQuery()
            ->where('visibility',1)
            ->whereExpression("widget is NULL OR widget = 0")
            ->get();
		//debugga($list);exit;
        $list_url = array();
		if( okArray($list) ){
			foreach($list as $v){
				$list_url[$v->id] = $v->get('title');
			}
		}

		return $list_url;
	}


}
?>