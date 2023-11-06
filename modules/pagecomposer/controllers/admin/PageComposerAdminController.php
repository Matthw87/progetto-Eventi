<?php
use ScssPhp\ScssPhp\Compiler;
use Marion\Controllers\Controller;
use Marion\Core\{Marion,Base};
use Marion\Entities\Cms\Page;
use Marion\Entities\Cms\PageComposer;
use Marion\Components\PageComposerComponent;
use Marion\Support\Cache;
use Marion\Support\Form\Traits\FormHelper;

class PageComposerAdminController extends Controller{
	use FormHelper;
	public $_auth = 'cms';


	public $enable_preview = true;
	

	function display(){
		$action = $this->getAction();
		$this->setMenu('cms_page');
		
		
		switch($action){
			/*case 'import':
				$id = _var('id');
				$json = '[{"id":"2701","parent":"2699","position":"2","id_adv_page":"23","title":null,"type":"col-50","id_page":null,"module":null,"orderView":"2","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2700","parent":"2699","position":"1","id_adv_page":"23","title":null,"type":"col-50","id_page":null,"module":null,"orderView":"1","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2699","parent":"0","position":"0","id_adv_page":"23","title":"2 col.","type":"row","id_page":"1","module":null,"orderView":"1","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2703","parent":"0","position":"0","id_adv_page":"23","title":"1 col.","type":"row","id_page":"1","module":null,"orderView":"4","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2704","parent":"2703","position":"1","id_adv_page":"23","title":null,"type":"col-100","id_page":null,"module":null,"orderView":"1","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2722","parent":"2719","position":"0","id_adv_page":"23","title":"Popup","type":"module","id_page":"0","module":"593","orderView":"9","visibility":"1","module_function":"WidgetPopup","content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2707","parent":"2704","position":"0","id_adv_page":"23","title":"Button","type":"module","id_page":"0","module":"12","orderView":"7","visibility":"1","module_function":"WidgetButton","content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2706","parent":"2704","position":"0","id_adv_page":"23","title":"Button","type":"module","id_page":"0","module":"12","orderView":"6","visibility":"1","module_function":"WidgetButton","content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2705","parent":"2704","position":"0","id_adv_page":"23","title":"Button","type":"module","id_page":"0","module":"12","orderView":"5","visibility":"1","module_function":"WidgetButton","content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2702","parent":"2701","position":"0","id_adv_page":"23","title":"Widget Popup Side","type":"module","id_page":"0","module":"932","orderView":"3","visibility":"1","module_function":"WidgetPopupSide","content":null,"id_html":null,"class_html":null,"parameters":"a:9:{s:16:\"background_popup\";s:7:\"#995959\";s:19:\"colore_titolo_popup\";s:7:\"#834444\";s:18:\"background_bottone\";s:0:\"\";s:20:\"colore_testo_bottone\";s:7:\"#b33333\";s:12:\"target_blank\";N;s:5:\"image\";a:2:{s:2:\"en\";s:28:\"upload-file-502372992018.jpg\";s:2:\"it\";s:28:\"upload-file-502372992018.jpg\";}s:3:\"url\";a:2:{s:2:\"en\";s:0:\"\";s:2:\"it\";s:0:\"\";}s:5:\"title\";a:2:{s:2:\"en\";s:3:\"sss\";s:2:\"it\";s:4:\"ssss\";}s:12:\"title_button\";a:2:{s:2:\"en\";s:5:\"sssss\";s:2:\"it\";s:4:\"ssss\";}}","block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2721","parent":"2717","position":"4","id_adv_page":"23","title":null,"type":"col-25","id_page":null,"module":null,"orderView":"4","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2720","parent":"2717","position":"3","id_adv_page":"23","title":null,"type":"col-25","id_page":null,"module":null,"orderView":"3","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2719","parent":"2717","position":"2","id_adv_page":"23","title":null,"type":"col-25","id_page":null,"module":null,"orderView":"2","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2718","parent":"2717","position":"1","id_adv_page":"23","title":null,"type":"col-25","id_page":null,"module":null,"orderView":"1","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null},{"id":"2717","parent":"2701","position":"0","id_adv_page":"23","title":"4 col.","type":"row","id_page":"0","module":null,"orderView":"8","visibility":"1","module_function":null,"content":null,"id_html":null,"class_html":null,"parameters":null,"block":"content","cache":"0","animate_css":null,"background_url":null,"background_repeat":null,"background_position":null,"background_size":null,"background_attachment":null}]';

				$dati = json_decode($json);
				$tree = Base::buildTree($dati);
				$database = _obj('Database');
				$database->delete('composed_page_composition_tmp',"id_adv_page={$id}");
				foreach($tree as $v){
					$this->importRow($id,$v);
				}
				

				debugga('finito');
				exit;*/
			case 'export':
				$id = _var('id');
				$database = Marion::getDB();
				$items = $database->select('*','composed_page_composition',"composed_page_id={$id} order by orderView");

				
				echo json_encode($items);
				exit;
				//debugga($items);exit;
				//debugga($id);exit;
				exit;
			case 'sort_items':
				$id_box = _var('id_box');
				$database = Marion::getDB();
				$items = $database->select('*','composed_page_composition_tmp',"parent={$id_box} order by orderView");
				if( okArray($items) ){
					foreach($items as $k => $v){
						$function = $v['module_function'];
						
						if( $function && class_exists($function) ){
							$object = new $function();
							
							$items[$k]['img_logo'] = $object->getLogo($v);
							
	
						}
						
						switch($v['type']){
							case 'row':
								$items[$k]['img_logo'] = '../modules/pagecomposer/img/row-blank.png';
								break;
						}
					}
				}
				
				

				$this->setVar('id_box', $id_box);
				$this->setVar('items', $items);
				$this->output('@pagecomposer/admin/sort_items.htm');
				exit;
			case 'edit-accordion':
			case 'edit-tab':
				$this->editContainer('page_composer_tab_conf','form_tab.htm');
				
				exit;
			case 'edit-row':
				$this->editContainer('page_composer_row_conf','form_row.htm');
				exit;
			case 'edit-accordions':
				$this->editContainer('page_composer_accordions_conf','form_accordions.htm');
				
				exit;
			case 'edit-tabs':
				$this->editContainer('page_composer_tabs_conf','form_tabs.htm');
				
				exit;
			case 'edit-row-icon-left':

				$this->editContainer('page_composer_row_icon','form_row-icon.htm');
				
				exit;
			case 'edit-popup':
				

				$this->editContainer('page_composer_popup_conf','form_popup.htm');
				
				exit;
			case 'editor_css':
				$this->editor_css();
				exit;
			
			case 'edit_ok':
				$id= _var('id');
				$block = _var('block');
				$database = Marion::getDB();
				
				$database->delete('composed_page_composition',"composed_page_id={$id}");
				
				$database->execute("INSERT composed_page_composition SELECT * FROM composed_page_composition_tmp where composed_page_id={$id};");
				$database->delete('composed_page_composition_tmp',"composed_page_id={$id}");


				$this->clean($id);

				$path = media_dir('pagecomposer/js');

				if( file_exists($path.'/js_head_tmp_'.$id.".js") ){
					$js_head = file_get_contents($path.'/js_head_tmp_'.$id.".js");
					if( $js_head ){
						file_put_contents($path.'/js_head_'.$id.".js",$js_head);
					}else{
						if( file_exists($path.'/js_head_'.$id.".js")){
							unlink($path.'/js_head_'.$id.".js");
						}
						
					}
				}
				
				if( file_exists($path.'/js_end_tmp_'.$id.".js") ){
					$js_end = file_get_contents($path.'/js_end_tmp_'.$id.".js");
					if( $js_end ){
						file_put_contents($path.'/js_end_'.$id.".js",$js_end);
					}else{
						if( file_exists($path.'/js_end_'.$id.".js") ){
							unlink($path.'/js_end_'.$id.".js");
						}
						
					}
				}


				$path = media_dir('pagecomposer/css');
				if( file_exists($path.'/css_tmp_'.$id.".css") ){
					file_put_contents($path.'/css_'.$id.".css",file_get_contents($path.'/css_tmp_'.$id.".css"));
				}
				
				$database->execute("update composed_pages set custom_css=custom_css_tmp,custom_js_head=custom_js_head_tmp,custom_js_end=custom_js_end_tmp where id={$id}");
				$this->log($id,'salvataggio');
				
				header("Location: index.php?ctrl=PageComposerAdmin&mod=pagecomposer&action=edit&id={$id}&block={$block}&saved=1");
				exit;
				break;
			/*case 'edit_tabs':

				$options_conf = array(
					'form_control' => 'page_composer_tabs_conf',
					'module' => 'pagecomposer',
					'template_html' => 'form_tabs.htm',
				);
				$obj = new PageComposerComponentConf($options_conf);
				$obj->render();
				
				break;
			case 'edit_tab':

				$options_conf = array(
					'form_control' => 'page_composer_tab_conf',
					'module' => 'pagecomposer',
					'template_html' => 'form_tab.htm',
				);
				$obj = new PageComposerComponentConf($options_conf);
				$obj->render();
				
				break;
			case 'edit_popup':

				$options_conf = array(
					'form_control' => 'page_composer_popup_conf',
					'module' =>'pagecomposer',
					'template_html' => 'form_popup.htm',
				);
				$obj = new PageComposerComponentConf($options_conf);
				$obj->render();
				
				

				break;*/
			case 'reset':
				$id = _var('id');
				$block = _var('block');
				$database = Marion::getDB();
				$database->delete('composed_page_composition_tmp',"composed_page_id={$id}");
				header("Location: index.php?ctrl=PageComposerAdmin&mod=pagecomposer&action=edit&id={$id}&block={$block}&saved=1");
				exit;
				
			default:
				
				
				$this->editor();
				break;
				
		}

		
	}

	

	

