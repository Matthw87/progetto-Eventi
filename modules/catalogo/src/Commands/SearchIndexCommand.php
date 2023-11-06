<?php

namespace Catalogo\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Carbon;
use Marion\Core\Marion;

class SearchIndexCommand extends Command 
{
    protected function configure()
    {
        $this->setName('catalogo:build-search')
        ->setDescription('Build search index');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

       
        //debugga($now);exit;
        $check_date = Marion::getConfig('catalogo_setting','search_index_last_check');
        
        $now = Carbon::now()->format('Y-m-d H:i');
        Marion::setConfig('catalogo_setting','search_index_last_check',$now);
        

        if( !$check_date ){
            $changed_products = DB::table('products')->pluck('id')->toArray();
        }else{
            $changed_products = DB::table('products')
                ->where('updated_at','>=',$check_date)
                ->pluck('id')->toArray();
        }
        if( !okArray($changed_products) ) return;
        DB::table('product_search_index')->whereIn('product_id',$changed_products)->delete();

        $toinsert = [];
        $data_tags = [];
        $data_categories = [];
        $data_manufactures = [];

        $categories = DB::table('product_categories','c')
            ->join('product_category_langs as l','l.product_category_id','=','c.id')
            ->select(['id','name','lang'])
            ->get()->toArray();
        
       
        if( okArray($categories) ){
            foreach($categories as $v){
                $data_categories[$v->id][$v->lang] = $v->name;
            }
        }
       
        $tags = DB::table('product_tags','t')
            ->join('product_tag_langs as l','l.product_tag_id','=','t.id')
            ->select(['id','name','lang'])
            ->get()->toArray();
        if( okArray($tags) ){
            foreach($tags as $v){
                $data_tags[$v->id][$v->lang] = $v->name;
            }
        }

        $manufactures = DB::table('product_manufacturers','m')
            ->join('product_manufacturer_langs as l','l.product_manufacturer_id','=','m.id')
            ->select(['id','name','lang'])
            ->get()->toArray();
        if( okArray($manufactures) ){
            foreach($manufactures as $v){
                $data_manufactures[$v->id][$v->lang] = $v->name;
            }
        }
        //debugga($data_manufactures);exit;
       

      
        $list = DB::table('products','p')
            ->join('product_langs as l','l.product_id','=','p.id')
            ->select([
                'id',
                'name',
                'lang',
                'sku',
                'parent_id',
                'product_category_id',
                'product_manufacturer_id'
            ])
            ->whereIn('id',$changed_products)
            ->get()->toArray();
            
        foreach($list as $v){
                if( $v->parent_id ){
                    $id_product = $v->parent_id;
                }else{
                    $id_product = $v->id;
                    
    
                }
                $key_name = "product_name_".$v->id;
                $key_sku = "product_sku_".$v->id;
                if( $v->product_category_id && isset($data_categories[$v->product_category_id]) ){
                    $categories = $data_categories[$v->product_category_id];
                    if( okArray($categories) ){
        
                        foreach($categories as $lang => $v1){
                            $toinsert[] = array(
                                'product_id' => $id_product,
                                'product_key' => 'section',
                                'uid' => 'category_'.$v->product_category_id."_".$id_product."_".$lang,
                                'product_value' => $v1,
                                'lang' => $lang,
                            );
                        }
                        
                    }
                }
                if( $v->product_manufacturer_id && isset($data_manufactures[$v->product_manufacturer_id]) ){
                    $manufactures = $data_manufactures[$v->product_manufacturer_id];
                    if( okArray($manufactures) ){
        
                        foreach($manufactures as $lang => $v1){
                            $toinsert[] = array(
                                'product_id' => $id_product,
                                'product_key' => 'manufacturer',
                                'uid' => 'manufacturer_'.$v->product_manufacturer_id."_".$id_product."_".$lang,
                                'product_value' => $v1,
                                'lang' => $lang,
                            );
                        }
                        
                    }
                }
                
                
    
                $toinsert[] = array(
                    'product_id' => $id_product,
                    'product_key' => 'name',
                    'product_value' => $v->name,
                    'uid' => $key_name."_".$v->lang,
                    'lang' => $v->lang,
                );
                $toinsert[] = array(
                    'product_id' => $id_product,
                    'product_key' => 'sku',
                    'uid' => $key_sku."_".$v->lang,
                    'product_value' => $v->sku,
                    'lang' => $v->lang,
                );
        }

        $tag_composition = DB::table('product_tag_associations')->where('product_id',$changed_products)->get()->toArray();

        if( okArray($tag_composition) ){
            foreach($tag_composition as $v){
                if( $v->product_tag_id && isset($data_tags[$v->product_tag_id]) ){
                    $info_tag = $data_tags[$v->product_tag_id];
                    foreach($info_tag as $lang => $name){
                        $toinsert[] = array(
                            'id_product' => $v->product_id,
                            'product_key' => 'tag',
                            'uid' => 'tag_'.$v->product_tag_id."_".$id_product."_".$lang,
                            'product_value' => $name,
                            'lang' => $lang,
                        );
                    }
                }
            }
        }
        

        $_toinsert = [];
        foreach($toinsert as $v){
            $_toinsert[] = $v;
            if( count($_toinsert) > 100 ){
                DB::table('product_search_index')->insert($_toinsert);
                $_toinsert = [];
            }
           
        }
        if( count($_toinsert) > 0 ){
            DB::table('product_search_index')->insert($_toinsert);
        }        
        return;

    }
    
}