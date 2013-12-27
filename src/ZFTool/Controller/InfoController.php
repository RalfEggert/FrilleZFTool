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
        // get uf2 path
        $zf2Path = $this->getZF2Path();

        // fetch Version file
        if (file_exists($zf2Path . '/Zend/Version/Version.php')) {
            require_once $zf2Path . '/Zend/Version/Version.php';
            $msg = 'The application in this folder is using Zend Framework ';
        } else {
            $msg = 'The ZFTool is using Zend Framework ';
        }

        // start output
        $this->console->writeLine(Module::NAME, Color::GREEN);
        $this->console->writeLine($msg . Version::VERSION);
    }

    /**
     * Show installed modules
     */
    public function modulesAction()
    {
        // get needed options to shorten code
        $path = realpath($this->requestOptions->getPath());

        // get modules
        $modules = $this->getModulesFromService();

        // check modules
        if (empty($modules)) {
            return $this->sendError(
                'No modules installed. Are you in the root folder of a ZF2 app?'
            );
        }

        // start output
        $this->console->writeLine(
            'Modules installed in ' . $path . ':',
            Color::GREEN
        );

        // output modules
        foreach ($modules as $module) {
            $this->console->writeLine(' - ' . $module);
        }
    }

    /**
     * Show controllers for a module
     */
    public function controllersAction()
    {
        // get needed options to shorten code
        $path       = $this->requestOptions->getPath();
        $moduleName = $this->requestOptions->getModuleName();
        $modulePath = realpath($this->requestOptions->getModulePath());

        // check for module path and application config
        if (!file_exists($path . '/module')
            || !file_exists($path . '/config/application.config.php')
        ) {
            return $this->sendError(
                'The path ' . $path . ' doesn\'t contain a ZF2 application. '
                . 'I cannot find a module here.'
            );
        }

        // check if module exists
        if (!file_exists($modulePath)) {
            return $this->sendError(
                'The module ' . $moduleName . ' does not exist.'
            );
        }

        // get controllers
        $controllers = $this->getControllerForModule($modulePath);

        // check controllers
        if (empty($controllers)) {
            return $this->sendError(
                'No controllers availabel for module ' . $moduleName .  '.'
            );
        }

        // start output
        $this->console->writeLine(
            'Controllers available in module ' . $moduleName . ':',
            Color::GREEN
        );

        // output controllers
        foreach ($controllers as $controllerClass => $controllerType) {
            $this->console->writeLine(
                ' - ' . $controllerClass . ' (' . $controllerType . ')'
            );
        }
    }

    /**
     * Show actions for a controller in a module
     */
    public function actionsAction()
    {
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
                'The path ' . $path . ' doesn\'t contain a ZF2 application. '
                . 'I cannot find a module here.'
            );
        }

        // check if module exists
        if (!file_exists($modulePath)) {
            return $this->sendError(
                'The module ' . $moduleName . ' does not exist.'
            );
        }

        // check if controller exists already in module
        if (!file_exists($controllerPath . $controllerFile)) {
            return $this->sendError(
                'The controller "' . $controllerClass
                . '" does not exists in module "' . $moduleName . '". '
                . 'I cannot find any controller actions here.'
            );
        }

        // get actions
        $actions = $this->getActionsForController(
            $controllerPath . $controllerFile, $controllerKey
        );

        // check actions
        if (empty($actions)) {
            return $this->sendError(
                'No actions availabel for controller ' . $controllerKey
                .  ' in module ' . $moduleName . '.'
            );
        }

        // start output
        $this->console->writeLine(
            'Actions available in controller ' . $controllerKey
            .  ' in module ' . $moduleName . ':',
            Color::GREEN
        );

        // output actions
        foreach ($actions as $actionMethod) {
            $this->console->writeLine(
                ' - ' . $actionMethod . '()'
            );
        }
    }

    /**
     * Get installed modules
     *
     * @return array
     */
    protected function getModulesFromService()
    {
        // try to load module manager
        try{
            /* @var $mm \Zend\ModuleManager\ModuleManager */
            $mm = $this->getServiceLocator()->get('modulemanager');
        } catch(ServiceNotFoundException $e) {
            return $this->sendError(
                'Cannot get Zend\ModuleManager\ModuleManager instance. Is your application using it?'
            );
        }

        // fetch modules
        $modules = array_keys($mm->getLoadedModules(false));

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
        }
        return false;
    }

}