	function setMedia(){
		$action = $this->getAction();

		$widget_actions = array('edit-popup','edit-row-icon-left','edit-tab','edit-tabs');
		if( in_array($action,$widget_actions)){
			$this->registerCSS($this->getBaseUrl().'modules/pagecomposer/css/conf.css');
		}
		//$this->registerCSS($this->getBaseUrl().'css/admin/magnific-popup.css');
		$this->registerCSS($this->getBaseUrl().'modules/pagecomposer/css/pagecomposer_admin.css');
		$this->registerJS($this->getBaseUrl().'assets/plugins/jquery-ui/jquery-ui-1.10.4.min.js','end');
		$this->registerJS($this->getBaseUrl().'assets/plugins/jquery-nestable/jquery.nestable.js','end');
		//$this->registerJS($this->getBaseUrl().'js/admin/custom/jquery.magnific-popup.min.js','end');
		//$this->registerJS($this->getBaseUrl().'modules/pagecomposer/js/pagecomposer_admin_new.js','end');
		if( $action != 'sort_items'){
			$this->registerJS($this->getBaseUrl().'modules/pagecomposer/js/pagecomposer_admin_new.js?v=3','end');
		}
		

		if( $action == 'sort_items'){
			$this->registerJS($this->getBaseUrl().'assets/plugins/jquery-nestable/jquery.nestable.js','end');
			$this->registerJS($this->getBaseUrl().'modules/pagecomposer/js/sort_items.js','end');
		}
	}



	function getWidgets(){
		$widgets = array( 
			array(
				'title' => 'blank',
				'type' => 'row',
				'module' => '',
				'id' => 0,
				'icon' => '../modules/pagecomposer/img/row-blank.png',
				'icon-active' => '../modules/pagecomposer/img/row-blank-active.png',
				'repeat' => 1,
			),
			array(
				'title' => '1 col.',
				'type' => 'row-1',
				'module' => '',
				'id' => 0,
				'icon' => '../modules/pagecomposer/img/row-1.png',
			'icon-active' => '../modules/pagecomposer/img/row-1-active.png',
				'repeat' => 1,
			),
			array(
				'title' => '2 col.',
				'type' => 'row-2',
				'module' => '',
				'id' => 0,
				'icon' => '../modules/pagecomposer/img/row-2.png',
			'icon-active' => '../modules/pagecomposer/img/row-2-active.png',
				'repeat' => 1,
			),
		
			array(
				'title' => '3 col.',
				'type' => 'row-3',
				'module' => '',
				'id' => 0,
				'icon' => '../modules/pagecomposer/img/row-3.png',
				'icon-active' => '../modules/pagecomposer/img/row-3-active.png',
				'repeat' => 1,
			),
			array(
				'title' => '4 col.',
				'type' => 'row-4',
				'module' => '',
				'id' => 0,
				'icon' => '../modules/pagecomposer/img/row-4.png',
				'icon-active' => '../modules/pagecomposer/img/row-4-active.png',
				'repeat' => 1,
			),
		
			array(
				'title' => '2 col. 75 / 25',
				'type' => 'row-75-25',
				'module' => '',
				'id' => 0,
				'icon' => '../modules/pagecomposer/img/row-75-25.png',
				'icon-active' => '../modules/pagecomposer/img/row-75-25-active.png',
				'repeat' => 1,
			),
			array(
				'title' => '2 col. 25 / 75',
				'type' => 'row-25-75',
				'module' => '',
				'id' => 0,
				'icon' => '../modules/pagecomposer/img/row-25-75.png',
				'icon-active' => '../modules/pagecomposer/img/row-25-75-active.png',
				'repeat' => 1,
			),
			/*array(
				'title' => 'row with icon left',
				'type' => 'row-with-icon-left',
				'module' => '',
				'id' => 0,
				'icon' => '/img/composer/row-1.png',
				'icon-active' => '/img/composer/row-1-active.png',
				'repeat' => 1,
			),*/
			
		);

		return $widgets;
	}
	

