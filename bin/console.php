#!/usr/bin/env php
<?php
define('_MARION_CONSOLE_',1);
require ('config/include.inc.php');
use Symfony\Component\Console\Application;

$application = new Application();

$application->addCommands(
    [
        new Marion\Commands\ModuleListCommand(),
        new Marion\Commands\ModuleInstallCommand(),
        new Marion\Commands\ModuleUninstallCommand(),
        new Marion\Commands\ModuleUpgradeCommand(),
        new Marion\Commands\ModuleCreateCommand(),
        new Marion\Commands\ModuleDisableCommand(),
        new Marion\Commands\ModuleEnableCommand(),
        new Marion\Commands\ModuleRequireCommand(),
        new Marion\Commands\ModuleSeederCommand(),
        new Marion\Commands\SetupCommand(),
        new Marion\Commands\MigrationCreateCommand(),
        new Marion\Commands\BuildCssCommand(),
        new Marion\Commands\MigrateCommand(),
        new Marion\Commands\MigrateRollbackCommand(),
        new Marion\Commands\MigrationListCommand(),
        new Marion\Commands\DatabaseReady(),
        new Marion\Commands\UpgradeCommand()
    ]
);
Marion\Core\Marion::do_action('action_load_commands',array($application));
$application->run();