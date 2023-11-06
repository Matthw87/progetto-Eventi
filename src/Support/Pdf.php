<?php
namespace Marion\Support;

class Pdf{

    public static function getInstance($options=[]): \mikehaertl\wkhtmlto\Pdf {
        $_options = [
            'binary' => _env('WKHTMLTOPDF_PATH')
        ];
        if( okArray($options) ){
            $options = array_merge($options,$_options);
        }else{
            $options = $_options;
        }
        $pdf = new \mikehaertl\wkhtmlto\Pdf($options);
        return $pdf;
    }

    public static function html(string $html): \mikehaertl\wkhtmlto\Pdf {
        $pdf = self::getInstance();
        $pdf->addPage($html);
        return $pdf;
    }
    
}