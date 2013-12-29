<?php

namespace ZFTool\Controller;

use Zend\Code\Generator\Exception\RuntimeException as GeneratorException;
use Zend\Code\Generator;
use Zend\Code\Reflection;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use ZFTool\Generator\ModuleConfigurator;
use ZFTool\Generator\ModuleGenerator;
use ZFTool\Model\Skeleton;
use ZFTool\Model\Utility;
use ZFTool\Options\RequestOptions;

/**
 * Class CreateController
 *
 * @package ZFTool\Controller
 */
class CreateController extends AbstractActionController
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
     * @var ModuleGenerator
     */
    protected $moduleGenerator;

    /**
     * @var ModuleConfigurator
     */
    protected $moduleConfigurator;

    /**
     * @param AdapterInterface $console
     * @param ModuleGenerator  $moduleGenerator
     */
    function __construct(
        AdapterInterface $console, RequestOptions $requestOptions,
        ModuleGenerator $moduleGenerator, ModuleConfigurator $moduleConfigurator
    ) {
        // setup dependencies
        $this->console            = $console;
        $this->requestOptions     = $requestOptions;
        $this->moduleGenerator    = $moduleGenerator;
        $this->moduleConfigurator = $moduleConfigurator;
    }

    /**
     * Create a project
     *
     * @return ConsoleModel
     */
    public function projectAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->projectHelp();
        }

        // output header
        $this->consoleHeader('Creating new Zend Framework 2 project');

        // check for zip extension
        if (!extension_loaded('zip')) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'You need to install the ZIP extension of PHP.'),
                )
            );
        }

        // check for openssl extension
        if (!extension_loaded('openssl')) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'You need to install the OpenSSL extension of PHP.'),
                )
            );
        }

        // get needed options to shorten code
        $path   = $this->requestOptions->getPath();
        $tmpDir = $this->requestOptions->getTmpDir();

        // check if path provided
        if ($path == '.') {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Please provide the path to create the project in.'),
                )
            );
        }

        // check if path exists
        if (file_exists($path)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The directory '),
                    array(Color::RED    => $path),
                    array(Color::NORMAL => ' already exists. '),
                    array(Color::NORMAL => 'You cannot create a ZF2 project here.'),
                )
            );
        }

        // check last commit
        $commit = Skeleton::getLastCommit();
        if (false === $commit) { // error on github connection
            $tmpFile = Skeleton::getLastZip($tmpDir);
            if (empty($tmpFile)) {
                return $this->sendError(
                    array(
                        array(Color::NORMAL => 'I cannot access the API of GitHub.'),
                    )
                );
            }
            $this->console->writeLine(
                'Warning: I cannot connect to GitHub, I will use the last '
                . 'download of ZF2 Skeleton.',
                Color::LIGHT_RED
            );
        } else {
            $tmpFile = Skeleton::getTmpFileName($tmpDir, $commit);
        }

        // check for Skeleton App
        if (!file_exists($tmpFile)) {
            if (!Skeleton::getSkeletonApp($tmpFile)) {
                return $this->sendError(
                    array(
                        array(Color::NORMAL => 'I cannot access the ZF2 skeleton application in GitHub.'),
                    )
                );
            }
        }

        // set Zip Archive
        $zip = new \ZipArchive;
        if ($zip->open($tmpFile)) {
            $stateIndex0 = $zip->statIndex(0);
            $tmpSkeleton = $tmpDir . '/' . rtrim($stateIndex0['name'], "/");
            if (!$zip->extractTo($tmpDir)) {
                return $this->sendError(
                    array(
                        array(Color::NORMAL => 'Error during the unzip of '),
                        array(Color::RED    => $tmpFile),
                        array(Color::NORMAL => '.'),
                    )
                );
            }
            $result = Utility::copyFiles($tmpSkeleton, $path);
            if (file_exists($tmpSkeleton)) {
                Utility::deleteFolder($tmpSkeleton);
            }
            $zip->close();
            if (false === $result) {
                return $this->sendError(
                    array(
                        array(Color::NORMAL => 'Error during the copy of the files in '),
                        array(Color::RED    => $path),
                        array(Color::NORMAL => '.'),
                    )
                );
            }
        }

        // check for composer
        if (file_exists($path . '/composer.phar')) {
            exec('php ' . $path . '/composer.phar self-update');
        } else {
            if (!file_exists($tmpDir . '/composer.phar')) {
                if (!file_exists($tmpDir . '/composer_installer.php')) {
                    file_put_contents(
                        $tmpDir . '/composer_installer.php',
                        '?>' . file_get_contents(
                            'https://getcomposer.org/installer'
                        )
                    );
                }
                exec(
                    'php ' . $tmpDir . '/composer_installer.php --install-dir '
                    . $tmpDir
                );
            }
            copy($tmpDir . '/composer.phar', $path . '/composer.phar');
        }
        chmod($path . '/composer.phar', 0755);

        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');

        $this->console->write('ZF2 skeleton application installed in ');
        $this->console->writeLine(realpath($path), Color::GREEN);
        $this->console->writeLine();

        $this->console->writeLine('       => In order to execute the skeleton application you need to install the ZF2 library.');

        $this->console->write('       => Execute: ');
        $this->console->write('composer.phar install', Color::GREEN);
        $this->console->write(' in ');
        $this->console->writeLine(realpath($path), Color::GREEN);

        $this->console->write('       => For more info please read ');
        $this->console->writeLine(realpath($path) . '/README.md', Color::GREEN);

        // output footer
        $this->consoleFooter('project was successfully created');

    }

    /**
     * Create a project help
     */
    public function projectHelp()
    {
        // output header
        $this->consoleHeader('Create a new project with the SkeletonApplication', ' Help ');

        $this->console->writeLine(
            '       zf.php create project <path>',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       <path> ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Path of the project to be created.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }

    /**
     * Create a controller
     *
     * @return ConsoleModel
     */
    public function controllerAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->controllerHelp();
        }

        // output header
        $this->consoleHeader('Creating new controller');

        // get needed options to shorten code
        $path               = $this->requestOptions->getPath();
        $flagWithFactory    = $this->requestOptions->getFlagWithFactory();
        $moduleName         = $this->requestOptions->getModuleName();
        $modulePath         = $this->requestOptions->getModulePath();
        $controllerName     = $this->requestOptions->getControllerName();
        $controllerPath     = $this->requestOptions->getControllerPath();
        $controllerClass    = $this->requestOptions->getControllerClass();
        $controllerFile     = $this->requestOptions->getControllerFile();
        $controllerViewPath = $this->requestOptions->getControllerViewPath();
        $actionViewFile     = $this->requestOptions->getActionViewFile();

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

        // set mode
        if (file_exists($controllerPath . $controllerFile)) {
            $mode = 'update';
        } else {
            $mode = 'insert';
        }

        // check if controller exists already in module
        if ($mode == 'update' && !$flagWithFactory) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The controller '),
                    array(Color::RED    => $controllerName),
                    array(Color::NORMAL => ' already exists in module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        if ($mode == 'insert') {
            // write start message
            $this->console->write('       => Creating controller ');
            $this->console->write ($controllerName, Color::GREEN);
            $this->console->write(' in module ');
            $this->console->writeLine($moduleName, Color::GREEN);

            // create controller class
            $controllerFlag = $this->moduleGenerator->createController();

            // write start message
            $this->console->write('       => Creating view script ');
            $this->console->write ($actionViewFile, Color::GREEN);
            $this->console->write(' in ');
            $this->console->writeLine($controllerViewPath, Color::GREEN);

            // create view script
            $viewScriptFlag = $this->moduleGenerator->createViewScript();

            // write start message
            $this->console->write('       => Adding controller configuration for ');
            $this->console->writeLine($moduleName, Color::GREEN);

            // add controller configuration to module
            $moduleConfig = $this->moduleConfigurator->addControllerConfig();
        }

        // check for factory flag
        if ($flagWithFactory) {

            // create controller factory class
            try {
                $factoryFlag = $this->moduleGenerator->createControllerFactory();
            } catch (GeneratorException $e) {
                return $this->sendError(
                    array(
                        array(Color::NORMAL => 'The factory for the controller '),
                        array(Color::RED    => $controllerName),
                        array(Color::NORMAL => ' of module '),
                        array(Color::RED    => $moduleName),
                        array(Color::NORMAL => ' exists already.'),
                    )
                );
            }

            // write start message
            $this->console->write('       => Creating factory for controller ');
            $this->console->write ($controllerName, Color::GREEN);
            $this->console->write(' in module ');
            $this->console->writeLine($moduleName, Color::GREEN);

            // add controller factory configuration to module
            $moduleConfig = $this->moduleConfigurator->addControllerFactoryConfig();
        } else {
            $factoryFlag = false;
        }

        // check for module config updates
        if ($moduleConfig) {
            // update module configuration
            $this->moduleGenerator->updateConfiguration(
                $moduleConfig, $modulePath . '/config/module.config.php'
            );

            // write start message
            $this->console->write('       => Updating configuration for module ');
            $this->console->writeLine($moduleName, Color::GREEN);
        }

        $this->console->writeLine();
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');

        // write message
        if ($factoryFlag) {
            $this->console->write('The controller ');
            $this->console->write($controllerName, Color::GREEN);
            $this->console->write(' has been created with a factory in module ');
            $this->console->writeLine($moduleName, Color::GREEN);
        } else {
            $this->console->write('The controller ');
            $this->console->write($controllerName, Color::GREEN);
            $this->console->write(' has been created in module ');
            $this->console->writeLine($moduleName, Color::GREEN);
        }

        // output footer
        $this->consoleFooter('controller was successfully created');

    }

    /**
     * Create a controller help
     */
    public function controllerHelp()
    {
        // output header
        $this->consoleHeader('Create a new controller within an module', ' Help ');

        $this->console->writeLine(
            '       zf.php create controller <controller_name> <module_name> [<path>] [options]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       <controller_name>  ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Name of controller to be created.',
            Color::NORMAL
        );
        $this->console->write(
            '       <module_name>      ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Module in which controller should be created.',
            Color::NORMAL
        );
        $this->console->write(
            '       [<path>]           ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) path to a ZF2 application.',
            Color::NORMAL
        );
        $this->console->write(
            '       --factory|-f       ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Create a factory for the controller.',
            Color::NORMAL
        );
        $this->console->write(
            '       --ignore|-i        ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Ignore coding conventions.',
            Color::NORMAL
        );
        $this->console->write(
            '       --config|-c        ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Prevent that module configuration is updated.',
            Color::NORMAL
        );
        $this->console->write(
            '       --apidocs|-a       ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Prevent the api doc block generation.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }

    /**
     * Create an action method
     *
     * @return ConsoleModel
     */
    public function methodAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->methodHelp();
        }

        // output header
        $this->consoleHeader('Creating new action');

        // get needed options to shorten code
        $path               = $this->requestOptions->getPath();
        $moduleName         = $this->requestOptions->getModuleName();
        $controllerName     = $this->requestOptions->getControllerName();
        $controllerPath     = $this->requestOptions->getControllerPath();
        $controllerClass    = $this->requestOptions->getControllerClass();
        $controllerFile     = $this->requestOptions->getControllerFile();
        $controllerViewPath = $this->requestOptions->getControllerViewPath();
        $actionName         = $this->requestOptions->getActionName();
        $actionViewFile     = $this->requestOptions->getActionViewFile();

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

        // check if action name provided
        if (!$actionName) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Please provide the action name as parameter.'),
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

        // check if controller exists already in module
        if (!file_exists($controllerPath . $controllerFile)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The controller '),
                    array(Color::RED    => $controllerName),
                    array(Color::NORMAL => ' does not exist in module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => '. I cannot create an action here.'),
                )
            );
        }

        // write start message
        $this->console->write('       => Adding action ');
        $this->console->write ($actionName, Color::GREEN);
        $this->console->write(' to controller ');
        $this->console->write ($controllerName, Color::GREEN);
        $this->console->write(' in module ');
        $this->console->writeLine($moduleName, Color::GREEN);

        // update controller class
        try {
            $controllerFlag = $this->moduleGenerator->updateController();
        } catch (GeneratorException $e) {
            $this->console->writeLine();

            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The action '),
                    array(Color::RED    => $actionName),
                    array(Color::NORMAL => ' already exists in controller '),
                    array(Color::RED    => $controllerName),
                    array(Color::NORMAL => ' of module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        // write start message
        $this->console->write('       => Creating view script ');
        $this->console->write ($actionViewFile, Color::GREEN);
        $this->console->write(' in ');
        $this->console->writeLine($controllerViewPath, Color::GREEN);

        // create view script
        $viewScriptFlag = $this->moduleGenerator->createViewScript();

        $this->console->writeLine();
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');

        // write message
        $this->console->write('The action ');
        $this->console->write($actionName, Color::GREEN);
        $this->console->write(' has been created in controller ');
        $this->console->write($controllerName, Color::GREEN);
        $this->console->write(' of module ');
        $this->console->writeLine($moduleName, Color::GREEN);

        // output footer
        $this->consoleFooter('action was successfully created');

    }

    /**
     * Create an action method help
     */
    public function methodHelp()
    {
        // output header
        $this->consoleHeader('Create a new action for a controller within an module', ' Help ');

        $this->console->writeLine(
            '       zf.php create action <action_name> <controller_name> <module_name> [<path>] [options]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       <action_name>      ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Name of action to be created.',
            Color::NORMAL
        );
        $this->console->write(
            '       <controller_name>  ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Name of controller in which action should be created.',
            Color::NORMAL
        );
        $this->console->write(
            '       <module_name>      ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Module containing the controller.',
            Color::NORMAL
        );
        $this->console->write(
            '       [<path>]           ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) path to a ZF2 application.',
            Color::NORMAL
        );
        $this->console->write(
            '       --ignore|-i        ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Ignore coding conventions.',
            Color::NORMAL
        );
        $this->console->write(
            '       --apidocs|-a       ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Prevent the api doc block generation.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }

    /**
     * Create a module
     *
     * @return ConsoleModel
     */
    public function moduleAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->moduleHelp();
        }

        // output header
        $this->consoleHeader('Creating new module');

        // get needed options to shorten code
        $path          = $this->requestOptions->getPath();
        $moduleName    = $this->requestOptions->getModuleName();
        $modulePath    = $this->requestOptions->getModulePath();
        $moduleViewDir = $this->requestOptions->getModuleViewDir();

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
        if (file_exists($modulePath)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => ' already exists.'),
                )
            );
        }

        // write start message
        $this->console->write('       => Creating module class for module ');
        $this->console->writeLine($moduleName, Color::GREEN);

        // Create the Module.php
        $this->moduleGenerator->createModule();

        // write start message
        $this->console->write('       => Creating configuration for module ');
        $this->console->writeLine($moduleName, Color::GREEN);

        // Create the module.config.php
        $this->moduleGenerator->createConfiguration();

        // write start message
        $this->console->write('       => Adding module ');
        $this->console->write($moduleName, Color::GREEN);
        $this->console->writeLine(' to application configuration.');

        // add module configuration to application
        $applicationConfig = $this->moduleConfigurator->addModuleConfig();

        // check for module config updates
        if ($applicationConfig) {
            // update module configuration
            $configFlag = $this->moduleGenerator->updateConfiguration(
                $applicationConfig, $path . '/config/application.config.php'
            );
        }

        $this->console->writeLine();
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');

        $this->console->write('The module ');
        $this->console->write($moduleName, Color::GREEN);
        $this->console->write(' has been created');

        // success
        if ($path !== '.') {
            $this->console->write(' in ');
            $this->console->writeLine($path, Color::GREEN);
        } else {
            $this->console->writeLine();
        }

        // output footer
        $this->consoleFooter('module was successfully created');

    }

    /**
     * Create a module help
     */
    public function moduleHelp()
    {
        // output header
        $this->consoleHeader('Create a new module', ' Help ');

        $this->console->writeLine(
            '       zf.php create module <module_name> [<path>] [options]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       <module_name>  ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Name of module to be created.',
            Color::NORMAL
        );
        $this->console->write(
            '       [<path>]       ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) path to a ZF2 application.',
            Color::NORMAL
        );
        $this->console->write(
            '       --ignore|-i    ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Ignore coding conventions.',
            Color::NORMAL
        );
        $this->console->write(
            '       --apidocs|-a   ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Prevent the api doc block generation.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }

    /**
     * Create the routing for a module
     *
     * @return ConsoleModel
     */
    public function routingAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->routingHelp();
        }

        // output header
        $this->consoleHeader('Creating the routing for a module');

        // get needed options to shorten code
        $path       = $this->requestOptions->getPath();
        $moduleName = $this->requestOptions->getModuleName();
        $modulePath = $this->requestOptions->getModulePath();

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

        // write start message
        $this->console->write('       => Creating routing for module ');
        $this->console->writeLine($moduleName, Color::GREEN);

        // set config flag
        $configFlag = false;

        // update controller class
        try {
            // add controller configuration to module
            $moduleConfig = $this->moduleConfigurator->addRouterConfig();

            // check for module config updates
            if ($moduleConfig) {
                // update module configuration
                $configFlag = $this->moduleGenerator->updateConfiguration(
                    $moduleConfig, $modulePath . '/config/module.config.php'
                );

                // success message
                $this->console->write('       => Updating configuration for module ');
                $this->console->writeLine($moduleName, Color::GREEN);

                // change flag
                $configFlag = true;
            }
        } catch (GeneratorException $e) {
            $this->console->writeLine();

            return $this->sendError(
                array(
                    array(Color::NORMAL => 'No controller exist in the module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        $this->console->writeLine();
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');

        $this->console->write('The routing has been configured in module ');
        $this->console->writeLine($moduleName, Color::GREEN);

        // output footer
        $this->consoleFooter('routing was successfully created');

    }

    /**
     * Create the routing for a module help
     */
    public function routingHelp()
    {
        // output header
        $this->consoleHeader('Create the routing for a module', ' Help ');

        $this->console->writeLine(
            '       zf.php create routing <module_name> [<path>] [options]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       <module_name>  ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Name of module to create the routing for.',
            Color::NORMAL
        );
        $this->console->write(
            '       [<path>]       ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) path to a ZF2 application.',
            Color::NORMAL
        );
        $this->console->write(
            '       --single|-s    ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Create single standard route for the module.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }

    /**
     * Create a view helper
     *
     * @return ConsoleModel
     */
    public function viewHelperAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->viewHelperHelp();
        }

        // output header
        $this->consoleHeader('Creating new view helper');

        // get needed options to shorten code
        $path               = $this->requestOptions->getPath();
        $flagWithFactory    = $this->requestOptions->getFlagWithFactory();
        $moduleName         = $this->requestOptions->getModuleName();
        $modulePath         = $this->requestOptions->getModulePath();
        $viewHelperName     = $this->requestOptions->getViewHelperName();
        $viewHelperPath     = $this->requestOptions->getViewHelperPath();
        $viewHelperClass    = $this->requestOptions->getViewHelperClass();
        $viewHelperFile     = $this->requestOptions->getViewHelperFile();

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

        // check if helper name provided
        if (!$viewHelperName) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Please provide the view helper name as parameter.'),
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

        // set mode
        if (file_exists($viewHelperPath . $viewHelperFile)) {
            $mode = 'update';
        } else {
            $mode = 'insert';
        }

        // check if view helper exists already in module
        if ($mode == 'update' && !$flagWithFactory) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The view helper '),
                    array(Color::RED    => $viewHelperName),
                    array(Color::NORMAL => ' already exists in module '),
                    array(Color::RED    => $moduleName),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        // create view helper
        if ($mode == 'insert') {
            // write start message
            $this->console->write('       => Creating view helper ');
            $this->console->write ($viewHelperName, Color::GREEN);
            $this->console->write(' in module ');
            $this->console->writeLine($moduleName, Color::GREEN);

            // create view helper class
            $viewHelperFlag = $this->moduleGenerator->createViewHelper();

            // write start message
            $this->console->write('       => Adding view helper configuration for ');
            $this->console->writeLine($moduleName, Color::GREEN);

            // add view helper configuration to module
            $moduleConfig = $this->moduleConfigurator->addViewHelperConfig();
        }

        // check for factory flag
        if ($flagWithFactory) {

            // create view helper factory class
            try {
                $factoryFlag = $this->moduleGenerator->createViewHelperFactory();
            } catch (GeneratorException $e) {
                return $this->sendError(
                    array(
                        array(Color::NORMAL => 'The factory for the view helper '),
                        array(Color::RED    => $viewHelperName),
                        array(Color::NORMAL => ' of module '),
                        array(Color::RED    => $moduleName),
                        array(Color::NORMAL => ' exists already.'),
                    )
                );
            }

            // write start message
            $this->console->write('       => Creating factory for view helper ');
            $this->console->write ($viewHelperName, Color::GREEN);
            $this->console->write(' in module ');
            $this->console->writeLine($moduleName, Color::GREEN);

            // add view helper factory configuration to module
            $moduleConfig = $this->moduleConfigurator->addViewHelperFactoryConfig();
        } else {
            $factoryFlag = false;
        }

        // check for module config updates
        if ($moduleConfig) {
            // update module configuration
            $this->moduleGenerator->updateConfiguration(
                $moduleConfig, $modulePath . '/config/module.config.php'
            );

            // write start message
            $this->console->write('       => Updating configuration for module ');
            $this->console->writeLine($moduleName, Color::GREEN);
        }

        $this->console->writeLine();
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');

        // write message
        if ($factoryFlag) {
            $this->console->write('The view helper ');
            $this->console->write($viewHelperName, Color::GREEN);
            $this->console->write(' has been created with a factory in module ');
            $this->console->writeLine($moduleName, Color::GREEN);
        } else {
            $this->console->write('The view helper ');
            $this->console->write($viewHelperName, Color::GREEN);
            $this->console->write(' has been created in module ');
            $this->console->writeLine($moduleName, Color::GREEN);
        }

        $this->console->writeLine();
        $this->console->writeLine('       => In order to use the view helper add the following code to any view script.');
        $this->console->writeLine('          <?php echo $this->' . lcfirst($viewHelperName) . '(); ?>', Color::CYAN);

        // output footer
        $this->consoleFooter('view helper was successfully created');

    }

    /**
     * Create a view helper help
     */
    public function viewHelperHelp()
    {
        // output header
        $this->consoleHeader('Create a new view helper within an module', ' Help ');

        $this->console->writeLine(
            '       zf.php create view-helper <helper_name> <module_name> [<path>] [options]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       <helper_name>      ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Name of view helper to be created.',
            Color::NORMAL
        );
        $this->console->write(
            '       <module_name>      ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Module in which view helper should be created.',
            Color::NORMAL
        );
        $this->console->write(
            '       [<path>]           ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) path to a ZF2 application.',
            Color::NORMAL
        );
        $this->console->write(
            '       --factory|-f       ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Create a factory for the view helper.',
            Color::NORMAL
        );
        $this->console->write(
            '       --ignore|-i        ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Ignore coding conventions.',
            Color::NORMAL
        );
        $this->console->write(
            '       --config|-c        ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Prevent that module configuration is updated.',
            Color::NORMAL
        );
        $this->console->write(
            '       --apidocs|-a       ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Prevent the api doc block generation.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }
}
