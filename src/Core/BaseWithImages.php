<?php
namespace Marion\Core;
use Marion\Support\Image\ImageComposed;
class BaseWithImages extends Base{
	const IMAGES_FIELD_TABLE = 'images';
	const BASE_PATH = _MARION_BASE_URL_;


	public $_imageToRemove;
	public $_old_images;

	//verifica se l'oggetto ha immagini
	public function hasImages(){
		$field_images = STATIC::IMAGES_FIELD_TABLE;
		
		if( property_exists( $this, 'images') ){
			return okArray($this->$field_images);
		}else{
			return false;
		}
		
	}
	
	
	function addImageFromUrl($url,$filename,$options_resize=NULL){
		$field_images = STATIC::IMAGES_FIELD_TABLE;
		$image = ImageComposed::fromUrl($url,$filename,$options_resize);
		if(is_object($image)){
			$res = $image->save();
			if(is_object($res)){
				$id_image = $res->getId();
				array_push($this->$field_images,$id_image);
			}	
		}
		
		return $this;
	}


	//aggiunge un immagine all'oggetto
	public function addImage($file,$options_resize=NULL){
		$field_images = STATIC::IMAGES_FIELD_TABLE;
		if( property_exists( $this, $field_images) ){
			if(file_exists($file)){
				$image = ImageComposed::withFile($file['tmp_name'],$options_resize);
				if(is_object($image)){
					$res = $image->save();
					if(is_object($res)){
						$id_image = $res->getId();
						array_push($this->$field_images,$id_image);
					}	

				}
				
			}
		}
		return $this;

	}
	
	//restituisce un array contenete i path delle immagini
	public function getUrlImagesArray($type='original',$watermark=true){
		$field_images = STATIC::IMAGES_FIELD_TABLE;
		if(okArray($this->$field_images) ){
	
			foreach( $this->$field_images as $k => $v){
				$toreturn[] = $this->getUrlImage($k,$type,$watermark);
			}
			return $toreturn;
		}
		return false;
	}


	//restituisce l'immagine all'indice specificato del formato specificato
	function getUrlImage($index=0,$type='original',$watermark=true,$name_image=NULL){
		$field_images = STATIC::IMAGES_FIELD_TABLE;
		$idImages = $this->$field_images;
		if(okArray($idImages) ){
			//prendo il nome dell'immagine
			$database = Marion::getDB();
			
			$img = $database->select('i.*',"image as i join imageComposed as c on c.{$type}=i.id","c.id={$idImages[$index]}");
			if(okArray($img)){
				if( $name_image ){
					$name = $name_image;
				}else{
					$name = $img[0]['filename_original'];
					$explode = explode('.',$name);
					//debugga($explode);exit;
					$name = Marion::slugify($explode[0]);
					$name = $name.".".$img[0]['ext'];
					
				}
				
				$type_short = $this->getTypeImageUrl($type);
				
				if( !$watermark ){
					return _MARION_BASE_URL_."img/{$idImages[$index]}/{$type_short}-nw/{$name}";
				}else{
					return _MARION_BASE_URL_."img/{$idImages[$index]}/{$type_short}/{$name}";
				}
				
			}
		}
		return false;
		
	}

	//restituisce l'immagine all'indice specificato del formato specificato
	function getPathImage($index=0,$type='original'){
		$field_images = STATIC::IMAGES_FIELD_TABLE;
		$idImages = $this->$field_images;
		if(okArray($idImages) ){
			//prendo il nome dell'immagine
			$database =  Marion::getDB();
		
			$img = $database->select('i.*',"image as i join imageComposed as c on c.{$type}=i.id","c.id={$idImages[$index]}");
			
			if(okArray($img)){
				return  $img[0]['path'];
				
			}
		}
		return false;
		
	}

	//restituisce l'immagine all'indice specificato del formato specificato
	function getAbsolutePathImage($index=0,$type='original'){

		
		$field_images = STATIC::IMAGES_FIELD_TABLE;
		$idImages = $this->$field_images;
		if(okArray($idImages) ){
			//prendo il nome dell'immagine
			$database =  Marion::getDB();
		
			$img = $database->select('i.*',"image as i join imageComposed as c on c.{$type}=i.id","c.id={$idImages[$index]}");
			
			if(okArray($img)){
				return _MARION_ROOT_DIR_.$img[0]['path'];
				
			}
		}
		return false;
		
	}

	
	//rimuove un immagine all'indice specificato
	function removeImageAtIndex($index=0){
		if( (int)$index >= 0){
			$index = (int)$index;
			$field_images = STATIC::IMAGES_FIELD_TABLE;
			$idImages = $this->$field_images;

			$this->_imageToRemove[] = $idImages[$index];
			unset($idImages[$index]);
			$this->$field_images = $idImages;
		}
		return $this;
	}


	function getTypeImageUrl($type){
		switch( $type ){
			case 'thumbnail':
				$type = 'th';
				break;
			case 'small':
				$type = 'sm';
				break;
			case 'medium':
				$type = 'md';
				break;
			case 'large':
				$type = 'lg';
				break;
			case 'original':
				$type = 'or';
				break;
			default:
				$type='or';

		}
		return $type;

	}








	/**************************************************** OVERRIDE METODI DELLA CLASSE Base **********************************************/

	public function init(): void{
		
		parent::init();
		
		if($this->hasImages()){
			$field_images = STATIC::IMAGES_FIELD_TABLE;
			$this->_old_images = $this->$field_images;
		}



	}









	//Funzione eseguita prima di effettuare il salvataggio dell'oggetto
	function beforeSave(): void{
		//debugga($this);exit;
		parent::beforeSave();
		$field_images = STATIC::IMAGES_FIELD_TABLE;
		$idImages = $this->$field_images;
		
		if( okArray($idImages) ){
			$this->$field_images = array_values($idImages);
		}else{
			$this->$field_images = array();
		}
	}
	

	//Funzione eseguita dopo il salvataggio dell'oggetto
	function afterSave(): void{
		parent::afterSave();
		if( okArray($this->_old_images) ){
			$field_images = STATIC::IMAGES_FIELD_TABLE;
			$removed_images = [];
			foreach($this->_old_images as $v ){
				if( !in_array($v,$this->$field_images)){
					$removed_images[] = $v;
				}
			}
			if(okArray($removed_images)){
				foreach($removed_images as $v){
					$image = ImageComposed::withId($v);	
					if($image){
						$image->delete();
					}
				}
			}
		}


		if(okArray($this->_imageToRemove)){
			foreach($this->_imageToRemove as $v){
				$image = ImageComposed::withId($v);	
				if($image){
					$image->delete();
				}
			}
		}
		unset($this->_imageToRemove);
	}

	

	function delete(): void{
		parent::delete();
		$field_images = STATIC::IMAGES_FIELD_TABLE;
		foreach($this->$field_images as $v){
			$image = ImageComposed::withId($v);	
			if($image){
				$image->delete();
			}
		}
	}
}
?>