	function editor_css_ajax(){
		$formdata = $this->getFormdata();
		$id = $formdata['id_box'];
		unset($formdata['id_box']);
		
		$database = Marion::getDB();
		$database->update('composed_page_composition_tmp',"id={$id}",$formdata);
		
		$risposta = array(
				'result' => 'ok',
				'id' => $id
				
		);
		echo json_encode($risposta);
		exit;
	}

	function editor_css(){
		$id = _var('id_box');
		$database = Marion::getDB();
		$dati = $database->select('*','composed_page_composition_tmp',"id={$id}");
		if( okArray($dati) ){
			
			$this->setVar('dati_box',$dati[0]);
		}
		

		$input_url = array(
			'name' => 'formdata[background_url]',
			'id' => 'background_url',
			'value' => $dati[0]['background_url'],
			'type' => 'hidden',
			'etichetta' => 'background-url',
			'descrizione' => '*Puoi caricare un immagine di bakground per questo elemento'
		);

		$input_url_webp = array(
			'name' => 'formdata[background_url_webp]',
			'id' => 'background_url_webp',
			'value' => $dati[0]['background_url_webp'],
			'type' => 'hidden',
			'etichetta' => 'background-url webp',
			'descrizione' => '*Puoi caricare un immagine di bakground per questo elemento in formato webp.'
		);

		$this->setVar('input_url',$input_url);
		$this->setVar('input_url_webp',$input_url_webp);
		
		$this->output('@pagecomposer/admin/editor_css.htm');
		
		
	}


	function editor(){
		//$this->setMenu('coupon_manage');
		
		
		$id = _var('id');

		
		


		$page = Page::prepareQuery()->where('composed_page_id',$id)->getOne();
		
		if( is_object($page) ){
			$this->setVar('titolo',$page->get('title'));
		}
		if( defined('_PAGE_COMPOSER_DASHBOARD_PAGE_ID_') ){
			if( $id == _PAGE_COMPOSER_DASHBOARD_PAGE_ID_ ){
				$this->enable_preview = false;
				$this->setVar('titolo','Dashboard');
			}
		}
		
		$_template_vars['enable_preview'] = $this->enable_preview;

		$block = _var('block');
		$database = Marion::getDB();

		
		$_template_vars['last_logs'] = $this->getLastlogs();
		//$composer_template = Marion::widget(basename(__DIR__));
		if( defined('_PAGE_COMPOSER_DASHBOARD_PAGE_ID_') && $id != _PAGE_COMPOSER_DASHBOARD_PAGE_ID_ ){
			$_page = $database->select('*','composed_pages',"id={$id}");
			if( okArray($_page) ){
				$_page = $_page[0];
				$layout = $database->select('*','composed_page_layouts',"id={$_page['layout_id']}");
				if( okArray($layout) ){
					$_template_vars['layout_name'] = $layout[0]['name'];
					$_template_vars['layout'] = $layout[0]['label'];
					$_template_vars['blocks'] = json_decode($layout[0]['blocks']);
					
				}
			}
		}else{
			$_template_vars['layout_name'] = 'Dashboard';
			$_template_vars['layout'] = 'fullpage';
			$_template_vars['blocks'] = ['content'];
			
		}
		



		

		
		if( !$block && count($_template_vars['blocks']) == 1 ){
			header("Location: index.php?ctrl=PageComposerAdmin&mod=pagecomposer&id={$id}&block=".$_template_vars['blocks'][0]);
		}
		
		if( count($_template_vars['blocks']) == 1 ){
			$_template_vars['no_select_block'] = true;
		}

		$_template_vars['id_page'] = $id;
		$_template_vars['block'] = $block;
		$_template_vars['blocco'] = $block;
		
		
		$check = $database->select('count(*) as cont','composed_page_composition_tmp',"composed_page_id={$id}");
		
		if( $check[0]['cont'] > 0 ){
			$_template_vars['bozza'] = true;
			
		}else{
			$database->delete('composed_page_composition_tmp',"composed_page_id={$id}");
			$database->execute("INSERT composed_page_composition_tmp SELECT * FROM composed_page_composition where composed_page_id={$id}");
		}

		
		$composer = new PageComposer($id);
		$tree = $composer->buildTreeBlockEdit($block);
		
		
		global $_functions_pagecomposer_twig;
		$_functions_pagecomposer_twig = array(
				new \Twig\TwigFunction('page_composer_buttons', function ($dati) {
					
					$loader = new \Twig\Loader\FilesystemLoader('../modules/pagecomposer/templates/admin');
					$twig = new \Twig\Environment($loader,array());
					echo $twig->render('buttons.htm', array('v2'=>$dati));
				}),
				new \Twig\TwigFunction('page_composer_add_buttons', function ($dati) {
					
					$loader = new \Twig\Loader\FilesystemLoader('../modules/pagecomposer/templates/admin');
					$twig = new \Twig\Environment($loader,array());
					echo $twig->render('add_buttons.htm', array('v2'=>$dati));
				}),
				new \Twig\TwigFunction('page_composer_element', function ($dati) {
					
					$loader = new \Twig\Loader\FilesystemLoader('../modules/pagecomposer/templates/admin');
					$twig = new \Twig\Environment($loader,array());
					global $_functions_pagecomposer_twig;
					foreach($_functions_pagecomposer_twig as $func){
							
							$twig->addFunction($func);
					}
					switch($dati['type']){
						case 'tabs':
							
							$_template = 'pc_tabs.htm';
							break;
						case 'row':

							/*$_has_tabs = false;
							if( okArray($dati['children']) ){
								foreach($dati['children'] as $z){
									if( $z['type'] == 'tabs'){
										$_has_tabs = true;
										break;
									}
								}
							}
							$dati['has_tabs'] = $_has_tabs;*/
							
							
							$_template ='pc_row.htm';
							break;
						case 'accordion_container':
							
							$_template = 'pc_accordion_container.htm';
							break;
						case 'popup_container':
							
							$_template = 'pc_popup_container.htm';
							break;
						case 'row-with-icon-left':
							$_template = 'pc_row.htm';
							break;
						default:
							$_template = 'pc_element.htm';
							break;
					}
					echo $twig->render($_template, array('v2'=>$dati));
				}),
				new \Twig\TwigFunction('hasSpaceRowComposer', function ($riga) {
					
					if( $riga['type'] == 'row' ){
						$tot = 0;
						foreach($riga['children'] as $v1){
							$explode = explode('-',$v1['type']);

							$tot += $explode[1];
							
						}
						
					}
					if( $tot >= 90 ) return false;
					return true;
				}),
				new \Twig\TwigFunction('classStartSpaceComposer', function ($riga) {
					
					if( $riga['type'] == 'row' ){
						$tot = 0;
						foreach($riga['children'] as $v1){
							$explode = explode('-',$v1['type']);

							$tot += $explode[1];
							
						}
						
					}
					if( $tot ){
						switch($tot){
							case '0':
								$class = "vuoto-100";
								break;
							case '25':
								$class = "vuoto-75";
								break;
							case '33':
								$class = "vuoto-66";
								break;
							case '50':
								$class = "vuoto-50";
								break;
							case '66':
								$class = "vuoto-33";
								break;
							case '75':
								$class = "vuoto-25";
								break;
							default:
								$class = 'vuoto-no';
								break;
								

						}
					}
					
					return $class;
					
				}),
				new \Twig\TwigFunction('isAvailableColumnComposer', function ($riga,$size) {
					
					if( $riga['type'] == 'row' ){
						$tot = 0;
						foreach($riga['children'] as $v1){
							$explode = explode('-',$v1['type']);

							$tot += $explode[1];
							
						}
						
					}
					
					if( $tot + $size > 100 ){
						return false;
					}
					return true;
				})
			);





		
		
		foreach($_functions_pagecomposer_twig as $func){
			//debugga($func);exit;
			
			$this->addTemplateFunction($func);
		}
			

		
		
		$widgets = $this->getWidgets();
		
		
		$_template_vars['widgets'] = $widgets;

		
		$_template_vars['items'] = $tree;
		
		foreach($_template_vars as $k => $v){
			$this->setVar($k,$v);
		}

		
		if( _var('fromAjax') ){
			ob_start();
			$this->output('@pagecomposer/admin/tree_ajax.htm');
			$html = ob_get_contents();
			ob_end_clean();

			$risposta = array(
				'result' => 'ok',
				'html' => $html
			);
			echo json_encode($risposta);
			exit;
		}else{
			
			$this->output('@pagecomposer/admin/tree.htm');
		}
		

	}


