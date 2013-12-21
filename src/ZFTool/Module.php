<?php

namespace ZFTool;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module implements ConsoleUsageProviderInterface, AutoloaderProviderInterface, ConfigProviderInterface
{
    const NAME    = 'ZFTool - Zend Framework 2 command line Tool';

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
        return self::NAME;
    }

    public function getConsoleUsage(ConsoleAdapterInterface $console)
    {
        $config = $this->sm->get('config');
        if(!empty($config['ZFTool']) && !empty($config['ZFTool']['disable_usage'])){
            return null; // usage information has been disabled
        }

        // TODO: Load strings from a translation container
        return array(

            'Basic information:',
            'modules [list]'              => 'show loaded modules',
            'version | --version'         => 'display current Zend Framework version',

            'Diagnostics',
            'diag [options] [module name]'  => 'run diagnostics',
            array('[module name]'               , '(Optional) name of module to test'),
            array('-v --verbose'                , 'Display detailed information.'),
            array('-b --break'                  , 'Stop testing on first failure'),
            array('-q --quiet'                  , 'Do not display any output unless an error occurs.'),
            array('--debug'                     , 'Display raw debug info from tests.'),

            'Application configuration:',
            'config list'               => 'list all configuration options',
            'config get <name>'         => 'display a single config value, i.e. "config get db.host"',
            'config set <name> <value>' => 'set a single config value (use only to change scalar values)',

            'Project creation:',
            'create project <path>'     => 'create a skeleton application',
            array('<path>', 'The path of the project to be created'),

            'Module creation:',
            'create module <moduleName> [<path>] [--ignore-conventions|-i] [--no-docblocks|-d]'     => 'create a module',
            array('<moduleName>', 'The name of the module to be created'),
            array('<path>', 'The root path of a ZF2 application where to create the module'),
            array('--ignore-conventions | -i', 'Ignore coding conventions'),
            array('--no-docblocks | -d', 'Prevent the doc block generation'),

            'Controller creation:',
            'create controller <controllerName> <moduleName> [<path>] [--ignore-conventions|-i] [--no-config|-n] [--no-docblocks|-d]' => 'create a controller in module',
            array('<controllerName>', 'The name of the controller to be created'),
            array('<moduleName>', 'The module in which the controller should be created'),
            array('<path>', 'The root path of a ZF2 application where to create the controller'),
            array('--ignore-conventions | -i', 'Ignore coding conventions'),
            array('--no-config | -n', 'Prevent that module configuration is updated'),
            array('--no-docblocks | -d', 'Prevent the doc block generation'),

            'Action creation:',
            'create action <actionName> <controllerName> <moduleName> [<path>] [--ignore-conventions|-i] [--no-docblocks|-d]' => 'create an action in a controller',
            array('<actionName>', 'The name of the action to be created'),
            array('<controllerName>', 'The name of the controller in which the action should be created'),
            array('<moduleName>', 'The module containing the controller'),
            array('<path>', 'The root path of a ZF2 application where to create the action'),
            array('--ignore-conventions | -i', 'Ignore coding conventions'),
            array('--no-docblocks | -d', 'Prevent the doc block generation'),

            'Route creation:',
            'create routing <moduleName> [<path>] [--single-route|-s]' => 'create the routing for a module',
            array('<moduleName>', 'The module containing the controller'),
            array('<path>', 'The root path of a ZF2 application where to create the action'),
            array('--single-route | -s', 'Create a single standard route for the module'),

            'Classmap generator:',
            'classmap generate <directory> <classmap file> [--append|-a] [--overwrite|-w]' => '',
            array('<directory>',        'The directory to scan for PHP classes (use "." to use current directory)'),
            array('<classmap file>',    'File name for generated class map file  or - for standard output. '.
                                        'If not supplied, defaults to autoload_classmap.php inside <directory>.'),
            array('--append | -a',      'Append to classmap file if it exists'),
            array('--overwrite | -w',   'Whether or not to overwrite existing classmap file'),

            'Zend Framework 2 installation:',
            'install zf <path> [<version>]' => '',
            array('<path>', 'The directory where to install the ZF2 library'),
            array('<version>', 'The version to install, if not specified uses the last available'),
        );
    }
}
