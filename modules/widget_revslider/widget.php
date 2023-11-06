<?php
use Marion\Components\PageComposerComponent;
use Marion\Core\Marion;
use Marion\Entities\Cms\PageComposer;
use Marion\Utilities\PageComposerTools;
class WidgetRevSliderComponent extends  PageComposerComponent{
	
	function getDataSlider($id){
		if( !(int)$id ) return false;
		if( isset($GLOBALS['widget_revslider']) && okArray($GLOBALS['widget_revslider']) && array_key_exists($id,$GLOBALS['widget_revslider'])) return $GLOBALS['widget_revslider'][$id];
		$database = Marion::getDB();
		
		$res = $database->select('*','revolution_slider',"id={$id}");
		if( okArray($res) ){
			$res = $res[0];
			$GLOBALS['widget_revslider'][$id]['js'] = unserialize($res['js']);
			$GLOBALS['widget_revslider'][$id]['css'] = unserialize($res['css']);
			$GLOBALS['widget_revslider'][$id]['content'] = $res['content'];
		}else{
			$GLOBALS['widget_revslider'][$id] = null;
		}
	}

	function registerJS($data=null){
		if( isset($data['parameters']) ){
			$parameters = unserialize($data['parameters']);
			$id  = isset($parameters['slider_id'][$GLOBALS['activelocale']])?$parameters['slider_id'][$GLOBALS['activelocale']]:null;
			if( (int)$id ){
				$path_media = _MARION_BASE_URL_.'media/contents/widget_revslider/sliders/slider_'.$id;
				$this->getDataSlider($id);
				
				
				if( isset($GLOBALS['widget_revslider'][$id]['js']) && okArray($GLOBALS['widget_revslider'][$id]['js']) ){
					foreach( $GLOBALS['widget_revslider'][$id]['js'] as $v ){
						if( preg_match('/\/jquery\//',$v['url']) ) continue;
						
						PageComposer::registerJS($path_media."/".$v['url'],'head');
					}
				}
			}
		}
	}
	function registerCSS($data=null){
		if( isset($data['parameters']) ){
			$parameters = unserialize($data['parameters']);
			
			$id  = isset($parameters['slider_id'][$GLOBALS['activelocale']])?$parameters['slider_id'][$GLOBALS['activelocale']]:null;
			if( (int)$id ){
				$this->getDataSlider($id);
				if( isset($GLOBALS['widget_revslider'][$id]['css']) && okArray($GLOBALS['widget_revslider'][$id]['css']) ){
					foreach( $GLOBALS['widget_revslider'][$id]['css'] as $v ){
						if( preg_match('/http/',$v['url']) ){
							PageComposer::registerCSS($v['url']);
						}else{
							$path_media = _MARION_BASE_URL_.'media/contents/widget_revslider/sliders/slider_'.$id;
							PageComposer::registerCSS($path_media."/".$v['url']);
						}
					}
				}
			}
		}
	}

	function build($data=null){
			
			$parameters = $this->getParameters();

			if( isset($parameters['slider_id'][$GLOBALS['activelocale']]) ){
				$id  = $parameters['slider_id'][$GLOBALS['activelocale']];
				$this->getDataSlider($id);
				echo $GLOBALS['widget_revslider'][$id]['content'];
			}
	}





	function export($directory){
		$parameters = $this->getParameters();
		$database = Marion::getDB();
		$dati = array();
		
		$sliders = array();


		if(okArray($parameters)){
			foreach($parameters['id_slider'] as $lang => $id){
				$res = $database->select('*','revolution_slider',"id={$id}");
				
				if( okArray($res) ){
					$dati[] = $res[0];
					$path = media_dir('widget_revslider/sliders/slider_'.$res[0]['id']);
					//$path = _MARION_MODULE_DIR_."widget_revslider/sliders/slider_".$res[0]['id'];
					if(file_exists($path)){
						$sliders[] = array(
							'relative' => "slider_".$res[0]['id'],
							'absolute' => $path,

						);
					}
				}
			}
		}
		
		foreach($sliders as $slider){
			$dest = $directory."/".$slider['relative'].".zip";
			
			PageComposerTools::Zip($slider['absolute'],$dest);
		}

		file_put_contents($directory."/dati.json",json_encode($dati));
		

	}


	function import($directory){
		$database = Marion::getDB();

		
	
		$parameters = $this->getParameters();
		//debugga($parameters);
		$dati = json_decode(file_get_contents($directory."/dati.json"),true);
		
		$associa = array();
		
		foreach($dati as $v){
			$id_old = $v['id'];
			unset($v['id']);
			$id = $database->insert('revolution_slider',$v);
			$content = $v['content'];
			
			$content = preg_replace("/slider_{$id_old}/","slider_{$id}",$content);
			$database->update('revolution_slider',"id={$id}",array('content' => $content));
			$associa[$id_old] = $id;
		
		}
		

		foreach($parameters['id_slider'] as $lang => $v){
			$parameters['id_slider'][$lang] = $associa[$v];
		}

		
		$list = scandir($directory);
		
		foreach($list as $file){
			if( preg_match('/zip/',$file) ){
				$path_zip = $directory."/".$file;
				$name = explode('.',$file);
				$zip = new \ZipArchive;
				if ($zip->open($path_zip) === TRUE) {
					$zip->extractTo($directory."/".$name[0]);
					$zip->close();
				
				}
			}
		}
		
		
		foreach($associa as $id_old => $id){
			$path_old = $directory."/slider_".$id_old;
			$path_new = media_dir('widget_revslider/sliders/slider_'.$id);
			if( file_exists($path_old) ){
				$this->rcopy($path_old,$path_new);
			}	
		}
		$this->setParameters($parameters);
		
		
		return true;

	}

	function rcopy($src, $dest){

		// If source is not a directory stop processing
		if(!is_dir($src)) return false;
	
		// If the destination directory does not exist create it
		if(!is_dir($dest)) { 
			if(!mkdir($dest)) {
				// If the destination directory could not be created stop processing
				return false;
			}    
		}
	
		// Open the source directory to read in files
		$i = new \DirectoryIterator($src);
		foreach($i as $f) {
			if($f->isFile()) {
				copy($f->getRealPath(), "$dest/" . $f->getFilename());
			} else if(!$f->isDot() && $f->isDir()) {
				$this->rcopy($f->getRealPath(), "$dest/$f");
			}
		}
	}


}






?>