<?php
namespace Marion\Support\Image;
use Illuminate\Database\Capsule\Manager as DB;

use \Intervention\Image\ImageManagerStatic;

define('IMAGE_TABLE', "image");
define('UPLOAD_IMAGES_DIR', _MARION_MEDIA_DIR_."upload/images/");
class Image{
    
    public int $id;
    public string $uuid;

    public float $height;
    public float $width;
    public float $size;
    public string $mime;
    public string $filename;
    public string $ext;

    private $_data;

    private array $mime_types = array(
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'flif' => 'image/flif',
        'flv' => 'video/x-flv',
        'js' => 'application/x-javascript',
        'json' => 'application/json',
        'tiff' => 'image/tiff',
        'css' => 'text/css',
        'xml' => 'application/xml',
        'doc' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'xlt' => 'application/vnd.ms-excel',
        'xlm' => 'application/vnd.ms-excel',
        'xld' => 'application/vnd.ms-excel',
        'xla' => 'application/vnd.ms-excel',
        'xlc' => 'application/vnd.ms-excel',
        'xlw' => 'application/vnd.ms-excel',
        'xll' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pps' => 'application/vnd.ms-powerpoint',
        'rtf' => 'application/rtf',
        'pdf' => 'application/pdf',
        'html' => 'text/html',
        'htm' => 'text/html',
        'php' => 'text/html',
        'txt' => 'text/plain',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'mp3' => 'audio/mpeg3',
        'wav' => 'audio/wav',
        'aiff' => 'audio/aiff',
        'aif' => 'audio/aiff',
        'avi' => 'video/msvideo',
        'wmv' => 'video/x-ms-wmv',
        'mov' => 'video/quicktime',
        'zip' => 'application/zip',
        'tar' => 'application/x-tar',
        'swf' => 'application/x-shockwave-flash',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ott' => 'application/vnd.oasis.opendocument.text-template',
        'oth' => 'application/vnd.oasis.opendocument.text-web',
        'odm' => 'application/vnd.oasis.opendocument.text-master',
        'odg' => 'application/vnd.oasis.opendocument.graphics',
        'otg' => 'application/vnd.oasis.opendocument.graphics-template',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'otp' => 'application/vnd.oasis.opendocument.presentation-template',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'odc' => 'application/vnd.oasis.opendocument.chart',
        'odf' => 'application/vnd.oasis.opendocument.formula',
        'odb' => 'application/vnd.oasis.opendocument.database',
        'odi' => 'application/vnd.oasis.opendocument.image',
        'oxt' => 'application/vnd.openofficeorg.extension',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
        'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
        'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
        'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
        'thmx' => 'application/vnd.ms-officetheme',
        'onetoc' => 'application/onenote',
        'onetoc2' => 'application/onenote',
        'onetmp' => 'application/onenote',
        'onepkg' => 'application/onenote',
        'csv' => 'text/csv',
    );

   

    private string $destination_path_file;
    private string $destination_path_webp_file;
    
    private \Intervention\Image\Image $image;

    public static function initFromFile($file): self{
        $obj = new Image();
        $obj->image = ImageManagerStatic::make($file);
        $obj->filename = $obj->image->filename;
        $obj->ext = $obj->image->extension;
        return $obj;
    }

    public static function initFromString($data,$filename): self{
		$data = base64_decode($data);
		//prendo il mime type
		$f = finfo_open();
		$mime_type = finfo_buffer($f, $data, FILEINFO_MIME_TYPE);
		$im = imagecreatefromstring($data);
		$ext = self::getExtensionFromMime($mime_type);
		if($ext){
			$path = _MARION_TMP_DIR_."/".$filename.".".$ext;
			switch( $ext ){
				case 'png':	
					imagepng($im,$path);
					break;
				case 'gif':	
					imagegif($im, $path);
					break;
				case 'jpe':
				case 'jpeg':	
					imagejpeg($im, $path);
					break;
			}
			return self::initFromFile($path);
		}else{
			return null;	
		}
		
	}

    public static function initFromForm($data): self{
        $obj = new Image();
        $obj->image = ImageManagerStatic::make($data['tmp_name']);
        $obj->filename = $data['name'];
        $obj->ext = array_search($data['type'],$obj->mime_types);
        return $obj;
    }


