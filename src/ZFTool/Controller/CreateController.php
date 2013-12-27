<?php

namespace ZFTool\Controller;

use Zend\Code\Generator\Exception\RuntimeException as GeneratorException;
use Zend\Code\Generator;
use Zend\Code\Reflection;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Stdlib\Parameters;
use Zend\Filter\StaticFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\ConsoleModel;
use ZFTool\Generator\ModuleGenerator;
use ZFTool\Generator\ModuleConfigurator;
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
        // check for zip extension
        if (!extension_loaded('zip')) {
            return $this->sendError(
                'You need to install the ZIP extension of PHP'
            );
        }

        // check for openssl extension
        if (!extension_loaded('openssl')) {
            return $this->sendError(
                'You need to install the OpenSSL extension of PHP'
            );
        }

        // get needed options to shorten code
        $path   = $this->requestOptions->getPath();
        $tmpDir = $this->requestOptions->getTmpDir();

        // check if path exists
        if (file_exists($path)) {
            return $this->sendError(
                'The directory ' . $path . ' already exists. '
                . 'You cannot create a ZF2 project here.'
            );
        }

        // check last commit
        $commit = Skeleton::getLastCommit();
        if (false === $commit) { // error on github connection
            $tmpFile = Skeleton::getLastZip($tmpDir);
            if (empty($tmpFile)) {
                return $this->sendError(
                    'I cannot access the API of GitHub.'
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
                    'I cannot access the ZF2 skeleton application in GitHub.'
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
                    'Error during the unzip of ' . $tmpFile
                );
            }
            $result = Utility::copyFiles($tmpSkeleton, $path);
            if (file_exists($tmpSkeleton)) {
                Utility::deleteFolder($tmpSkeleton);
            }
            $zip->close();
            if (false === $result) {
                return $this->sendError(
                    'Error during the copy of the files in ' . $path
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
        $this->console->writeLine(
            'ZF2 skeleton application installed in ' . $path, Color::GREEN
        );
        $this->console->writeLine(
            'In order to execute the skeleton application you need to '
            . 'install the ZF2 library.'
        );
        $this->console->writeLine(
            'Execute: "composer.phar install" in ' . $path
        );
        $this->console->writeLine(
            'For more info in ' . $path . '/README.md'
        );
    }

    /**
     * Create a controller
     *
     * @return ConsoleModel
     */
    public function controllerAction()
    {
        // get needed options to shorten code
        $path               = $this->requestOptions->getPath();
        $flagWithFactory    = $this->requestOptions->getFlagWithFactory();
        $moduleName         = $this->requestOptions->getModuleName();
        $modulePath         = $this->requestOptions->getModulePath();
        $controllerName     = $this->requestOptions->getControllerName();
        $controllerPath     = $this->requestOptions->getControllerPath();
        $controllerClass    = $this->requestOptions->getControllerClass();
        $controllerFile     = $this->requestOptions->getControllerFile();

        // check for module path and application config
        if (!file_exists($path . '/module')
            || !file_exists($path . '/config/application.config.php')
        ) {
            return $this->sendError(
                'The path ' . $path . ' doesn\'t contain a ZF2 application. '
                . 'I cannot create a controller here.'
            );
        }

        // check if controller exists already in module
        if (file_exists($controllerPath . $controllerFile)) {
            return $this->sendError(
                'The controller "' . $controllerClass
                . '" already exists in module "' . $moduleName . '".'
            );
        }

        // write start message
        $this->console->writeLine(
            'Creating controller "' . $controllerName
            . '" in module "' . $moduleName . '".',
            Color::YELLOW
        );

        // create controller class
        $controllerFlag = $this->moduleGenerator->createController();

        // create view script
        $viewScriptFlag = $this->moduleGenerator->createViewScript();

        // add controller configuration to module
        $moduleConfig = $this->moduleConfigurator->addControllerConfig();

        // check for factory flag
        if ($flagWithFactory) {
            // create controller factory class
            $factoryFlag = $this->moduleGenerator->createControllerFactory();

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

            // success message
            $this->console->writeLine(
                'Module configuration was updated for module "'
                . $moduleName . '".',
                Color::WHITE
            );
        }

        // write message
        if ($controllerFlag && $viewScriptFlag && $factoryFlag) {
            $this->console->writeLine(
                'The controller "' . $controllerName
                . '" has been created with a factory in module "'
                . $moduleName . '".',
                Color::GREEN
            );
        } elseif ($controllerFlag && $viewScriptFlag) {
            $this->console->writeLine(
                'The controller "' . $controllerName
                . '" has been created in module "' . $moduleName . '".',
                Color::GREEN
            );
        } else {
            $this->console->writeLine(
                'There was an error during controller creation.',
                Color::RED
            );
        }
    }

    /**
     * Create a controller factory
     *
     * @return ConsoleModel
     */
    public function controllerFactoryAction()
    {
        // get needed options to shorten code
        $path               = $this->requestOptions->getPath();
        $moduleName         = $this->requestOptions->getModuleName();
        $modulePath         = $this->requestOptions->getModulePath();
        $controllerName     = $this->requestOptions->getControllerName();
        $controllerPath     = $this->requestOptions->getControllerPath();
        $controllerClass    = $this->requestOptions->getControllerClass();
        $controllerFile     = $this->requestOptions->getControllerFile();

        // check for module path and application config
        if (!file_exists($path . '/module')
            || !file_exists($path . '/config/application.config.php')
        ) {
            return $this->sendError(
                'The path ' . $path . ' doesn\'t contain a ZF2 application. '
                . 'I cannot create a controller here.'
            );
        }

        // check if controller exists already in module
        if (!file_exists($controllerPath . $controllerFile)) {
            return $this->sendError(
                'The controller "' . $controllerClass
                . '" does not exist in module "' . $moduleName . '".'
            );
        }

        // write start message
        $this->console->writeLine(
            'Creating factory for controller "' . $controllerName
            . '" in module "' . $moduleName . '".',
            Color::YELLOW
        );

        // create controller factory class
        try {
            $factoryFlag = $this->moduleGenerator->createControllerFactory();
        } catch (GeneratorException $e) {
            return $this->sendError(
                'The factory for the controller "' . $controllerName
                . '" of module "' . $moduleName . '" exists already.'
            );
        }

        // add controller factory configuration to module
        $moduleConfig = $this->moduleConfigurator->addControllerFactoryConfig();

        // check for module config updates
        if ($moduleConfig) {
            // update module configuration
            $this->moduleGenerator->updateConfiguration(
                $moduleConfig, $modulePath . '/config/module.config.php'
            );

            // success message
            $this->console->writeLine(
                'Module configuration was updated for module "'
                . $moduleName . '".',
                Color::WHITE
            );
        }

        // write message
        if ($factoryFlag) {
            $this->console->writeLine(
                'The factory for the controller "' . $controllerName
                . '" has been created in module "' . $moduleName . '".',
                Color::GREEN
            );
        } else {
            $this->console->writeLine(
                'There was an error during controller factory creation.',
                Color::RED
            );
        }
    }

    /**
     * Create an action method
     *
     * @return ConsoleModel
     */
    public function methodAction()
    {
        // get needed options to shorten code
        $path            = $this->requestOptions->getPath();
        $moduleName      = $this->requestOptions->getModuleName();
        $controllerName  = $this->requestOptions->getControllerName();
        $controllerPath  = $this->requestOptions->getControllerPath();
        $controllerClass = $this->requestOptions->getControllerClass();
        $controllerFile  = $this->requestOptions->getControllerFile();
        $actionName      = $this->requestOptions->getActionName();

        // check for module path and application config
        if (!file_exists($path . '/module')
            || !file_exists($path . '/config/application.config.php')
        ) {
            return $this->sendError(
                'The path ' . $path . ' doesn\'t contain a ZF2 application. '
                . 'I cannot create a controller action here.'
            );
        }

        // check if controller exists already in module
        if (!file_exists($controllerPath . $controllerFile)) {
            return $this->sendError(
                'The controller "' . $controllerClass
                . '" does not exists in module "' . $moduleName . '". '
                . 'I cannot create a controller action here.'
            );
        }

        // write start message
        $this->console->writeLine(
            'Creating action "' . $actionName
            . '" in controller "' . $controllerName
            . '" in module "' . $moduleName . '".',
            Color::YELLOW
        );

        // update controller class
        try {
            $controllerFlag = $this->moduleGenerator->updateController();
        } catch (GeneratorException $e) {
            return $this->sendError(
                'The action "' . $actionName
                . '" already exists in controller "' . $controllerName
                . '" of module "' . $moduleName . '".'
            );
        }

        // create view script
        $viewScriptFlag = $this->moduleGenerator->createViewScript();

        // write message
        if ($controllerFlag && $viewScriptFlag) {
            $this->console->writeLine(
                'The action "' . $actionName
                . '" has been created in controller "' . $controllerName
                . '" of module "' . $moduleName . '".',
                Color::GREEN
            );
        } else {
            $this->console->writeLine(
                'There was an error during action creation.',
                Color::RED
            );
        }
    }

    /**
     * Create a module
     *
     * @return ConsoleModel
     */
    public function moduleAction()
    {
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
                'The path ' . $path . ' doesn\'t contain a ZF2 application. '
                . 'I cannot create a module here.'
            );
        }

        // check if module exists
        if (file_exists($modulePath)) {
            return $this->sendError(
                'The module ' . $moduleName . ' already exists.'
            );
        }

        // write start message
        $this->console->writeLine(
            'Creating module "' . $moduleName . '".',
            Color::YELLOW
        );

        // Create the Module.php
        $this->moduleGenerator->createModule();

        // Create the module.config.php
        $this->moduleGenerator->createConfiguration();

        // add module configuration to application
        $applicationConfig = $this->moduleConfigurator->addModuleConfig();

        // check for module config updates
        if ($applicationConfig) {
            // update module configuration
            $configFlag = $this->moduleGenerator->updateConfiguration(
                $applicationConfig, $path . '/config/application.config.php'
            );

            // success message
            $this->console->writeLine(
                'Application configuration was updated.',
                Color::WHITE
            );
        }

        // success
        if ($path === '.') {
            $this->console->writeLine(
                'The module "' . $moduleName . '" has been created',
                Color::GREEN
            );
        } else {
            $this->console->writeLine(
                'The module "' . $moduleName . '" has been created in ' . $path,
                Color::GREEN
            );
        }
    }

    /**
     * Create the routing for a module
     *
     * @return ConsoleModel
     */
    public function routingAction()
    {
        // get needed options to shorten code
        $path       = $this->requestOptions->getPath();
        $moduleName = $this->requestOptions->getModuleName();
        $modulePath = $this->requestOptions->getModulePath();

        // check for module path and application config
        if (!file_exists($path . '/module')
            || !file_exists($path . '/config/application.config.php')
        ) {
            return $this->sendError(
                'The path ' . $path . ' doesn\'t contain a ZF2 application. '
                . 'I cannot create the routing here.'
            );
        }

        // check if module exists
        if (!file_exists($modulePath)) {
            return $this->sendError(
                'The module ' . $moduleName . ' does not exist.'
            );
        }

        // write start message
        $this->console->writeLine(
            'Creating routing in module "' . $moduleName . '".',
            Color::YELLOW
        );

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
                $this->console->writeLine(
                    'Module configuration was updated for module "'
                    . $moduleName . '".',
                    Color::WHITE
                );

                // change flag
                $configFlag = true;
            }
        } catch (GeneratorException $e) {
            return $this->sendError(
                'No controller exist in the module ' . $moduleName . '.'
            );
        }

        // write message
        if ($configFlag) {
            $this->console->writeLine(
                'The routing has been configured in module "'
                . $moduleName . '".',
                Color::GREEN
            );
        } else {
            $this->console->writeLine(
                'The routing has not been changed in module "'
                . $moduleName . '".',
                Color::YELLOW
            );
        }
    }
}