	function ajax(){
		
		$action = $this->getAction();
		
		$id = _var('id');
		$database = Marion::getDB();
		switch($action){
			case 'save_custom_name':
				$id_widget = _var('id_widget');
				$name = _var('name');
				$database->update('composed_page_composition_tmp',"id={$id_widget}",array('custom_name'=>$name));
				$risposta = array(
					'result' => 'ok',
				);
				echo json_encode($risposta);
				exit;
				
			case 'editor_css':
				$this->editor_css_ajax();
				exit;
			case 'save_css':
				$id = _var('id');
				$css = _var('css');
				$js_head = _var('js_head');
				$js_end = _var('js_end');
				$path_media = media_dir('pagecomposer');
				//debugga($_POST);exit;
				$scss = new Compiler();
				try{
					$data_tmp = $css;
					
					$scss_variables = [
						[
							'variable' => 'BASE_URL',
							'value' =>  $this->getBaseUrl(),
							'description' => 'percorso base del sito'
						],
						[
							'variable' => 'THEME_DIR',
							'value' =>  $this->getBaseUrl()."themes/"._MARION_THEME_,
							'description' => 'percorso del tema corrente'
						],
					];
					Marion::do_action('action_register_scss_variables',[&$scss_variables]);
					$parameters = [];
					foreach($scss_variables as $v){
						$parameters[$v['variable']] = $v['value'];
					}
					$string = '';
					foreach($parameters as $key => $value){
						$string .= '$'.$key.':"'.$value.'";';
					}
					
					$data_tmp = $string.$data_tmp;
					//file_put_contents($path_media.'/css/style_'.$id.".scss",$css);
					$compressed = $scss->compileString($data_tmp);
					file_put_contents($path_media.'/css/css_tmp_'.$id.".css",$compressed->getCss());
				}catch(Exception $e){

					$risposta = array(
						'result' => 'nak',
						'error' =>  $e->getMessage()
					);
					echo json_encode($risposta);
					exit;
					
					
				}
				
				file_put_contents($path_media.'/js/js_head_tmp_'.$id.".js",$js_head);
				file_put_contents($path_media.'/js/js_end_tmp_'.$id.".js",$js_end);
				
		
				$toinsert = array(
					'custom_css_tmp' => $css,
					'custom_js_head_tmp' => $js_head,
					'custom_js_end_tmp' => $js_end,
				);
				
				$database->update('composed_pages',"id={$id}",$toinsert);
				
				$risposta = array(
					'result' => 'ok',
					'css' => file_get_contents($path_media.'/css/css_tmp_'.$id.".css")
				);
				echo json_encode($risposta);
				exit;
				break;
			case 'edit_page_ajax':
			case 'edit_page':
				$id = _var('id');

				$id_home = _var('id');
				$block = _var('block');
				
				
				$_page = $database->select('*','page_advanced',"id={$id}");
				if( okArray($_page) ){
					$_page = $_page[0];
					$layout = $database->select('*','layout_page',"id={$_page['id_layout']}");
					if( okArray($layout) ){
						//$composer_template->blocks = json_decode($layout[0]['blocks']);
						
					}
				}
				

				//$composer_template->id_page = $id;
				//$composer_template->block = $block;
				
				if( $action == 'edit_page' ){
					$check = $database->select('count(*) as cont','composed_page_composition_tmp',"composed_page_id={$id}");
					
					if( $check[0]['cont'] > 0 ){
						//$composer_template->bozza = true;
						
					}else{
						$database->delete('composed_page_composition_tmp',"composed_page_id={$id}");
						$database->execute("INSERT composed_page_composition_tmp SELECT * FROM composed_page_composition where composed_page_id={$id}");
					}

				}
				$composer = new PageComposer($id_home);
				$tree = $composer->buildTreeBlockEdit($block);
				

				
				//$widgets = array_widget_base_pagecomposer();

				
				//$composer_template->widgets = $widgets;

				
				//$composer_template->items = $tree;

				if( $action == 'edit_page_ajax' ){
					ob_start();
					//$composer_template->output('tree_ajax.htm');
					$html = ob_get_contents();
					ob_end_clean();

					$risposta = array(
						'result' => 'ok',
						'html' => $html
					);
					echo json_encode($risposta);
					exit;
				}else{
					//$composer_template->output('tree.htm');
				}
				break;
			case 'edit_page_ok':
				$id_page = _var('id');
				$block = _var('block');
				$database->delete('composed_page_composition',"composed_page_id={$id_page}");
				
				$database->execute("INSERT composed_page_composition SELECT * FROM composed_page_composition_tmp where composed_page_id={$id_page};");
				$database->delete('composed_page_composition_tmp',"composed_page_id={$id_page}");

				$path = media_dir('pagecomposer');// $_SERVER['DOCUMENT_ROOT']._MARION_ROOT_DIR_;

				
				file_put_contents($path.'/js/js_head_'.$id_page.".js",file_get_contents($path.'/js/js_head_tmp_'.$id_page.".js"));
				file_put_contents($path.'/js/js_end_'.$id_page.".js",file_get_contents($path.'/js/js_end_tmp_'.$id_page.".js"));
				file_put_contents($path.'/css/css_'.$id_page.".css",file_get_contents($path.'/css/css_tmp_'.$id_page.".css"));

				
				$database->execute("update page_advanced set custom_css_tmp=custom_css where id={$id_page}");
				//$composer_template->link="index.php?action=edit_page&id={$id_page}&block={$block}";
				//$composer_template->output('continua.htm');

				break;
			case 'get_widgets':
				$id_page = _var('id');
				$block = _var('block');
				$id_row = _var('id_row');
				$position = _var('position');
				$component = $database->select('*','composed_page_composition_tmp',"id={$id_row}");

				if( okArray($component) ){
					$comp = $component[0];
					$box = PageComposerComponent::getTypeBox($comp['type']);
				}
				
				$module_widgets = $database->select('w.*,m.kind','widgets as w join modules as m on m.id=w.module_id',"m.active=1 AND (w.composed_page_id IS NULL OR w.composed_page_id={$id_page})");
				//debugga($module_widgets);exit;
				//debugga(_PAGE_COMPOSER_DASHBOARD_PAGE_ID_);exit;
				//PRENDO SOLO I WIDGET DELLA DASHBOARD
				if( defined('_PAGE_COMPOSER_DASHBOARD_PAGE_ID_') && $id_page == _PAGE_COMPOSER_DASHBOARD_PAGE_ID_ ){
					foreach($module_widgets as $k => $w){
						$restrictions = unserialize($w['restrictions']);
						
						if( !okArray($restrictions) || !in_array('dashboard',$restrictions) ){
							unset($module_widgets[$k]);
						}
					}
				}
				
				$_widgets = [];
				if( okArray($module_widgets) ){
					foreach($module_widgets as $v){
						if( class_exists($v['function']) ){
							$class = $v['function'];
							$obj = new $class();
							$img_logo = $obj->getLogo();
							if( !$obj->isAvailable($box) ) continue;
						}
						$_widgets[] = array(
							'title' => $v['name'],
							'type' => 'module',
							'module' => $v['module_id'],
							'function' => $v['function'],
							'repeat' => $v['repeatable'],
							'id' => 0,
							'type_module' => $v['kind'],
							'url_edit' => $v['url_conf'],
							'img_logo' => $img_logo
						);

					}
				}

				
				
				
				
				
				$query = Page::prepareQuery();
				
				
				$query->where('theme',Marion::getConfig('SETTING_THEMES','theme'))->where('widget',1);
				

				$page = $query->get();
				if( okArray($page) ){
					foreach($page as $p){
						$_widgets[] = array(
							'title' => $p->get('url'),
							'type' => 'page',
							'module' => '',
							'id' => $p->id,
							'type_module' => 'pagine',
							'repeat' => 1,
							'img_logo' => '/modules/widget_html/img/logo.png'
						);

					}
				}
				$widgets = [];
				if( okArray($_widgets)){
					foreach($_widgets as $v){
						$widgets[$v['module']."-".$v['type']."-".$v['id']."-".$v['function']] = $v;
						
					}
				}
				
				

				
				
				$list = $database->select('*','composed_page_composition_tmp',"composed_page_id={$id_page} AND block='{$block}' order by orderView ASC");
				
				$list_tmp = $list;
				unset($list);
				
				//debugga($list_tmp);exit;
				foreach($list_tmp as $k => $v){
					$list[$v['id']] = $v;
					$key = '';
					if( $v['type'] == 'page'){
						$list[$v['id']]['url_edit'] = $this->getBaseUrlBackend().'index.php?ctrl=Page&id='.$v['id_page'];
					}else{

						
						$key = $v['module']."-module-0-".$v['module_function'];
						
						if( isset($widgets[$key]) ){
							$list[$v['id']]['url_edit'] = $widgets[$key]['url_edit'];
						}
						
					}
					if( isset($widgets[$key]) && !$widgets[$key]['repeat'] ){
					
						unset($widgets[$key]);
					}
					
				}
				if( okArray($widgets)){
					foreach($widgets as $v){
						$gruppo[$v['type_module']][] = $v;
					}
				}
				

				$widgets = $this->getWidgets();
				foreach($widgets as $k => $v){
					$widgets[$k]['img_logo'] = $v['icon'];
				}
				$widgets[] = array(
							'title' => 'tabs',
							'type' => 'tabs',
							'module' => '',
							'id' => 0,
							'type_module' => 'elements',
							'repeat' => 1,
							//'img_logo' => '/modules/widget_html/img/logo.png'
						);

				$widgets[] = array(
							'title' => 'popup',
							'type' => 'popup_container',
							'module' => '',
							'id' => 0,
							'type_module' => 'elements',
							'repeat' => 1,
							//'img_logo' => '/modules/widget_html/img/logo.png'
						);
				$widgets[] =   array(
							'title' => 'accordions',
							'type' => 'accordion_container',
							'module' => '',
							'id' => 0,
							'type_module' => 'elements',
							'repeat' => 1,
							//'img_logo' => '/modules/widget_html/img/logo.png'
						);

				
				$gruppo['STRUTTURA'] = $widgets;

				$this->setVar('active_tab',array_keys($gruppo)[0]);

				/*$gruppo['ELEMENTI'][] = array(
					'title' => 'spazio',
					'type' => 'space',
					'module' => '',
					'id' => 0,
					'img_logo' => '/img/composer/space.png',
					'repeat' => 1,
				);*/
				
				
				
				
				$this->setVar('id_row',$id_row);
				$this->setVar('position',$position);
				$this->setVar('group_widgets',$gruppo);
				
				ob_start();
				$this->output('@pagecomposer/admin/widgets.htm');
				$html = ob_get_contents();
				ob_end_clean();


				$risposta = array(
					'result' => 'ok',
					'html' => $html
				);
				echo json_encode($risposta);
				exit;
				


				break;
			case 'del_block':
				
				
				$id = _var('id');
				PageComposer::removeNode($id);
				
				
				$risposta = array(
					'result' => 'ok',
					//'html' => $html
				);
				echo json_encode($risposta);
				break;
			case 'cache_block':
				$id = _var('id');
				$cache= !(int)_var('cache');
				$array = array(
					'cache' => $cache
				);

				
				foreach(Marion::getConfig('locale','supportati') as $lo ){
					$key_cache = 'page_composer_block_'.$id."_".$lo;
					if( Cache::exists($key_cache) ){
						
						Cache::remove($key_cache);
					}
				}

				$database->update('composed_page_composition_tmp',"id={$id}",$array);
				
				
				
				$risposta = array(
					'result' => 'ok',
					'cache' => $cache,
				);
				echo json_encode($risposta);

				break;
			case 'save_block_css':
				$id = _var('id');

				$array['id_html'] = _var('id_html');
				$array['class_html'] = _var('class_html');
				$array['animate_css'] = _var('animate_css');
				$database->update('composed_page_composition_tmp',"id={$id}",$array);
				
				$risposta = array(
					'result' => 'ok',
					//'html' => $html
				);
				echo json_encode($risposta);

				break;
			case 'paste_box':

				$ids_box = json_decode(_var('ids_box'));
			
				$parent = _var('parent');
				foreach($ids_box as $id_box){
					
					PageComposer::copyNode($id_box,$parent);
				}
				$risposta = array(
					'result' => 'ok',
					//'html' => $html
				);
				echo json_encode($risposta);


				break;
			case 'add_block_to_page':
				
				$array['title'] = _var('title');
				$array['type'] = _var('type');
				$array['id_page'] = _var('id');
				$array['block'] = _var('block');
				//$composer_template->block =_var('block');
				$array['module'] = _var('module');
				$array['module_function'] = _var('function');
				
				$id_home = _var('id_home');
				if( !in_array($array['type'],array('row-with-icon-left'))){
					if( preg_match('/row-/',$array['type']) ){
						$array['type'] = 'row';
					}
				}
				if( $array['module']  ){
					$module_widget = $database->select('*','widgets',"module_id={$array['module']}");
					if( okArray($module_widget) ){
						//$array['module_function'] = $module_widget[0]['function'];
					}
				}

				/*if( $array['type'] == 'element' ){
					switch($array['title']){
						case 'space50':
							$array['content'] = "<div class='space50'></div>";
							break;
						case 'space30':
							$array['content'] = "<div class='space30'></div>";
							break;
					}
				}	*/

				$last = $database->select('max(orderView) as max','composed_page_composition_tmp',"composed_page_id={$id_home}");
				$max = $last[0]['max']+1;
				$array['orderView'] = $max;
				$array['composed_page_id'] = $id_home;
				$array['parent'] = _var('parent');
				$array['position'] = _var('position');
				if( _var('type') == 'tab' || _var('type') == 'accordion' ){
					$last = $database->select('max(position) as max','composed_page_composition_tmp',"composed_page_id={$id_home} AND parent={$array['parent']}");
					$position = $last[0]['max']+1;
					$array['position'] = $position;
				}

				
				$array['id'] = $database->insert('composed_page_composition_tmp',$array);
				if( $array['module_function'] ){
					$id_toreturn = $array['id'];
				}
				$num = 0;
				switch(_var('type')){
					case 'tabs':
						$child1 = array(
							'composed_page_id' => $id_home,
							'parent' => $array['id'],
							'position' => 1,
							'type' => 'tab',
							'orderView' => 1,
							'block' => $array['block'],
						);
						$child2 = array(
							'composed_page_id' => $id_home,
							'parent' => $array['id'],
							'position' => 2,
							'type' => 'tab',
							'orderView' => 2,
							'block' => $array['block'],
						);
						$database->insert('composed_page_composition_tmp',$child1);
						$database->insert('composed_page_composition_tmp',$child2);

						break;
					case 'row-1':
						$num = 1;
						$type = 'col-100';
						break;
					case 'row-2':
						$num = 2;
						$type = 'col-50';
						break;
					case 'row-3':
						$num = 3;
						$type = 'col-33';
						break;
					case 'row-4':
						$num = 4;
						$type = 'col-25';
						break;
					case 'row-with-icon-left':
						
						$child1 = array(
							'composed_page_id' => $id_home,
							'parent' => $array['id'],
							'position' => 2,
							'type' => 'row-with-icon-left-col-right',
							'orderView' => 2,
							'block' => $array['block'],
						);
						$database->insert('composed_page_composition_tmp',$child1);
						break;
					case 'row-25-75':
						$child1 = array(
							'composed_page_id' => $id_home,
							'parent' => $array['id'],
							'position' => 1,
							'type' => 'col-25',
							'class_edit' => 'box-edit-25-sx',
							'orderView' => 1,
							'block' => $array['block'],
						);
						$child2 = array(
							'composed_page_id' => $id_home,
							'parent' => $array['id'],
							'position' => 2,
							'type' => 'col-75',
							'orderView' => 2,
							'block' => $array['block'],
						);
						$database->insert('composed_page_composition_tmp',$child1);
						$database->insert('composed_page_composition_tmp',$child2);
						
						break;
					case 'row-75-25':
						$child1 = array(
							'composed_page_id' => $id_home,
							'parent' => $array['id'],
							'position' => 1,
							'type' => 'col-75',
							'orderView' => 1,
							'block' => $array['block'],
						);
						$child2 = array(
							'composed_page_id' => $id_home,
							'parent' => $array['id'],
							'position' => 2,
							'type' => 'col-25',
							'class_edit' => 'box-edit-25-dx',
							'orderView' => 2,
							'block' => $array['block'],
						);
						$database->insert('composed_page_composition_tmp',$child1);
						$database->insert('composed_page_composition_tmp',$child2);
						
						break;
				}
				
				for( $k=1;$k<=$num;$k++ ){
					$child = array(
						'composed_page_id' => $id_home,
						'parent' => $array['id'],
						'position' => $k,
						'type' => $type,
						'orderView' => $k,
						'block' => $array['block'],
					);
					$database->insert('composed_page_composition_tmp',$child);
					
				}
				
				
				$risposta = array(
					'result' => 'ok',
					'dati' => $array,
					'id' => isset($id_toreturn)?$id_toreturn:null,
				);
				echo json_encode($risposta);

				break;
			case 'save_composition_box':

				$list = _var('list');
				$id = _var('id');
				$k = 1;
				foreach($list as $v){
					$database->update('composed_page_composition_tmp',"id={$v['id']}",array('orderView'=>$k));
					$k++;


				}
				$risposta = array(
						'result' => 'ok',
				);
				echo json_encode($risposta);
				break;
			case 'save_composition':

				$id_page = _var('id');
				$block = _var('block');
				$list = _var('list');
				foreach($list as $k => $v){
					$database->update('composed_page_composition_tmp',"id={$v['id']} AND composed_page_id={$id_page} AND block='{$block}'",array('orderView' => $k+1));
				}
				$risposta = array(
					'result' => 'ok'
				);
				echo json_encode($risposta);
				break;
			case 'sort_items':
				$id_box = _var('id_box');
				$items = $database->select('*','composed_page_composition_tmp',"parent={$id_box} order by orderView");
					
				foreach($items as $k => $v){
					$function = $v['module_function'];
					
					if( class_exists($function) ){
						$object = new $function();
						
						$items[$k]['img_logo'] = $object->getLogo($v);
						

					}
					
					switch($v['type']){
						case 'row':
							$items[$k]['img_logo'] = '/img/composer/row-blank.jpg';
							break;
					}
				}
				

				//$composer_template->id_box = $id_box;
				//$composer_template->items = $items;
				//$composer_template->output('page_composer_sort_items.htm');
				break;
			case 'save_order_row':
				$type = _var('type');
				$ids_box = json_decode(_var('items'));
				foreach($ids_box as $k => $v){
					if( $type == 'tab' ){
						$update = array(
							'orderView'=>($k+1),
							'position' => ($k+1),	
						);
					}else{

						$update = array(
							'orderView'=>($k+1)
						);

					}
					$database->update('composed_page_composition_tmp',"id={$v}",$update);
					
				}
				$risposta = array(
						'result' => 'ok',
				);
				echo json_encode($risposta);

				break;

		
			case 'export_row':

				
				$export = array();

				if( !$id ){
					$id_page = _var('id_page');
					/*$database = _obj('Database');
					$export = $database->select('*','composed_page_composition',"id_adv_page={$id_page} order by orderView,id");*/

					$export = $database->select('*','composed_page_composition_tmp',"composed_page_id={$id_page} order by orderView,id");
					
					/*$items = $database->select('*','composed_page_composition_tmp',"parent=0 AND id_adv_page={$id_page} order by orderView,id");
				
					if( okArray($items) ){
						
						foreach($items as $v){
							//$v['parent'] = 0;
							$this->exportRow($v,$export);
						}
					}*/
				}else{

					$items = $database->select('*','composed_page_composition_tmp',"id={$id} order by orderView,id");
				
					if( okArray($items) ){
						$parent = $items[0];
						$parent['parent'] = 0;
						$export[] = $parent;
					
						$items = $database->select('*','composed_page_composition_tmp',"parent={$id} order by orderView,id");
						if( okArray($items) ){
							foreach($items as $v){
								//$v['parent'] = 0;
								$this->exportRow($v,$export);
							}
						}
						
					}

				}
				
				
				
				
			
				echo json_encode($export);
				exit;
			case 'import_row':
				$sostituisci = _var('sostituisci');
				$id_parent = _var('id_box');
				$data = json_decode(trim(_var('data'),"'"));
				
				
				$tree = Base::buildTree($data);
				
				if( !$id_parent ){
					$check = true;
					foreach(array_values($tree) as $_row ){
						if( $_row->type != 'row' ){
							$check = false;
							break;
						}
					}
					if( !$check ){
						$risposta = array(
							'result' => 'nak',
							'error' => "Assicurati che i blocchi che stai importando siano di tipo riga."
						);
						echo json_encode($risposta);
						exit;
					}
					
				}

				uasort($tree,function ($a, $b) {
					if($a->orderView == $b->orderView) {
						return 0;
					}
					return ($a->orderView > $b->orderView) ? -1 : 1;
				});

				
				if( !$id_parent && $sostituisci){
					
					$database->delete('composed_page_composition_tmp',"composed_page_id={$id}");
				}
				

				

				foreach($tree as $v){
					$this->importRow($id,$v,$id_parent);
				}

				$risposta = array(
					'result' => 'ok',
				);
				echo json_encode($risposta);
				exit;

				
				exit;


				
		}

		//echo json_encode($risposta);
		
	}
	


