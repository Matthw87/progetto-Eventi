<?php
use Marion\Controllers\ListAdminController;
use Marion\Core\Marion;
use Marion\Support\ListWrapper\ListHelper;
use Marion\Support\ListWrapper\DataSource;


class HookController extends ListAdminController{
	public $_auth = 'superadmin';

	/**
	 * display list
	 *
	 * @return void
	 */
	function displayList(){
        $this->setMenu('developer_hooks');
        $this->setTitle("Hooks");
        
        $this->resetToolButtons();

        $fields = [
            [
                'name' => _translate('hooks.name','developer'),
                'field_value' => 'name',
                'sortable' => true,
                'sort_id' => 'name',
                'searchable' => true,
                'search_name' => 'name',
                'search_value' => _var('name'),
                'search_type' => 'input',
            ],
            [
                'name' =>_translate('hooks.description','developer'),
                'field_value' => 'description',
                'sortable' => true,
                'sort_id' => 'description',
                'searchable' => true,
                'search_name' => 'description',
                'search_value' => _var('description'),
                'search_type' => 'input',
            ],
            [
                'name' =>_translate('hooks.type','developer'),
                'field_value' => 'type',
                'sortable' => true,
                'sort_id' => 'type',
                'searchable' => true,
                'search_name' => 'type',
                'search_value' => _var('type'),
                'search_type' => 'type',
            ],
            [
                'name' =>_translate('hooks.module','developer'),
                'field_value' => 'directory',
                'sortable' => true,
                'sort_id' => 'directory',
                'searchable' => true,
                'search_name' => 'directory',
                'search_value' => _var('directory'),
                'search_type' => 'directory',
            ],
            [
                'name' =>_translate('hooks.functions','developer'),
                'function_type' => 'row',
                'function' => function($row){
                    $text = '<table class=\'table table-striped\'><thead><th>'._translate('hooks.function','developer').'</th><th>'._translate('hooks.module','developer').'</th></thead><tbody>';
                    $has_actions = false;
                    if( array_key_exists($row->name,Marion::$actions_module) ){
                        $has_actions = true;
                        $functions = Marion::$actions_module[$row->name];
                        foreach($functions as $function => $data){
                            $text .= "<tr><td>".$function."</td><td>{$data['module']}</td></tr>";
                        }
                    }
                    $text .= '</tbody></table>';
                    //debugga(Marion::$actions_module);exit;
                    return $has_actions?$text: '';
                    //foreach(Marion::$actions_module as $action => $functions){
                }
            ],
            

        ];
       
        $dataSource = (new DataSource('hooks'))
            ->addFields(['hooks.name','hooks.description','hooks.type','hooks.id','modules.directory']);
        $dataSource->queryBuilder()
        ->leftJoin('modules','modules.id','=','hooks.module_id');


        ListHelper::create('developer_hook',$this)
            ->setFieldsFromArray($fields)
            ->enableExport(true)
            //->setPerPage($limit)
            ->setExportTypes(['pdf','csv','excel'])
            ->enableBulkActions(false)
            ->enableSearch(true)
            ->setFieldsFromArray($fields)
            ->setDataSource($dataSource)
            ->onSearch(function(\Illuminate\Database\Query\Builder $query){
                if( $id = _var('id') ){
                    $query->where('id',$id);
                }
                if( $name = _var('name') ){
                    $query->where('hooks.name','like',"%{$name}%");
                }
                if( $description = _var('description') ){
                    $query->where('description','like',"%{$description}%");
                }
                if( $directory = _var('directory') ){
                    $query->where('directory','like',"%{$directory}%");
                }
                if( $type = _var('type') ){
                    $query->where('type','like',"%{$type}%");
                }
            })
            ->onSort(function(\Illuminate\Database\Query\Builder $query,$field,$order){
                if( in_array($field,['name','description','directory','type'])){
                    $query->orderBy($field,$order);
                }
            })->display();
		
	}

}



?>