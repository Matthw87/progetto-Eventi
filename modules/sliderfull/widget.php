<?php

use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
use SliderFull\Slide;
use Marion\Core\Marion;
use Marion\Support\Image\ImageComposed;
use Marion\Support\Image\Image;
use SliderFull\Slider;

class WidgetSliderfull extends PageComposerComponent{

	function registerJS($data=null){
		PageComposer::registerJS("modules/sliderfull/assets/js/sliderfull.js");
	}

	function registerCSS($data = NULL)
	{
		PageComposer::registerCSS("modules/sliderfull/assets/css/style.css");
	}

	function build($data=null){
		$dati = $this->getParameters();
		$this->setVar('id_box',$this->id_box);
		$date = date('Y-m-d');
		$slides =[];
		$id_slider = isset($dati['id_slider'])?$dati['id_slider']:null;
		if( $id_slider ){
			$slider = Slider::withId($id_slider);
			$this->setVar('slider',$slider);
			$slides = Slide::prepareQuery()
			->whereExpression("(date_start is NULL OR date_start <= '{$date}')")
			->where('slider_id',$id_slider)
			->whereExpression("(date_end is NULL OR date_end >= '{$date}')")
			->orderBy('order_view')
			->get();
			
		}
		$user = Marion::getUser();
		foreach($slides as $k => $slide){
			if(isMultilocale()){
				if( okArray($slide->allowed_langs) ){
					if( !in_array(_MARION_LANG_,$slide->allowed_langs) ){
						unset($slides[$k]);
						continue;
					}
				}
			}
			if( okArray($slide->allowed_user_categories) ){
				if( $user ){
					if( !in_array($user->user_category_id,$slide->allowed_user_categories) ){
						unset($slides[$k]);
						continue;
					}
				}else{
					unset($slides[$k]);
					continue;
				}
			}
		}
		$this->setVar('slides',array_values($slides));
		if( count($slides) == 1 ){
			$this->setVar('only_one',1);
		}
		if( $this->isMobile()){
			$this->output('@sliderfull/widgets/slider_mobile.htm');
		}else{
			$this->output('@sliderfull/widgets/slider.htm');
		}
	}




	function export($directory){
	
		$dati = $this->getParameters();

		$id_slider = $dati['id_slider'];
		//$slides = SlideFull::prepareQuery()->where('id_slider',$id_slider)->get();
		$database = Marion::getDB();
		$select = $database->select('*','sliderfull_sliders',"id={$id_slider}");

		$select_slides = $database->select('*','sliderfull_slides',"slider_id={$id_slider}");
		$json = array(
			'slider' => $select[0],
			'slides' => $select_slides
		);
		foreach($select_slides as $v){
			$image = ImageComposed::withId($v['image']);
			if( is_object($image) ){
				$im = Image::withId($image->_original);

				$associazione[$image->getId()] = $im->file_src_pathname;
				$associazione_new[$image->getId()] = preg_replace('#^../upload/images/#','',$im->file_src_pathname);
			}

			$image = ImageComposed::withId($v['image_mobile']);
			if( is_object($image) ){
				$im = Image::withId($image->_original);

				$associazione[$image->getId()] = $im->file_src_pathname;
				$associazione_new[$image->getId()] = preg_replace('#^../upload/images/#','',$im->file_src_pathname);
				
			}
			
		}

		$zip = new \ZipArchive;
		if ($zip->open($directory.'/images.zip', \ZipArchive::CREATE) === TRUE)
		{
			
			foreach($associazione as $path){
				$new_path = preg_replace('#^../upload/images/#','',$path);
				
				if(file_exists($path)){
					//debugga($path);exit;
					$zip->addFile($path,$new_path);
				}
				
			}
			// All files are added, so close the zip file.
			$zip->close();
		}

		
		$json['associazione'] = $associazione_new;
		file_put_contents($directory."/dati.json",json_encode($json));
		

	}


	

	function import($directory){

		$parameters = $this->getParameters();
		
		$dati = json_decode(file_get_contents($directory."/dati.json"),true);
		$zip = new \ZipArchive;
		$path_zip = $directory."/images.zip";
		if ($zip->open($path_zip) === TRUE) {
			$zip->extractTo($directory."/".'images');
			$zip->close();
		}
		$new = array();
		foreach($dati['associazione'] as $id_old => $path){
			$path = $directory."/".'images/'.$path;
			$image = ImageComposed::withFile($path);
			$image->save();
			
			$new[$id_old] = $image->getId();

			
		}

		//debugga($dati);
		$database = Marion::getDB();
		$slider = $dati['slider'];
		unset($slider['id']);
		$id_slider = $database->insert('sliderfull',$slider);
		foreach($dati['slides'] as $slide){
			unset($slide['id']);
			$slide['id_slider'] = $id_slider;
			$slide['image'] = $new[$slide['image']];
			$slide['image_mobile'] = $new[$slide['image_mobile']];
			$database->insert('slidefull',$slide);
		}
		$parameters['id_slider'] = $id_slider;
		$this->setParameters($parameters);

		return true;
	}
}

	



?>