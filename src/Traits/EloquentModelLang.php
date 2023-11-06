<?php
namespace Marion\Traits;
use Illuminate\Database\Capsule\Manager as DB;
trait EloquentModelLang {
    private $cached_data_lang = [];
    
    public function lang($lang=null){
       if( !$lang ) $lang = _MARION_LANG_;
       if( array_key_exists($lang,$this->cached_data_lang)){
            return $this->cached_data_lang[$lang];
        } 
        $data = DB::table($this->getLangTableName())
            ->where($this->getExternalKeyLangTableName(),$this->id)
            ->where($this->getLangField(),$lang)
            ->first();
        if( $data ){
            $this->cached_data_lang[$lang] = $data;
        }

        return $data;
    }

    public function getDataLang(){
        return DB::table($this->getLangTableName())
            ->where($this->getExternalKeyLangTableName(),$this->id)
            ->get();
    }


    private function getLangTableName(){
        if( property_exists($this,'table_lang')) {
            return $this->table_lang;
        }else{
           return $this->table."_lang";
        }

    }

    private function getExternalKeyLangTableName(){
        if( property_exists($this,'table_lang_external_key')) {
            return $this->table_lang_external_key;
        }else{
           return $this->table."_id";
        }
    }

    private function getLangField(){
        if( property_exists($this,'table_lang_field')) {
            return $this->table_lang_field;
        }else{
           return "lang";
        }
    }

}
?>