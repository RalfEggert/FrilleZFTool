<?php

namespace ZFTool\Controller;

use Zend\Code\Reflection\FileReflection;
use Zend\Code\Generator\ClassGenerator;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Version\Version;
use ZFTool\Module;
use ZFTool\Options\RequestOptions;

/**
 * Class InfoController
 *
 * @package ZFTool\Controller
 */
class InfoController extends AbstractActionController
{
    /**
     * @var AdapterInterface
     */
    protected $console;

    /**
     * @var RequestOptions
     */
    protected $requestOptions;

    /**
     * @param AdapterInterface $console
     * @param ModuleGenerator  $moduleGenerator
     */
    function __construct(
        AdapterInterface $console, RequestOptions $requestOptions
    ) {
        // setup dependencies
        $this->console        = $console;
        $this->requestOptions = $requestOptions;
    }

    /**
     * Show ZF2 version
     */
    public function versionAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->versionHelp();
        }

        // output header
        $this->consoleHeader('Fetching requested information');

        // get uf2 path
        $zf2Path = $this->getZF2Path();

        // fetch Version file
        if (file_exists($zf2Path . '/Zend/Version/Version.php')) {
            require_once $zf2Path . '/Zend/Version/Version.php';
            $msg = 'The application in this folder is using ';
        } else {
            $msg = 'The ZFTool is using ';
        }

        // start output
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');
        $this->console->writeLine($msg);
        $this->console->writeLine();
        $this->console->writeLine(
            '       => ' . 'Zend Framework '
            . Version::VERSION,
            Color::GREEN
        );

        // output footer
        $this->consoleFooter('requested info was successfully displayed');

    }

    /**
     * Show ZF2 version help
     */
    public function versionHelp()
    {
        // output header
        $this->consoleHeader('Display current Zend Framework 2 version', ' Help ');

        $this->console->writeLine(
            '       zf.php version [<path>]',
            Color::GREEN
        );
        $this->console->writeLine(
            '       zf.php --version [<path>]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       [<path>] ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) path to a ZF2 application.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }

    /**
     * Show installed modules
     */
    public function modulesAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->modulesHelp();
        }

        // output header
        $this->consoleHeader('Fetching requested information');

        // get needed options to shorten code
        $path = $this->requestOptions->getPath();

        // check for module path and application config
        if (!file_exists($path . '/module')
            || !file_exists($path . '/config/application.config.php')
        ) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The path '),
                    array(Color::RED    => $path),
                    array(Color::NORMAL => ' doesn\'t contain a ZF2 application.'),
                )
            );
        }

        // get modules
        $modules = $this->getModules($path);

        // check modules
        if (empty($modules)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'No modules installed. Are you in the root folder of a ZF2 app?'),
                )
            );
        }

        // start output
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');
        $this->console->write('Modules installed in ');
        $this->console->write($path, Color::GREEN);
        $this->console->writeLine(PHP_EOL);

        // output modules
        foreach ($modules as $module) {
            $this->console->writeLine(
                '       => ' . $module,
                Color::GREEN
            );
        }

        // output footer
        $this->consoleFooter('requested info was successfully displayed');

    }

    /**
     * Show ZF2 modules help
     */
    public function modulesHelp()
    {
        // output header
        $this->consoleHeader('Show all modules within a ZF2 application', ' Help ');

        $this->console->writeLine(
            '       zf.php modules [<path>]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       [<path>] ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) path to a ZF2 application.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }

    /**
     * Show controllers for a module
     */
    public function controllersAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->controllersHelp();
        }

        // output header
        $this->consoleHeader('Fetching requested information');

        // get needed options to shorten code
        $path       = $this->requestOptions->getPath();
        $moduleName = $this->requestOptions->getModuleName();
        $modulePath = realpath($this->requestOptions->getModulePath());

        // check for module path and application config
        if (!file_exists($path . '/module')
            || !file_exists($path . '/config/application.config.php')
        ) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The path '),
                    array(Color::RED    => $path),
                    array(Color::NORMAL => ' doesn\'t contain a ZF2 application.'),
                )
            );
        }

        // check if module name provided
        if (!$moduleName) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Please provide the module name as parameter.'),
                )
            );
        }

        // check if module exists
        if (!file_exists($modulePath)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => ' does not exist.'),
                )
            );
        }

        // get controllers
        $controllers = $this->getControllerForModule($modulePath);

        // check controllers
        if (empty($controllers)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'No controllers available for module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        // start output
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');
        $this->console->write('Controllers available in module ');
        $this->console->write($moduleName, Color::GREEN);
        $this->console->writeLine(PHP_EOL);

        // output controllers
        foreach ($controllers as $controllerClass => $controllerType) {
            $this->console->write(
                '       => ' . $controllerClass,
                Color::GREEN
            );
            $this->console->writeLine(
                ' (' . $controllerType . ')', Color::NORMAL
            );
        }

        // output footer
        $this->consoleFooter('requested info was successfully displayed');

    }

    /**
     * Show ZF2 controllers help
     */
    public function controllersHelp()
    {
        // output header
        $this->consoleHeader('Show all controllers for a module', ' Help ');

        $this->console->writeLine(
            '       zf.php controllers <module_name> [<path>]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       <module_name> ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Name of module.',
            Color::NORMAL
        );
        $this->console->write(
            '       [<path>]     ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) path to a ZF2 application.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }

    /**
     * Show actions for a controller in a module
     */
    public function actionsAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->actionsHelp();
        }

        // output header
        $this->consoleHeader('Fetching requested information');

        // get needed options to shorten code
        $path           = $this->requestOptions->getPath();
        $moduleName     = $this->requestOptions->getModuleName();
        $modulePath     = $this->requestOptions->getModulePath();
        $controllerKey  = $this->requestOptions->getControllerKey();
        $controllerName = $this->requestOptions->getControllerName();
        $controllerPath = $this->requestOptions->getControllerPath();
        $controllerFile = $this->requestOptions->getControllerFile();

        // check for module path and application config
        if (!file_exists($path . '/module')
            || !file_exists($path . '/config/application.config.php')
        ) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The path '),
                    array(Color::RED    => $path),
                    array(Color::NORMAL => ' doesn\'t contain a ZF2 application.'),
                )
            );
        }

        // check if controller name provided
        if (!$controllerName) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Please provide the controller name as parameter.'),
                )
            );
        }

        // check if module name provided
        if (!$moduleName) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Please provide the module name as parameter.'),
                )
            );
        }

        // check if module exists
        if (!file_exists($modulePath)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The module '),
                    array(Color::RED => $moduleName),
                    array(Color::NORMAL => ' does not exist.'),
                )
            );
        }

        // check if controller exists already in module
        if (!file_exists($controllerPath . $controllerFile)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The controller '),
                    array(Color::RED    => $controllerName),
                    array(Color::NORMAL => ' does not exist in module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        // get actions
        $actions = $this->getActionsForController(
            $controllerPath . $controllerFile, $controllerKey
        );

        // check actions
        if (empty($actions)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'No actions available for controller '),
                    array(Color::RED    => $controllerName),
                    array(Color::NORMAL => ' in module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        // start output
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');
        $this->console->write('Actions available in controller ');
        $this->console->write($controllerName, Color::GREEN);
        $this->console->write(' in module ');
        $this->console->write($moduleName, Color::GREEN);
        $this->console->writeLine(PHP_EOL);

        // output actions
        foreach ($actions as $actionMethod) {
            $this->console->writeLine(
                '       => ' . $actionMethod . '()',
                Color::GREEN
            );
        }

        // output footer
        $this->consoleFooter('requested info was successfully displayed');

    }

    /**
     * Show ZF2 actions help
     */
    public function actionsHelp()
    {
        // output header
        $this->consoleHeader('Show all actions for a controller in a module', ' Help ');

        $this->console->writeLine(
            '       zf.php actions <module_name> <controller_name> [<path>]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       <module_name>     ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Name of module.',
            Color::NORMAL
        );
        $this->console->write(
            '       <controller_name> ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Name of controller.',
            Color::NORMAL
        );
        $this->console->write(
            '       [<path>]         ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) path to a ZF2 application.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }

    /**
     * Get installed modules
     *
     * @return array
     */
    protected function getModules($path)
    {
        // define config file
        $configData = require $this->requestOptions->getPath() . '/config/application.config.php';

        // fetch modules
        $modules = $configData['modules'];

        // clear out ZFTool
        $modules = array_diff($modules, array('ZFTool'));

        return $modules;
    }

    /**
     * Get controllers for a module
     *
     * @return array
     */
    protected function getControllerForModule($modulePath)
    {
        // define config file
        $configData = require $modulePath . '/config/module.config.php';

        // initialize controllers
        $controllers = array();

        // check for no controllers
        if (!isset($configData['controllers'])) {
            return $controllers;
        }

        // loop through controllers
        foreach ($configData['controllers'] as $type => $controllerList) {
            // skip if not invokable nor factory
            if (!in_array($type, array('invokables', 'factories'))) {
                continue;
            }

            // loop through controller list
            foreach ($controllerList as $controllerKey => $controllerClass) {
                // add based on type
                if ($type == 'invokables') {
                    $controllers[$controllerKey] = 'invokable';
                } else {
                    $controllers[$controllerKey] = 'factory';
                }
            }
        }

        // sort by key
        ksort($controllers);

        return $controllers;
    }

    /**
     * Get actions for a controller
     *
     * @return array
     */
    protected function getActionsForController($controllerPath, $controllerKey)
    {
        // get file and class reflection
        $fileReflection  = new FileReflection(
            $controllerPath,
            true
        );
        $classReflection = $fileReflection->getClass(
            $controllerKey . 'Controller'
        );

        // setup class generator with reflected class
        $code = ClassGenerator::fromReflection($classReflection);

        // initialize controllers
        $actions = array();

        // lop through methods
        foreach (array_keys($code->getMethods()) as $methodName) {
            if (substr($methodName, -6) == 'Action') {
                $actions[] = $methodName;
            }
        }

        // sort actions
        sort($actions);

        return $actions;
    }

    /**
     * Get path to ZF2
     *
     * @return bool|string
     */
    protected function getZF2Path()
    {
        // check for ZF2 path
        if (getenv('ZF2_PATH')) {
            return getenv('ZF2_PATH');
        } elseif (get_cfg_var('zf2_path')) {
            return get_cfg_var('zf2_path');
        } elseif (is_dir('vendor/ZF2/library')) {
            return 'vendor/ZF2/library';
        } elseif (is_dir('vendor/zendframework/zendframework/library')) {
            return 'vendor/zendframework/zendframework/library';
        } elseif (is_dir('vendor/zendframework/zend-version')) {
            return 'vendor/zendframework/zend-version';
        } elseif (is_dir($this->requestOptions->getPath() . '/vendor/zendframework/zendframework/library')) {
            return $this->requestOptions->getPath() . '/vendor/zendframework/zendframework/library';
        }
        return false;
    }

}