	function log($id,$message){
		$database = Marion::getDB();
		$user = Marion::getUser();
		$toinsert = array(
			'id_user' => $user->id,
			'message' => $message,
			'id_page' => $id,
		);
		$database->insert('pagecomposer_log',$toinsert);
	}


	function getLastlogs(){
		$database = Marion::getDB();
		$id = _var('id');
		$list = $database->select('l.*,u.name,u.surname','pagecomposer_log as l join users as u on u.id=l.id_user',"id_page={$id} order by timestamp DESC LIMIT 1");
		

		return $list;
	

	}



	// EXPORT IMPORT FUNCTION

	function importRow($id_page,$data,$id_parent=0){

		$data = (array)$data;
		$data['parent'] = $id_parent;
		$data['composed_page_id'] = $id_page;
		unset($data['id']);
		$database = Marion::getDB();
		$children = $data['children'];
		unset($data['children']);

		$id_parent = $database->insert('composed_page_composition_tmp',$data);
		if( okArray($children) ){
			uasort($children,function ($a, $b) {
				if($a->orderView == $b->orderView) {
					return 0;
				}
				return ($a->orderView > $b->orderView) ? -1 : 1;
			});
			foreach($children as $v){
				$this->importRow($id_page,$v,$id_parent);
				
			}
		}

		return;

	}



