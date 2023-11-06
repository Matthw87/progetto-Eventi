<?php
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Migration;
class InstallDefaultHooksMigration extends Migration{
            
    public function up(){
        
        $hooks = [
            [
                'name' => 'action_on_boot',
                'description' => 'Action performed on boot',
                'type' => 'action',
            ],
            [
                'name' => 'action_after_init',
                'description' => 'Action performed after initialization',
                'type' => 'action',
            ],
            [
                'name' => 'action_load_commands',
                'description' => 'Action performed in console. Add command to application',
                'type' => 'action',
            ],
            [
                'name' => 'action_override_set_language',
                'description' => 'Action performed for override setting language',
                'type' => 'action',
            ],
            [
                'name' => 'action_after_login',
                'description' => 'Action performed after login',
                'type' => 'action',
            ],
            [
                'name' => 'action_after_logout',
                'description' => 'Action performed after logout',
                'type' => 'action',
            ],
            [
                'name' => 'action_add_entity_method',
                'description' => 'action taken to add a method to an existing entity',
                'type' => 'action',
            ],
            [
                'name' => 'action_entity_after_load',
                'description' => 'Action performed after load entity',
                'type' => 'action',
            ],
            [
                'name' => 'action_entity_after_save',
                'description' => 'Action performed after save entity',
                'type' => 'action',
            ],
            [
                'name' => 'action_entity_before_delete',
                'description' => 'Action performed before delete entity',
                'type' => 'action',
            ],
            [
                'name' => 'action_entity_after_delete',
                'description' => 'Action performed after delete entity',
                'type' => 'action',
            ],
            [
                'name' => 'action_register_twig_templates_dir',
                'description' => 'Action performed to register templates dir',
                'type' => 'action',
            ],
            [
                'name' => 'action_register_media_front',
                'description' => 'Action performed to register js or css on front controller',
                'type' => 'action',
            ],
            [
                'name' => 'action_clean_cache',
                'description' => 'Action performed when clean cache',
                'type' => 'action',
            ],
            [
                'name' => 'display_account_home',
                'description' => 'Display content in homepage user',
                'type' => 'display',
            ],
            [
                'name' => 'display_login',
                'description' => 'Display content in login page',
                'type' => 'display',
            ],
            
            
            
        ];
        foreach($hooks as $h){
            DB::table('hooks')->insert($h);
        }
        
    }
    
    public function down(){
        $hooks = [
            'action_on_boot',
            'action_after_init',
            'action_after_login',
            'action_after_logout',
            'action_add_entity_method',
            'action_entity_after_load',
            'action_entity_after_save',
            'action_entity_before_delete',
            'action_entity_after_delete',
            'action_register_twig_templates_dir',
            'action_register_media_front',
            'action_clean_cache',
            'action_load_commands'
        ];
        DB::table('hooks')->whereIn('name',$hooks)->delete();
    }
}
?>