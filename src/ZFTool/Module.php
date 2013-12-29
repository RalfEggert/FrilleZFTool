<?php

namespace ZFTool;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module implements ConsoleUsageProviderInterface, AutoloaderProviderInterface, ConfigProviderInterface
{
    const NAME    = 'ZFTool - Zend Framework 2 command line Tool, forked and pimped by Ralf Eggert';

    /**
     * @var ServiceLocatorInterface
     */
    protected $sm;

    public function onBootstrap(EventInterface $e)
    {
        $this->sm = $e->getApplication()->getServiceManager();
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    public function getConsoleBanner(ConsoleAdapterInterface $console)
    {
        $console->writeLine();
        $console->writeLine(
            str_pad('', $console->getWidth() - 1, ' ', STR_PAD_RIGHT),
            Color::NORMAL,
            Color::GREEN
        );
        $console->writeLine(
            str_pad(' ' . self::NAME, $console->getWidth() - 1, ' ', STR_PAD_BOTH),
            Color::NORMAL,
            Color::GREEN
        );
        $console->writeLine(
            str_pad('', $console->getWidth() - 1, ' ', STR_PAD_RIGHT),
            Color::NORMAL,
            Color::GREEN
        );
        $console->writeLine();

        return 'Usage:';
    }

    public function getConsoleUsage(ConsoleAdapterInterface $console)
    {
        $config = $this->sm->get('config');
        if(!empty($config['ZFTool']) && !empty($config['ZFTool']['disable_usage'])){
            return null; // usage information has been disabled
        }

        // TODO: Load strings from a translation container
        return array(

            'Add a -h option to each command to get additional help, examples:',
            'create controller -h' => '',
            'diag -h' => '',
            'config set -h' => '',

            'Display current Zend Framework 2 version:',
            'version | --version' => '',

            'Show all modules within a ZF2 application',
            'modules [<path>]' => '',

            'Show all controllers for a module',
            'controllers <module_name> [<path>]' => '',

            'Show all actions for a controller in a module',
            'actions <module_name> <controller_name> [<path>]' => '',

            'Run diagnostics:',
            'diag [<test_group_name>] [options]' => '',

            'List all configuration options:',
            'config list [<path>] [options]' => '',

            'Display a single config value:',
            'config get <config_name> [<path>] [options]' => '',

            'Set a single config value (to change scalar values in local configuration file):',
            'config set <config_name> <config_value> [<path>]' => '',

            'Create a skeleton application:',
            'create project <path>' => '',

            'Create a module:',
            'create module <module_name> [<path>] [options]' => '',

            'Create a controller in module:',
            'create controller <controller_name> <module_name> [<path>] [options]' => '',

            'Create a controller factory in module:',
            'create controller-factory <controller_name> <module_name> [<path>] [options]' => '',

            'Create an action in a controller:',
            'create action <action_name> <controller_name> <module_name> [<path>] [options]' => '',

            'Create the routing for a module:',
            'create routing <module_name> [<path>] [options]' => '',

            'Create a view helper in module:',
            'create view-helper <helper_name> <module_name> [<path>] [options]' => '',

            'Create a view helper factory in module:',
            'create view-helper-factory <helper_name> <module_name> [<path>] [options]' => '',

            'Generate a Classmap for a directory / module:',
            'generate classmap <directory> [<destination>]' => '',

            'Install ZF2 library to a path:',
            'install <path> [<version>]' => '',
        );
    }
}