	function exportRow($current,&$export){
		$export[] = $current;
		$database = Marion::getDB();
		$items = $database->select('*','composed_page_composition_tmp',"parent={$current['id']} order by orderView,id");
		if( okArray($items) ){
			foreach($items as $v){
				$this->exportRow($v,$export);
			}
		}

	}

	

	function editContainer($formcontrol,$templateform){



		$database = Marion::getDB();
		$this->id_box = _var('id_box');
		$this->setVar('id_box',_var('id_box'));
		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$dati = $formdata;
			$array = $this->checkDataForm($formcontrol,$formdata);
			if( $array[0] == 'ok'){

				if( $formcontrol == 'page_composer_tabs_conf' ){
					if( $array['menu_dimension'] + $array['content_dimension'] > 12 ){
						$array[0] = 'nak';
						$array[1] = 'Assicurati che la somma delle percentuali non superi il 100%';
					}
				}
			}
			if( $array[0] == 'ok'){

				
				unset($array[0]);
				
				$data = array();
				foreach($array as $k => $v){
					if( $k != '_locale_data'){
						$data[$k] = $v;
					}
				}
				if( array_key_exists('_locale_data',$array) ){
					foreach($array['_locale_data'] as $k =>$v){
						foreach($v as $k1 => $v1){
							$data[$k1][$k] = $v1;
						}
					}
				}
				
		
				
				$dati = serialize($data);

				if( $formcontrol == 'page_composer_row_conf'){
					$toupdate = $array;
					$database->update('composed_page_composition_tmp',"id={$this->id_box}",$toupdate);
					//debugga($database->lastquery);
				}else{
					$database->update('composed_page_composition_tmp',"id={$this->id_box}",array('parameters'=>$dati));
				}
				
				
				

				if( $formcontrol == 'page_composer_popup_conf'){
					$dati = $array;
				}
				if( $formcontrol == 'page_composer_row_conf'){
					$dati = $array;

					
				}

				if( $formcontrol == 'page_composer_accordions_conf'){
					$dati = $array;
				}

				

				
				$this->setVar('close_popup',1);
				$this->displayMessage('Dati salati con successo!','success');
			}else{
				$this->errors[]= $array[1];
			}
			
			
		}else{
			$data = $database->select('*','composed_page_composition_tmp',"id={$this->id_box}");
			
			if( okArray($data) ){
				
				if( $formcontrol == 'page_composer_row_conf'){
					
					$dati = $data[0];
				}else{
					if( isset($data[0]['parameters'])) {
						$dati = unserialize($data[0]['parameters']);
					}else{
						$dati = [];
					}
					
				}
				
			}
			
			
		}

