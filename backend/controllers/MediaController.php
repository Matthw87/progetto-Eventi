<?php
use Marion\Core\Marion;
use Marion\Support\Image\ImageComposed;
use Marion\Controllers\Controller;
use Marion\Support\Attachment\Attachment;
class MediaController extends Controller{
	public $_auth = 'admin';
	

	function ajax(){

		$type = _var('type');
		
		
		
		switch($type){
			case 'img':
				$this->uploadImages();
				break;
			case 'img_single':
				$this->uploadSingleImage();
				break;
			case 'attachment':
				$this->uploadFiles();
				break;
			case 'delete_image':
				$this->deleteImage();
				break;
		}
		
		
		
		
	}


	function deleteImage(){
		$id = _var('id');
		
		$image = ImageComposed::withId($id);
		
		
		/*if($formID){
			unset($_SESSION[$formID][$name][$id]);	
		}*/
		if( is_object($image) ){
			$image->delete();
		}
		$risposta = array('result'=>'ok','id'=>$id); 
		echo json_encode($risposta);
		exit;
	
	}

	function download(){
		$id = _var('id');
		$type = _var('type');
		if( $type == 'image'){
			$database = Marion::getDB();
			$sel = $database->select('*','imageComposed',"id={$id}");
			
			if( okArray($sel) ){
				$sel = $database->select('*','image',"id={$sel[0]['original']}");
				if( okArray($sel) ){
					$type = $sel[0]['mime'];
					$name = $sel[0]['filename_original'];
					$path = $sel[0]['path'];
				}
				
			}
			header('Content-Type: '.$type);
			header("Cache-Control: no-store, no-cache");  
			header('Content-Disposition: attachment; filename="'.$name.'"');
			readfile(_MARION_MEDIA_DIR_.$path);
			exit;
		}

		if( $type == 'attachment'){
			$id = _var('id');
			$attach = Attachment::withId($id);
			
			header('Content-Type: '.$attach->type);
			header("Cache-Control: no-store, no-cache");  
			header('Content-Disposition: attachment; filename="'.$attach->filename.'"');
			readfile(_MARION_MEDIA_DIR_.$attach->path);
			exit;
			
		}
		//debugga($id);exit;
	}


	function display(){
		$action = $this->getAction();
		
		switch($action){
			case 'download':
				
				$this->download();
				break;
		}

	}


	function uploadSingleImage(){
		$options_resize = Marion::getConfig('image','options');
		

		$resize_param = _var('resize');

		
		if( $resize_param && $resize_param != 'null' ){
			$resize = explode(',',$resize_param);
		
			if( okArray($resize)){
				$options_resize['resize'] = $resize;
			}
		}

		
		foreach($_FILES as $k=>$v){
			$image = ImageComposed::fromForm($_FILES[$k],$options_resize);//->save()->getId();
			
			$id = $image->save()->getId();
			if($id){
				/*if($formID){
					
					$_SESSION[$formID][$form][$name][$id] = $id;	
					
				}*/
				$risposta = array('result'=>'ok','id'=>$id);
			}else{
				$risposta = array('result'=>'nak');
			}
			echo json_encode($risposta);
			exit;
		}
	}
	

	function uploadImages(){
		
		$options_resize = Marion::getConfig('image','options');
		//$options_resize['accept_file_types'] = $extensions;

		$resize = explode(',',_var('resize'));
		if( okArray($resize)){
			$options_resize['resize'] = $resize;
		}
		
		$image = ImageComposed::fromForm($_FILES['file'],$options_resize);
		
		$id = $image->save()->getId();
		//debugga($image);exit;
		$risposta = array(
			'result'=>'ok',
			'id'=>$id,
			'url' => _MARION_BASE_URL_.'img/'.$id."/or/image.png",
		);
		
		echo json_encode($risposta);
		exit;
	}



	function uploadFiles(){
		
		$directory_save = _MARION_UPLOAD_DIR_."attachments";
		if( !file_exists($directory_save) ){
			mkdir($directory_save,0755);
		}
		$formID = _var('formID');
		$name = _var('name');
		$database = Marion::getDB();
		$info = $_FILES['file'];
		$split = explode(".",$info['name']);
		$nome_file = $split[0];
		$ext = $split[1];
		
		$info['name'] = $this->makeFriendly($nome_file).".".$ext;
		
		$path_save = $this->verifica_duplicati($info['name'],$directory_save);
		
		$toinsert['path'] = preg_replace('/\.\.\//','',$path_save);
		
		$path_root = preg_replace('/\//','\/',_MARION_ROOT_DIR_);
		$toinsert['path'] = preg_replace("/{$path_root}/",'',$toinsert['path']);
		
		if( move_uploaded_file( $_FILES['file']['tmp_name'],$path_save)){
			$toinsert['filename'] = $info['name'];
			$toinsert['type'] = $info['type'];
			$toinsert['size'] = $info['size'];
			$id = $database->insert('attachment',$toinsert);
			$name = explode('.',$info['name']);
			$ext = $name[count($name)-1];
			$img = '../assets/images/file-icons/512px/'.$ext.".png";
			if( !file_exists($img) ){
				$img = '../assets/images/file-icons/512px/_blank.png';
			}
			if($id){
				/*if($formID){
					$_SESSION[$formID][$form][$name][$id] = $id;	
				}*/
				$risposta = array('result'=>'ok','id'=>$id,'name'=>$info['name'],'img'=>$img);
			}else{
				$risposta = array('result'=>'nak');
			}
			
		}else{
			$risposta = array('result'=>'nak');
		}
		echo json_encode($risposta);
		exit;
	}
		
	
	

	function makeFriendly($string)
	{
		$string = strtolower(trim($string));
		$string = str_replace("'", '', $string);
		$string = preg_replace('#[^a-z\-]+#', '_', $string);
		$string = preg_replace('#_{2,}#', '_', $string);
		$string = preg_replace('#_-_#', '-', $string);
		return preg_replace('#(^_+|_+$)#D', '', $string);
	}

	function verifica_duplicati($file, $basedir) {
		$nomefile = $basedir . '/'. $file;
		if (file_exists($nomefile)) {
			$pf = $this->pathinfo_filename($nomefile);
			if (empty($pf['extension'])) $pf['extension'] = 'bin';

			if (preg_match('/([[:print:]]+)\_\((\d+)\)$/', $pf['filename'], $matches)) {
				$pf['filename'] = $matches[1] . '_('. ($matches[2]+1) .')';
			} else {
				$pf['filename'] .= '_(1)';
			}

			$pf['filename'] .= '.'.$pf['extension'];

			return $this->verifica_duplicati($pf['filename'], $basedir);
		}
		return $nomefile;
	}

	
	function pathinfo_filename($path) {
		$temp = pathinfo($path);
		if ($temp['extension']) {
			$temp['filename'] = substr($temp['basename'],0 ,strlen($temp['basename'])-strlen($temp['extension'])-1);
		}
		return $temp;
	}



	

	

}



?>