<?php

use Illuminate\Database\Capsule\Manager as DB;
use Marion\Controllers\ListAdminController;
use Marion\Support\Form\FormHelper;
use Marion\Support\ListWrapper\DataSource;
use Marion\Support\ListWrapper\ListHelper;

class SliderController extends ListAdminController{
	public $_auth = 'cms';
	private $path;
	

	function init($options = array())
	{
		parent::init($options);
		$this->path = media_dir('widget_revslider/sliders')."/";
	}

	function displayList(){
		$this->setTitle('Revolution sliders');
        $this->setMenu('revolution_sliders');
        
		
		if( _var('created') ){
			$this->displayMessage(_translate('messages.created','widget_revslider'));
		}
		if( _var('deleted') ){
			$this->displayMessage(_translate('messages.deleted','widget_revslider'));
		}
        
        $this->checkFunctions();
		$fields = [

			'id' => [
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',

			],
			'title' => [

				'name' => 'Nome',
				'field_value' => 'title',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'title',
				'search_name' => 'title',
				'search_value' => '',
				'search_type' => 'input',

			],

		];


		$dataSource = (new DataSource('revolution_slider'))
		->addFields(['revolution_slider.*']);
	


		ListHelper::create('revolution_slider',$this)
			->setFieldsFromArray($fields)
			->enableExport(true)
			//->setPerPage($limit)
			->setExportTypes(['pdf','csv','excel'])
			->enableSearch(true)
			->setFieldsFromArray($fields)
			->enableBulkActions(false)
			->addDeleteActionRowButton(function($row){
				return _translate(['messages.confirm_delete_message',$row->title],'widget_revslider');
			})
			->onDelete(function($id){
				$this->delete($id);
			})
			->setDataSource($dataSource)
			->display();
	}


	private function checkFunctions(){
		if( !class_exists('ZipArchive') ){
			$this->errors[] = "<b>ZipArchive</b> non è installato. Occorre installare questa estensione di php per poter caricare nuovi slider.";

		}
	}

	/**
	 * Display Form
	 *
	 * @return void
	 */
	function displayForm(){
		$this->setMenu('revolution_sliders');
		$this->setTitle("Revolution slider");
		$this->checkFunctions();
		
		$fields = [
			
			'file' => [
				'type' => 'file',
                'label' => "Exported revslider zip"
			]
		];
       
        $form = FormHelper::create('revolution_slider',$this)
            ->layoutFile(_MARION_MODULE_DIR_.'widget_revslider/templates/admin/forms/slider.xml')
            ->process(function(FormHelper $form){
				$data = $form->getValidatedData();
				
				$id = DB::table('revolution_slider')->insertGetId(['content'=>'']);
				$this->saveSlider('slider_'.$id,$data['file']);

				$dati = $this->parseSlider('slider_'.$id);
				foreach($dati as $k => $v){
					if( is_array($v) ){
						$update[$k] = serialize($v);
					}else{
						$update[$k] = $v;
					}
					DB::table('revolution_slider')->where('id',$id)->update($update);
				}
				$this->redirectToList(['created'=>1]);

            })->setFields($fields);

        $form->display();
	}
	
	/**
	 * Convert object to array
	 *
	 * @param stdClass $data
	 * @return array
	 */
	private function object_to_array(stdClass $data): array
	{
		if (is_array($data) || is_object($data))
		{
			$result = array();
			foreach ($data as $key => $value)
			{
				$result[$key] = $this->object_to_array($value);
			}
			return $result;
		}
		return $data;
	}

	/**
	 * Remove recursive directory
	 *
	 * @param string $dir
	 * @return void
	 */
	private function rrmdir(string $dir): void { 
	   if (is_dir($dir)) { 
		 $objects = scandir($dir); 
		 foreach ($objects as $object) { 
		   if ($object != "." && $object != "..") { 
			 if (is_dir($dir."/".$object))
			   $this->rrmdir($dir."/".$object);
			 else
			   unlink($dir."/".$object); 
		   } 
		 }
		 rmdir($dir); 
	   } 
	 }


	 /**
	  * Get info from slider
	  *
	  * @param string $path
	  * @return array
	  */
	 private function parseSlider(string $path): array{		
		$file = $this->path.$path.'/slider.html';
		if( !file_exists($file) ) return false;
		$doc = new \DOMDocument();
		$doc->loadHTMLFile($file);
	
		$title = $doc->getElementsByTagName('title')[0]->textContent;	
		$head = $doc->getElementsByTagName('head')[0];
		$scripts = $head->getElementsByTagName('script');
		
		for ($i = 0; $i < $scripts->length; $i++)
		{
			$script = $scripts->item($i);
			if( $script->textContent){
				$js[$i]['content'] = $script->textContent;
			}else{
				$js[$i]['url'] = $script->getAttribute('src');
			}
		}

		$scripts = $head->getElementsByTagName('link');
		for ($i = 0; $i < $scripts->length; $i++)
		{
			$script = $scripts->item($i);
			$css[]['url'] = $script->getAttribute('href');
		}
		
		$scripts = $head->getElementsByTagName('style');
		
		for ($i = 0; $i < $scripts->length; $i++)
		{
			$script = $scripts->item($i);
			if( trim($script->textContent) ){
				$css[]['content'] = $script->textContent;
			}
		}

		$body = $doc->getElementsByTagName('body')[0];
		$content = $doc->saveHTML($body);
		$head_path_css =  $this->path.'/'.$path."/head_css";
		$head_path_js =  $this->path.'/'.$path."/head_js";
		mkdir($head_path_js,0777,true);
		mkdir($head_path_css,0777,true);
		foreach($js as $ind =>$v){
			if( isset($v['content']) ){
				$script_file_path =  $this->path.'/'.$path."/head_js/script".$ind.".js";
				$script_file = "head_js/script".$ind.".js";
				$myfile = fopen($script_file_path, "w");
				fwrite($myfile, $v['content']);
				fclose($myfile);
				unset($js[$ind]['content']);
				$js[$ind]['url'] = $script_file;
			}
			
			
		}

		foreach($css as $ind =>$v){
			if( isset($v['content']) ){
				$script_file_path =  $this->path.'/'.$path."/head_css/style".$ind.".css";
				$script_file = "head_css/style".$ind.".css";
				$myfile = fopen($script_file_path, "w");
				fwrite($myfile, $v['content']);
				fclose($myfile);
				unset($css[$ind]['content']);
				$css[$ind]['url'] = $script_file;
			}
		}
		$content = preg_replace('/assets/',_MARION_BASE_URL_.'media/contents/widget_revslider/sliders/'.$path."/assets",$content);
		$dati = array(
			'title' => $title,
			'js' => $js,
			'css' => $css,
			'content' => $content
		);
		return $dati;
	}


	/**
	 * Save slider
	 *
	 * @param [type] $dest_base
	 * @param array $data
	 * @return void
	 */
	private function saveSlider($dest_base, array $data = []): void{
		if( okArray($data) ){
			
			$this->rrmdir($this->path.$dest_base);
			mkdir($this->path.$dest_base,0777,true);
			
			$zip = new ZipArchive;
			
			if ($zip->open($data['tmp_name']) === TRUE) {
				$zip->extractTo($this->path.$dest_base);
				$zip->close();
				
			}
		}
	}

	function delete(int $id): void{
	    $this->rrmdir($this->path.'slider_'.$id);
		DB::table('revolution_slider')->delete($id);
		$this->redirectToList(['deleted'=>1]);
		

		
	}
}
?>