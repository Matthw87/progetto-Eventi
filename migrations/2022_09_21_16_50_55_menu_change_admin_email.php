<?php
    use Illuminate\Database\Capsule\Manager as DB;
    use Marion\Core\Migration;
    use Marion\Entities\Cms\MenuItem;

    class menuChangeAdminEmailMigration extends Migration{

        public function up(){
            $this->addMenu();
        }

        public function down(){
            //to do
        }

        private function addMenu()
        {
            $changeCredentials = MenuItem::create()->set(
                [
                'tag' => 'change_credentials',
                'permission' => 'admin',
                'scope' => 'admin',
                'icon' => 'fa fa-lock',
                'url' => '',
                'active' => 1,
                'priority' => 1,
                'showLabel' => 0,
                'targetBlank' => 0,
                'name' => 'Cambio credenziali'
                ]
            )->save();

            $changeCredentialsChildren = [
                [
                    'tag' => 'change_credentials_email',
                    'permission' => 'config',
                    'scope' => 'admin',
                    'url' => 'index.php?ctrl=ChangeCredentials&action=email',
                    'active' => 1,
                    'priority' => 1,
                    'showLabel' => 0,
                    'targetBlank' => 0,
                    'name' => 'Cambio email',
                    'parent' => $changeCredentials->id
                ],
                [
                    'tag' => 'change_credentials_pwd',
                    'permission' => 'config',
                    'scope' => 'admin',
                    'url' => 'index.php?ctrl=ChangeCredentials&action=pwd',
                    'active' => 1,
                    'priority' => 1,
                    'showLabel' => 0,
                    'targetBlank' => 0,
                    'name' => 'Cambio password',
                    'parent' => $changeCredentials->id
                ],
            ];

            foreach($changeCredentialsChildren as $children)
            {
                $obj = MenuItem::create();
                $obj->set($children);
                $obj->save();
            }
        }
    }
?>