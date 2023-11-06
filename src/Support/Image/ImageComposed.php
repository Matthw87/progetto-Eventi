<?php
namespace Marion\Support\Image;
use Marion\Core\Marion;
define('IMAGE_COMPOSED_TABLE', "imageComposed");
class ImageComposed{
	private $_id;
	private $_options_resize;
	private $_resize;
	private $_file;
	private $_from_form;
	
	
	public function setId($id){
		$this->_id=$id;
	}
	
	public function getId(){
		return $this->_id;
	}
	
	
	public function __construct($options=array()) {
		if( !okArray($options) ){
			$options = Marion::getConfig('image','options');
		}

		
		$this->_options_resize = $options;
		
		$resize = $options['resize'];
		
		if( okArray($resize) ){
			$this->_resize = $resize;
			
		}
	}
	//prima si chiamava init
	public static function create($options=array()){
		return new ImageComposed($options);
	}
	
	
	public static function withFile($file,$options=array()){ 
		$image = new ImageComposed($options);
		$image->_file = $file;
		return $image;		
	}

	public static function fromUrl($url,$filename,$options=array()){
		if( filter_var($url, FILTER_VALIDATE_URL) && $filename ){
			$path_tmp = sys_get_temp_dir()."/".$filename;
			try {
				copy($url, $path_tmp);
			} catch (\Exception $e) {
				return false;
			}
			return self::withFile($path_tmp,$options);
		}
		return false;
	}

	public static function fromByte($data,$filename,$options=array()){
		
		$path_tmp = sys_get_temp_dir()."/".$filename;
		$res = file_put_contents($path_tmp,$data);
		
		return self::withFile($path_tmp,$options);
		
		return false;
	}
	
	public static function fromForm($file,$options=array()){ 
		$image = new ImageComposed($options);
		$image->_file = $file;

		//debugga($file);exit;
		
		$image->_from_form = true;
		return $image;		
	}
	
	
	public static function withId($id){
			$database =  Marion::getDB();
			$dati = $database->select('*',IMAGE_COMPOSED_TABLE,"id={$id}");
			
			if(okArray($dati)){
				$dati=$dati[0];
				$image = new ImageComposed();
				foreach($dati as $k => $v){
					$key = "_{$k}";
					$image->$key = $v;
				}	
				return $image;
			}
			return false;
	}
	
	public function setFile($pathFile){
		$this->_file = $pathFile;
		return $this;
	}
	
	public function get($type){
		if( $type ){
			$type = strtolower($type);
		}else{
			$type = 'original';	
		}
		$type = "_{$type}";
		return Image::byId($this->$type);	
	}
	
	public function display($type){
		if($type){
			$this->get($type)->display();	
		}else{
			$this->get('original')->display();	
		}
		
	}
	
	public function save(){

		$old_img = [];
		if($this->_id){
			$old_img = array();
			foreach($this as $k => $v){
				if( $k != '_id'){
					$old_img[] = $v;
				}	
				
			}	
		}
		
		if($this->_file){
			if( $this->_from_form ){
				$original = Image::initFromForm($this->_file,$this->_options_resize);
			}else{
				if( file_exists($this->_file) ){
					
				}
				$original = Image::initFromFile($this->_file,$this->_options_resize);	
				
			}
			$original = $original->save();
			$toinsert['original'] = $original->id;
			
			foreach($this->_resize as $v){
				if( $this->_from_form ){
					$image = Image::initFromForm($this->_file);

				}else{
					$image = Image::initFromFile($this->_file);	
				}
				$width = $this->_options_resize[$v."_x"];
				$height = $this->_options_resize[$v."_y"];
				$toinsert[$v] = $image->save($width,$height)->save()->id;	
			}
			
			$database =  Marion::getDB();
			if(!$this->_id){
				$this->_id = $database->insert(IMAGE_COMPOSED_TABLE,$toinsert);
				foreach($toinsert as $key => $value){
					$key = "_{$key}";
					$this->$key = $value;
				}
			}else{
				$database->update(IMAGE_COMPOSED_TABLE, "id={$this->_id}", $toinsert);
			}
			$image_new = self::withId($this->_id);
			foreach( $image_new as $k => $v){
				$this->$k = $v;	
			}
			if(isset($old_img) && okArray($old_img)){
				foreach( $old_img as $v){
					Image::byId($v)->delete();	
				}	
			}
			
		}
		return $this;
		
	}
	
	public function delete(){
		if( $this->_id){
			
			$database =  Marion::getDB();
			
			$obj = $this->get('original');
			
			if( $obj ){
				$obj->delete();
			}
			
			foreach($this->_resize as $v){
				
				$obj = $this->get($v);

				if( is_object($obj) ){
					$obj->delete();
				}
				
			}
			$database->delete(IMAGE_COMPOSED_TABLE,"id={$this->_id}");
				
		}
	}



	public function duplicate(){
		if( $this->_id ){
			
			$options_resize = getConfig('image','options');
			foreach($options_resize['resize'] as $resize){
				$key = "_{$resize}";
				if($this->$key){
					$resize_aviable[] = $resize;
				}
			}
			$options_resize['resize'] = $resize_aviable;
			
			$image = $this->get('original');
			
			
			if( is_object($image) ){
				
				$image_new = self::withFile($image->file_src_pathname);
				
				
				$res = $image_new->save();
				return $res;
			}

			
		}

		return false;

	}
	
}


?>