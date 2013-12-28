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

        // check if path exists
        if (file_exists($path)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The directory '),
                    array(Color::RED    => realpath($path)),
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
                        array(Color::RED    => realpath($path)),
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
     * Create a controller
     *
     * @return ConsoleModel
     */
    public function controllerAction()
    {
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
                    array(Color::RED    => realpath($path)),
                    array(Color::NORMAL => ' doesn\'t contain a ZF2 application.'),
                )
            );
        }

        // check if controller exists already in module
        if (file_exists($controllerPath . $controllerFile)) {
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

        // check for factory flag
        if ($flagWithFactory) {
            // create controller factory class
            $factoryFlag = $this->moduleGenerator->createControllerFactory();

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
     * Create a controller factory
     *
     * @return ConsoleModel
     */
    public function controllerFactoryAction()
    {
        // output header
        $this->consoleHeader('Creating new controller factory');

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
                array(
                    array(Color::NORMAL => 'The path '),
                    array(Color::RED    => realpath($path)),
                    array(Color::NORMAL => ' doesn\'t contain a ZF2 application.'),
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

        // write start message
        $this->console->write('       => Creating controller factory for ');
        $this->console->write ($controllerName, Color::GREEN);
        $this->console->write(' in module ');
        $this->console->writeLine($moduleName, Color::GREEN);

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
        $this->console->write('       => Updating controller configuration for ');
        $this->console->writeLine($moduleName, Color::GREEN);

        // add controller factory configuration to module
        $moduleConfig = $this->moduleConfigurator->addControllerFactoryConfig();

        // check for module config updates
        if ($moduleConfig) {
            // update module configuration
            $this->moduleGenerator->updateConfiguration(
                $moduleConfig, $modulePath . '/config/module.config.php'
            );
        }

        $this->console->writeLine();
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');

        // write message
        $this->console->write('The factory for controller ');
        $this->console->write($controllerName, Color::GREEN);
        $this->console->write(' has been created in module ');
        $this->console->writeLine($moduleName, Color::GREEN);

        // output footer
        $this->consoleFooter('controller factory was successfully created');

    }

    /**
     * Create an action method
     *
     * @return ConsoleModel
     */
    public function methodAction()
    {
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
                    array(Color::RED    => realpath($path)),
                    array(Color::NORMAL => ' doesn\'t contain a ZF2 application.'),
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
     * Create a module
     *
     * @return ConsoleModel
     */
    public function moduleAction()
    {
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
                    array(Color::RED    => realpath($path)),
                    array(Color::NORMAL => ' doesn\'t contain a ZF2 application.'),
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
     * Create the routing for a module
     *
     * @return ConsoleModel
     */
    public function routingAction()
    {
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
                    array(Color::RED    => realpath($path)),
                    array(Color::NORMAL => ' doesn\'t contain a ZF2 application.'),
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
}
