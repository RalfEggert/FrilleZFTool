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

            'Basic information:',
            'version | --version'                               => 'display current Zend Framework version',
            'modules [<path>]'                                  => 'show loaded modules',
            'controllers <moduleName> [<path>]'                 => 'show controllers for a module',
            'actions <controllerName> <moduleName> [<path>]'    => 'show actions for a controller in a module',

            'Diagnostics',
            'diag [options] [module name]'  => 'run diagnostics',
            array('[module name]'               , '(Optional) name of module to test'),
            array('-v --verbose'                , 'Display detailed information.'),
            array('-b --break'                  , 'Stop testing on first failure'),
            array('-q --quiet'                  , 'Do not display any output unless an error occurs.'),
            array('-d --debug'                     , 'Display raw debug info from tests.'),

            'Application configuration:',
            'config list'               => 'list all configuration options',
            'config get <name>'         => 'display a single config value, i.e. "config get db.host"',
            'config set <name> <value>' => 'set a single config value (use only to change scalar values)',

            'Project creation:',
            'create project <path>'     => 'create a skeleton application',
            array('<path>', 'The path of the project to be created'),

            'Module creation:',
            'create module <moduleName> [<path>] [--ignore|-i] [--apidocs|-a]'     => 'create a module',
            array('<moduleName>', 'The name of the module to be created'),
            array('<path>', 'The root path of a ZF2 application where to create the module'),
            array('--ignore | -i', 'Ignore coding conventions'),
            array('--apidocs | -a', 'Prevent the api doc block generation'),

            'Controller creation:',
            'create controller <controllerName> <moduleName> [<path>] [--factory|-f] [--ignore|-i] [--config|-c] [--apidocs|-a]' => 'create a controller in module',
            array('<controllerName>', 'The name of the controller to be created'),
            array('<moduleName>', 'The module in which the controller should be created'),
            array('<path>', 'The root path of a ZF2 application where to create the controller'),
            array('--factory | -f', 'Create a factory for the controller'),
            array('--ignore | -i', 'Ignore coding conventions'),
            array('--config | -c', 'Prevent that module configuration is updated'),
            array('--apidocs | -a', 'Prevent the api doc block generation'),

            'Action creation:',
            'create action <actionName> <controllerName> <moduleName> [<path>] [--ignore|-i] [--apidocs|-a]' => 'create an action in a controller',
            array('<actionName>', 'The name of the action to be created'),
            array('<controllerName>', 'The name of the controller in which the action should be created'),
            array('<moduleName>', 'The module containing the controller'),
            array('<path>', 'The root path of a ZF2 application where to create the action'),
            array('--ignore | -i', 'Ignore coding conventions'),
            array('--apidocs | -a', 'Prevent the api doc block generation'),

            'Route creation:',
            'create routing <moduleName> [<path>] [--single|-s]' => 'create the routing for a module',
            array('<moduleName>', 'The module containing the controller'),
            array('<path>', 'The root path of a ZF2 application where to create the action'),
            array('--single | -s', 'Create a single standard route for the module'),

            'Controller factory creation:',
            'create controller-factory <controllerName> <moduleName> [<path>] [--config|-c] [--apidocs|-a]' => 'create a controller factory in module',
            array('<controllerName>', 'The name of the controller the factory has to be created'),
            array('<moduleName>', 'The module in which the controller factory should be created'),
            array('<path>', 'The root path of a ZF2 application where to create the controller factory'),
            array('--config | -c', 'Prevent that module configuration is updated'),
            array('--apidocs | -a', 'Prevent the api doc block generation'),

            'Classmap generator:',
            'generate classmap <directory> [<destination>]' => '',
            array('<directory>',        'The directory to scan for PHP classes (use "." to use current directory)'),
            array('<destination>',    'File name for generated class map file  or - for standard output. '.
                                        'If not supplied, defaults to autoload_classmap.php inside <directory>.'),

            'Zend Framework 2 installation:',
            'install zf <path> [<version>]' => '',
            array('<path>', 'The directory where to install the ZF2 library'),
            array('<version>', 'The version to install, if not specified uses the last available'),
        );
    }
}