    function save(float $width = null,float $height = null): self{
        if( isset($this->uuid) ){
            $uuid = $this->uuid;
        }else{
            $uuid = uniqid();
        }
        
        $file_destination = $uuid.".".$this->ext;
        $file_destination_webp = $uuid.".webp";
        $this->destination_path_file = UPLOAD_IMAGES_DIR.$file_destination;
        $this->destination_path_webp_file = UPLOAD_IMAGES_DIR.$file_destination_webp;
        $relative_path = str_replace(_MARION_MEDIA_DIR_,'',$this->destination_path_file);

        if( $width || $height){
            $this->image->resize($width,$height,function ($constraint) {
                $constraint->aspectRatio();
            });
        }
       
        $this->image->save($this->destination_path_file);
        
        
        $data = [
            'path' => $relative_path,
            'ext' => $this->ext,
            'height' => $this->image->height(),
            'width' => $this->image->width(),
            'mime' => $this->image->mime(),
            'filename' => $uuid,
            'filename_original' => $this->filename
        ];

        $converter = new ImageConverter();
        if($converter->convert($this->destination_path_file , $this->destination_path_webp_file, _MARION_QUALITY_WEBP_CONVERT_) ){
            $relative_path_webp = str_replace(_MARION_MEDIA_DIR_,'',$this->destination_path_webp_file);
            $data['path_webp'] = $relative_path_webp;
        }else{
            $data['path_webp'] = $relative_path;
        }
        
        if( isset($this->id) ){
            DB::table(IMAGE_TABLE)->where('id',$this->id)->update($data);
        }else{
            $id = DB::table(IMAGE_TABLE)->insertGetId($data);
            $this->id = $id;
        }
        return self::byId($this->id);
        
    }


    public static function byId(int $id): self{
        $data = DB::table(IMAGE_TABLE)->where('id',$id)->first();
        $file = _MARION_MEDIA_DIR_.$data->path;
        $obj = new Image();
        $obj->_data = $data;
        $obj->id = $data->id;
        $obj->uuid = $data->filename;
        $obj->image = ImageManagerStatic::make($file);
        $obj->filename = $data->filename_original;
        $obj->ext = $obj->image->extension;
        return $obj;
    }


    public function delete(){
        if( $this->id ){
            DB::table(IMAGE_TABLE)->where('id',$this->id)->delete();
            $file = _MARION_MEDIA_DIR_.$this->_data->path;
            $file_webp = _MARION_MEDIA_DIR_.$this->_data->path_webp;
            if( file_exists($file) ){
                unlink($file);
            }
            if( file_exists($file_webp) ){
                unlink($file_webp);
            }
        }
    }



    private static function getExtensionFromMime($mime){
		$mimes = array(
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'flv' => 'video/x-flv',
            'js' => 'application/x-javascript',
            'json' => 'application/json',
            'tiff' => 'image/tiff',
            'css' => 'text/css',
            'xml' => 'application/xml',
            'doc' => 'application/msword',
            'docx' => 'application/msword',
            'xls' => 'application/vnd.ms-excel',
            'xlt' => 'application/vnd.ms-excel',
            'xlm' => 'application/vnd.ms-excel',
            'xld' => 'application/vnd.ms-excel',
            'xla' => 'application/vnd.ms-excel',
            'xlc' => 'application/vnd.ms-excel',
            'xlw' => 'application/vnd.ms-excel',
            'xll' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pps' => 'application/vnd.ms-powerpoint',
            'rtf' => 'application/rtf',
            'pdf' => 'application/pdf',
            'html' => 'text/html',
            'htm' => 'text/html',
            'php' => 'text/html',
            'txt' => 'text/plain',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mp3' => 'audio/mpeg3',
            'wav' => 'audio/wav',
            'aiff' => 'audio/aiff',
            'aif' => 'audio/aiff',
            'avi' => 'video/msvideo',
            'wmv' => 'video/x-ms-wmv',
            'mov' => 'video/quicktime',
            'zip' => 'application/zip',
            'tar' => 'application/x-tar',
            'swf' => 'application/x-shockwave-flash',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ott' => 'application/vnd.oasis.opendocument.text-template',
            'oth' => 'application/vnd.oasis.opendocument.text-web',
            'odm' => 'application/vnd.oasis.opendocument.text-master',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'otg' => 'application/vnd.oasis.opendocument.graphics-template',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'otp' => 'application/vnd.oasis.opendocument.presentation-template',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odi' => 'application/vnd.oasis.opendocument.image',
            'oxt' => 'application/vnd.openofficeorg.extension',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'thmx' => 'application/vnd.ms-officetheme',
            'onetoc' => 'application/onenote',
            'onetoc2' => 'application/onenote',
            'onetmp' => 'application/onenote',
            'onepkg' => 'application/onenote',
        );
		
		 $ext = array_search($mime,$mimes);
		 if($ext){
			return $ext;	 
		 }
		 return false;
		
		
	}


}