		$dataform = $this->getDataForm($formcontrol,$dati);
		
		$this->setVar('dataform',$dataform);
		
		$this->output('@pagecomposer/admin/'.$templateform);

		
	}


	

	// FORM EDIT TABS
	function classContainerTabsVertical(){
		$num = 12;

		for( $k =1; $k<=$num; $k++ ){
			$perc = round((100*$k/12),2);
			$toreturn[$k] = 'col-md-'.$k." ({$perc}%)";
		}

		return $toreturn;
	}


	function dection_type_list(){
		return array(
			0 => 'Disabilitata',
			'server' => 'Server-side',
			'client' => 'Client-side',
		);
	}


	function clean($id){
		
		
		$database = Marion::getDB();
		$list = $database->select('*','composed_page_composition',"composed_page_id={$id}");
		$da_eliminare = array();
		$tot = 0;
		$parent = [];
		$num_refusi = 0;
		if( okArray($list)) {
			foreach($list as $v){
				if( $v['parent'] > 0 ){
					$tot++;
					$parent[$v['parent']][] = $v;
					
					//$assoc[$v['parent']][] = $v;
					
				}
				$ids[] = $v['id'];
			}
		}
		
		//debugga($tot);
		$iter = 0;
		while(count($parent) > 0 && $iter < 10){
			//debugga($tot);
			$iter++;
			
			foreach($parent as $p => $figli){
				if( !in_array($p,$ids) ){
					$da_eliminare[] = $p;
					foreach($figli as $v){
						$da_eliminare[] = $v['id'];
					}
					unset($parent[$p]);
				}
			}
			$num_refusi += count($da_eliminare);
			$where = '';
			foreach($da_eliminare as $v){
				$where .= "{$v},";
			}
			$where = preg_replace('/\,$/','',$where);

			if($where){
				$database->delete('composed_page_composition',"id IN ({$where}) AND composed_page_id={$id}");
			}
			
			//$database->delete('composed_page_composition',"id IN ({$where}) AND id_adv_page={$id}");
			$list = $database->select('*','composed_page_composition',"composed_page_id={$id}");
			$parent = array();
			$ids = array();
			$tot = 0;
			foreach($list as $v){
				if( $v['parent'] > 0 ){
					$tot++;
					$parent[$v['parent']][] = $v;
					
					
				}
				$ids[] = $v['id'];
			}
		}
		//$database->delete('composed_page_composition_tmp',"id_adv_page={$id}");
		/*$risposta = array(
			'result' => 'ok',
			'deleted' => $num_refusi
		);
		echo json_encode($risposta);
		exit;*/
		return true;
	}
	



}



?>