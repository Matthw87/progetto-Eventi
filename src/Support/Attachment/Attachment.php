<?php
namespace Marion\Support\Attachment;
use Marion\Core\Marion;
define('ATTACHMENT_TABLE', "attachment");
class Attachment{
	public $id;
	public $filename;
	public $path;
	public $type;
	public $size;
	public $date_insert;


	private $_tmp_path;
	private $_directory_upload;
	

	public function __construct(){
		$this->_directory_upload = _MARION_MEDIA_DIR_.'upload/attachments';
	}


	//inizializza un oggetto da database
	public static function withId($id){
		if($id){
			$database = Marion::getDB();
			$data = $database->select('*',ATTACHMENT_TABLE,"id={$id}");
			
			if(okArray($data)){
				$data = $data[0];
				$obj = new Attachment();
				foreach($data as $k => $v){
					$obj->$k = $v;
				}
				return $obj;
			}else{
				
				return false;
			}

		}else{
			return false;
		}
	}


	//inizializza l'oggetto da un file input
	public static function fromForm($file){
			$info = $file;
			
			if( file_exists($info['tmp_name'] ) ){
				$obj = new Attachment();
				$obj->filename = $info['name'];
				$obj->type = $info['type'];
				$obj->size = $info['size'];
				$obj->_tmp_path = $info['tmp_name'];
				
				return $obj;
			
			}

			return false;

	}


	// uoload di un file che sta nella cartella temporanea
	function upload(){
		//determino il nuo path del file da caricare
		$path = self::verifica_duplicati($this->filename,$this->_directory_upload);
		
		//carico il file
		if( move_uploaded_file( $this->_tmp_path,$path)){
			$this->path = $path;
			return true;
		}
		return false;

	}

	//salvo il file nel database
	function save(){
		
		$toinsert['filename'] = $this->filename;
		$toinsert['path'] = $this->path;
		$toinsert['size'] = $this->size;
		$toinsert['type'] = $this->type;
		
		$database = Marion::getDB();
		$this->id = $database->insert(ATTACHMENT_TABLE,$toinsert);
		
		
		return $this;
	}
	

	
	//effettua il download di un oggetto
	function download(){
		if( $this->path ){
			if(file_exists($this->path)){
				header('Content-Description: File Transfer');
				header('Content-Type: '.$this->type);
				header('Content-Disposition: attachment; filename='.$this->filename);
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($this->path));
				ob_clean();
				readfile($this->path);
				exit;

			}
		}


	}

	//visualizza il documento
	function display(){
		if( $this->path ){
			
			if(file_exists($this->path)){

				header('Content-Type: '.$this->type);
				header('Content-Disposition: inline; filename='.$this->filename);
				header('Content-Transfer-Encoding: binary');
				header('Content-Length: ' . filesize($this->path));
				@readfile($this->path);
				exit;

			}
		}
	}



	//elimina un oggetto
	function delete(){
		if( $this->path ){
			if( file_exists($this->path) ){
				unlink($this->path);
			}
		}
		if( $this->id ){
			$database = Marion::getDB();
			$database->delete(ATTACHMENT_TABLE,"id={$this->id}");
		}
	
	}


	
	public static function pathinfo_filename($path){
		 $temp = pathinfo($path);
		if ($temp['extension']) {
			$temp['filename'] = substr($temp['basename'],0 ,strlen($temp['basename'])-strlen($temp['extension'])-1);
		}
		return $temp;

	}

	public static function verifica_duplicati($file, $basedir) {
		$nomefile = $basedir . '/'. $file;
		if (file_exists($nomefile)) {
			$pf = self::pathinfo_filename($nomefile);
			if (empty($pf['extension'])) $pf['extension'] = 'bin';

			if (preg_match('/([[:print:]]+)\s\((\d+)\)$/', $pf['filename'], $matches)) {
				$pf['filename'] = $matches[1] . ' ('. ($matches[2]+1) .')';
			} else {
				$pf['filename'] .= ' (1)';
			}

			$pf['filename'] .= '.'.$pf['extension'];

			return self::verifica_duplicati($pf['filename'], $basedir);
		}
		return $nomefile;
	}

}




?>