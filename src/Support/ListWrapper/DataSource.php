<?php
namespace Marion\Support\ListWrapper;
use Illuminate\Database\Capsule\Manager as DB;
class DataSource{
    private $root_table;
    private ListCore $list;

    private bool $enable_limit = true;

    private $fields = [];
    public array $data = [];
    public int $count;
    private array $on_sort_functions = [];
    private array $on_search_functions = [];

    private \Illuminate\Database\Query\Builder $query_builder;


    function __construct(string $table)
    {   
      
       $this->root_table = $table;
       $this->query_builder = DB::table($this->root_table);
    }

    function setEnbaleLimit(bool $enable_limit){
        $this->enable_limit = $enable_limit;
    }

    public function queryBuilder():  \Illuminate\Database\Query\Builder{
        return $this->query_builder;
    }

    public function setListCore(ListCore $list){
        $this->list = $list;
    }


    public function addFields(array $fields): self{
        $this->fields = array_merge($this->fields,$fields);
        return $this;
    } 

    public function removeFields(array $fields): self{
        $this->fields = array_filter($this->fields,function($field) use ($fields){
            return !in_array($field,$fields);
        });
        return $this;
    }

    public function onSort(\Closure $function){
        $this->on_sort_functions[] = $function;
    }
    public function onSearch(\Closure $function){
        $this->on_search_functions[] = $function;
    }


    private  function search(){
        $fields = $this->list->getFieldList();

        
        $search = false;
        foreach($fields as $field){
            if(array_key_exists('searchable',$field) && $field['search_name'] ){
                if( isset($_GET[$field['search_name']]) ){
                    $search = true;
                    break;
                }
            }
        }
        
        if( $search ){
            foreach($this->on_search_functions as $function){
                if( $function instanceof \Closure ){	
                    call_user_func($function,$this->queryBuilder());
                }
            }
        }
    }
    private function sort(){
        $fields = $this->list->getFieldList();
        $sort = false;
        $field = '';
        $order = '';
        if( isset($_GET['orderBy']) ){
            foreach($fields as $field){
                if(array_key_exists('sortable',$field) && $field['sort_id'] == $_GET['orderBy']){
                    $field = $_GET['orderBy'];
                    $order = $_GET['orderType'];
                    $sort = true;
                    break;
                }
            }
        }
        
        if( $sort ){
            foreach($this->on_sort_functions as $function){
                if( $function instanceof \Closure ){	
                    call_user_func($function,$this->query_builder,$field,$order);
                }
            }
        }
    }

    public function build(){
        
        if( $this->list ){
            $this->sort();
            $this->search();
        }
        
        
        
        
        $query_count = clone $this->query_builder;
        if( $this->enable_limit ){
            $limit = ListHelper::limit();
		    $offset = ListHelper::offset();
            $this->data = $this->query_builder->limit($limit)
                        ->offset($offset)
                        ->get($this->fields)
                        ->toArray();
        
        }else{
            $this->data = $this->query_builder
                        ->get($this->fields)
                        ->toArray();
        
        }
        $this->count = $query_count->count();
        
    }



}