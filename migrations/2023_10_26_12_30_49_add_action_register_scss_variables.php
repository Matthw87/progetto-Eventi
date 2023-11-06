<?php
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Migration;
class addActionRegisterScssVariablesMigration extends Migration{
            
    public function up(){
        
        $hooks = [
            [
                'name' => 'action_register_scss_variables',
                'description' => 'Action used for load scss variables for general theme and pagecompose page styles',
                'type' => 'action',
            ]
        ];
        foreach($hooks as $h){
            DB::table('hooks')->insert($h);
        }
        
    }
    
    public function down(){
        $hooks = [
            'action_register_scss_variables'
        ];
        DB::table('hooks')->whereIn('name',$hooks)->delete();
    }
}
?>