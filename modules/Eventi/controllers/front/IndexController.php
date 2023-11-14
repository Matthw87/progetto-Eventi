<?php
use Marion\Controllers\FrontendController;
use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends FrontendController{

    function test(): void{
        $this->output('@Eventi/test.html');
    }
}
?>