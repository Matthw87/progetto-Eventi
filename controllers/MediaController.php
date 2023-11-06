<?php 
use Marion\Support\Attachment\Attachment;
use Marion\Support\Image\ImageDisplay;
class MediaController{
    public $_image_cache = true;
    public $_image_cache_dir = 'cache';

    public $_image_watermark = false;
    public $_image_watermark_font = 'assets/fonts/DejaVuSerif.ttf';
    public $_image_watermark_text = '3d0';
    public $_image_watermark_position = 'diagonal';
    public $_image_watermak_positions_list = array(
        'diagonal', 
        'bottom_right', 
        'bottom_left', 
        'top_right', 
        'top_left', 
        'center',
    );



    

    function __construct(){

        $this->_image_cache = _MARION_CACHE_IMAGES_;
        $action = $_GET['action'];
        
        switch($action){
            case 'image':
                $this->displayImage();
                break;
            case 'attachment':
                $this->displayAttachment();
                break;
        }
    }

    function displayAttachment(){
        $id = $_GET['id'];
        $type = $_GET['type'];
        $attach = Attachment::withId($id);
        if( is_object($attach) ){
            switch($type){
                case 'download':
                    $attach->download();
                    break;
                default:
                    $attach->display();	
                    break;
            }
        }
        
    }

  

    function displayImage(){
       
		if($this->_image_cache ){
            $encode = base64_encode($_SERVER['REQUEST_URI']);
			$file = _MARION_ROOT_DIR_.$this->_image_cache_dir."/".$encode;
            if(file_exists($file) ){
			  

			   $image_data = unserialize(file_get_contents($file));
               //debugga($image_data);exit;
               if( $image_data['mime'] == 'image/svg'){
                    $image_data['mime'] = 'image/svg+xml';
               } 
			   if( isset($image_data['byte']) ){
				   header('Content-type: ' . $image_data['mime']);
				   echo $image_data['byte'];
				   ob_end_flush();
				   exit;
			   }else{
				   header('Content-type: ' . $image_data['mime']);
				   readfile(_MARION_MEDIA_DIR_.$image_data['path_webp']);
                   exit;
			   }
            }
        }
        
        $type = $_GET['type'];
        $id = $_GET['id'];
        $type = explode('-',$type);
       

        $no_watermark = isset($type[1])?$type[1]: false;
        $type = $type[0];

        switch( $type ){
            case 'th':
                $type = 'thumbnail';
                break;
            case 'sm':
                $type = 'small';
                break;
            case 'md':
                $type = 'medium';
                break;
            case 'lg':
                $type = 'large';
                break;
            case 'or':
                $type = 'original';
                break;
            
            $id = $_GET['id'];

        }
        
       
        
       
        $image = new ImageDisplay();
        $image->get($id,$type);
		
        if($this->_image_cache ){
			// debugga(_MARION_ROOT_DIR_.$this->_image_cache_dir."/".$encode);exit;
            if($this->_image_watermark ){
				
                $image->setFontWatermark($this->_image_watermark_font);
                $image->setTextWatermark($this->_image_watermark_text);
                $data = $image->getDataWithWatermark($this->_image_watermark_position);
				$image_data = array(
					'byte' => $data,
					'mime' => $image->mime
				);
            }else{
				
                //$data = $image->getData();
                //debugga($image);exit;
                $image_data = [
                    'path_webp' => $image->path_webp,
                    'path' => $image->path,
                    'mime' => $image->mime,
                ];
				//$image_data = (array)$image;
				//debugga($data);exit;
            }
			
			$file = _MARION_ROOT_DIR_.$this->_image_cache_dir."/".$encode;
            //debugga($image_data,'qua');exit;
            file_put_contents($file,serialize($image_data));
            if( $image_data['mime'] == 'image/svg'){
                $image_data['mime'] = 'image/svg+xml';
            }
			
			if( isset($image_data['byte']) ){
			   header('Content-type: ' . $image_data['mime']);
			   echo $image_data['byte'];
			   ob_end_flush();
			   exit;
		   }else{
			   header('Content-type: ' . $image_data['mime']);
			   readfile(_MARION_MEDIA_DIR_.$image_data['path_webp']);
               exit;
		   }
           
        }else{
            if($this->_image_watermark ){
                $image->setFontWatermark($this->_image_watermark_font);
                $image->setTextWatermark($this->_image_watermark_text);
                $image->displayWithWatermark($this->_image_watermark_position);
            }else{
                $image->display();
            }
        }
    }
    

    

}

?>