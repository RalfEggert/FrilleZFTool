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
     * @var ModuleGenerator
     */
    protected $moduleGenerator;

    /**
     * @param AdapterInterface $console
     * @param ModuleGenerator  $moduleGenerator
     */
    function __construct(
        AdapterInterface $console, ModuleGenerator $moduleGenerator
    ) {
        // setup dependencies
        $this->moduleGenerator = $moduleGenerator;
        $this->console         = $console;
    }


    /**
     * Convenience method to support IDE autocompletion
     *
     * @param Parameters $parameters
     *
     * @return RequestOptions
     */
    protected function requestOptions(Parameters $parameters = null)
    {
        return $this->plugin('requestOptions')->__invoke($parameters);
    }

    /**
     * Create a project
     *
     * @return ConsoleModel
     */
    public function projectAction()
    {
        // initialize request options
        $this->requestOptions($this->getRequest()->getParams());

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
        $path   = $this->requestOptions()->getPath();
        $tmpDir = $this->requestOptions()->getTmpDir();

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
        // initialize request options
        $this->requestOptions($this->getRequest()->getParams());

        // get needed options to shorten code
        $path               = $this->requestOptions()->getPath();
        $flagNoConfig       = $this->requestOptions()->getFlagNoConfig();
        $moduleName         = $this->requestOptions()->getModuleName();
        $modulePath         = $this->requestOptions()->getModulePath();
        $controllerName     = $this->requestOptions()->getControllerName();
        $controllerPath     = $this->requestOptions()->getControllerPath();
        $controllerKey      = $this->requestOptions()->getControllerKey();
        $controllerClass    = $this->requestOptions()->getControllerClass();
        $controllerFile     = $this->requestOptions()->getControllerFile();
        $controllerViewPath = $this->requestOptions()->getControllerViewPath();
        $actionName         = $this->requestOptions()->getActionName();
        $actionMethod       = $this->requestOptions()->getActionMethod();
        $actionViewPath     = $this->requestOptions()->getActionViewPath();
        $actionViewFile     = $this->requestOptions()->getActionViewFile();

        // change doc block flag
        $this->moduleGenerator->setCreateDocBlocks(
            false === $this->requestOptions()->getFlagNoDocBlocks() 
        );

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
        $controllerFlag = $this->moduleGenerator->createController(
            $controllerClass, $moduleName, $controllerPath . $controllerFile
        );

        // create dir if not exists
        if (!file_exists($controllerViewPath)) {
            mkdir($controllerViewPath, 0777, true);
        }

        // create view script
        $viewScriptFlag = $this->moduleGenerator->createViewScript(
            $actionName, $controllerName, $moduleName, $actionViewPath
        );

        // check for no configuration writing
        if (!$flagNoConfig) {
            // Read module configuration
            $moduleConfigOld = require $modulePath . '/config/module.config.php';
            $moduleConfigNew = $moduleConfigOld;

            // check for controllers configuration
            if (!isset($moduleConfigNew['controllers'])) {
                $moduleConfigNew['controllers'] = array();
            }

            // check for controllers invokables configuration
            if (!isset($moduleConfigNew['controllers']['invokables'])) {
                $moduleConfigNew['controllers']['invokables'] = array();
            }

            // check if invokable key is already there
            if (!in_array(
                $controllerKey, $moduleConfigNew['controllers']['invokables']
            )
            ) {
                $moduleConfigNew['controllers']['invokables'][$controllerKey]
                    = $controllerKey . 'Controller';
            }

            // check for view_manager
            if (!isset($moduleConfigNew['view_manager'])) {
                $moduleConfigNew['view_manager'] = array();
            }

            // check for template_path_stack
            if (!isset($moduleConfigNew['view_manager']['template_path_stack'])) {
                $moduleConfigNew['view_manager']['template_path_stack'] = array();
            }

            // set config dir
            $configDir = realpath($modulePath . '/config');

            // check for any path
            if (count($moduleConfigNew['view_manager']['template_path_stack'])
                > 0
            ) {
                // loop through path stack and add path again due to
                // constant resolution problems
                foreach (
                    $moduleConfigNew['view_manager']['template_path_stack'] as
                    $pathKey => $pathKey
                ) {
                    if ($configDir . '/../view' == $pathKey) {
                        $moduleConfigNew['view_manager']['template_path_stack'][$pathKey]
                            = '__DIR__ . \'/../view\'';
                    }
                }
            } else {
                $moduleConfigNew['view_manager']['template_path_stack'][]
                    = '__DIR__ . \'/../view\'';
            }

            // check for module config updates
            if ($moduleConfigNew !== $moduleConfigOld) {

                // update module configuration
                $this->moduleGenerator->updateConfiguration(
                    $moduleConfigNew, $modulePath . '/config/module.config.php'
                );

                // success message
                $this->console->writeLine(
                    'Module configuration was updated for module "'
                    . $moduleName . '".',
                    Color::WHITE
                );
            }
        }

        // write message
        if ($controllerFlag && $viewScriptFlag) {
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
     * Create an action method
     *
     * @return ConsoleModel
     */
    public function methodAction()
    {
        // initialize request options
        $this->requestOptions($this->getRequest()->getParams());

        // get needed options to shorten code
        $path               = $this->requestOptions()->getPath();
        $moduleName         = $this->requestOptions()->getModuleName();
        $controllerName     = $this->requestOptions()->getControllerName();
        $controllerPath     = $this->requestOptions()->getControllerPath();
        $controllerClass    = $this->requestOptions()->getControllerClass();
        $controllerKey      = $this->requestOptions()->getControllerKey();
        $controllerFile     = $this->requestOptions()->getControllerFile();
        $controllerViewPath = $this->requestOptions()->getControllerViewPath();
        $actionName         = $this->requestOptions()->getActionName();
        $actionMethod       = $this->requestOptions()->getActionMethod();
        $actionViewPath     = $this->requestOptions()->getActionViewPath();
        $actionViewFile     = $this->requestOptions()->getActionViewFile();

        // change doc block flag
        $this->moduleGenerator->setCreateDocBlocks(
            false === $this->requestOptions()->getFlagNoDocBlocks() 
        );

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
            $controllerFlag = $this->moduleGenerator->updateController(
                $actionMethod, $controllerKey, $moduleName,
                $controllerPath . $controllerFile
            );
        } catch (GeneratorException $e) {
            return $this->sendError(
                'The action "' . $actionName
                . '" already exists in controller "' . $controllerName
                . '" of module "' . $moduleName . '".'
            );
        }

        // create view script
        $viewScriptFlag = $this->moduleGenerator->createViewScript(
            $actionName, $controllerName, $moduleName, $actionViewPath
        );

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
        // initialize request options
        $this->requestOptions($this->getRequest()->getParams());

        // get needed options to shorten code
        $path          = $this->requestOptions()->getPath();
        $moduleName    = $this->requestOptions()->getModuleName();
        $modulePath    = $this->requestOptions()->getModulePath();
        $moduleViewDir = $this->requestOptions()->getModuleViewDir();

        // change doc block flag
        $this->moduleGenerator->setCreateDocBlocks(
            false === $this->requestOptions()->getFlagNoDocBlocks() 
        );

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

        // create dirs
        mkdir($modulePath . '/config', 0777, true);
        mkdir($modulePath . '/src/' . $moduleName . '/Controller', 0777, true);
        mkdir($modulePath . '/view/' . $moduleViewDir, 0777, true);

        // Create the Module.php
        $this->moduleGenerator->createModule(
            $moduleName, $modulePath . '/Module.php'
        );

        // Create the module.config.php
        $this->moduleGenerator->createConfiguration(
            array(),
            $modulePath . '/config/module.config.php'
        );

        // set file name
        $configFile = $path . '/config/application.config.php';

        // read application configuration
        $configData = require $configFile;

        // Add the module in application.config.php
        if (!in_array($moduleName, $configData['modules'])) {
            $configData['modules'][] = $moduleName;

            // update application configuration
            $this->moduleGenerator->updateConfiguration(
                $configData, $configFile
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
        // initialize request options
        $this->requestOptions($this->getRequest()->getParams());

        // get needed options to shorten code
        $path               = $this->requestOptions()->getPath();
        $flagSingleRoute    = $this->requestOptions()->getFlagSingleRoute();
        $moduleName         = $this->requestOptions()->getModuleName();
        $modulePath         = $this->requestOptions()->getModulePath();
        $moduleRoute        = $this->requestOptions()->getModuleRoute();

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

        // Read module configuration
        $moduleConfigOld = require $modulePath . '/config/module.config.php';
        $moduleConfigNew = $moduleConfigOld;

        // check if controller exists
        if (!isset($moduleConfigNew['controllers'])
            || count($moduleConfigNew['controllers']) == 0
        ) {
            return $this->sendError(
                'No controller exist in the module ' . $moduleName . '.'
            );
        }

        // check for router
        if (!isset($moduleConfigNew['router'])) {
            $moduleConfigNew['router'] = array();
        }

        // reset all routes
        $moduleConfigNew['router']['routes'] = array();

        // set controller namespace
        $controllerNamespace = $moduleName . '\Controller';

        // set child routes
        $childRoutes = array();

        // check for single route
        if ($flagSingleRoute) {
            // create child routes
            $childRoutes = array(
                'controller-action' => array(
                    'type'    => 'segment',
                    'options' => array(
                        'route'    => '/:controller[/:action[/:id]]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'id'         => '[0-9_-]*',
                        ),
                    ),
                ),
            );
        } else {
            // set controller keys
            $controllerKeys = array();

            // merge controller keys
            foreach ($moduleConfigNew['controllers'] as $group) {
                $controllerKeys = array_merge(
                    $controllerKeys,
                    array_keys($group)
                );
            }

            // merge controller keys
            foreach ($controllerKeys as $controllerName) {
                // clear leading namespace
                if (stripos($controllerName, $controllerNamespace) === 0) {
                    $controllerName = str_replace(
                        $controllerNamespace . '\\', '', $controllerName
                    );
                }

                // set routing key
                $routingKey = strtolower($controllerName);
                $routingKey = str_replace('controller', '', $routingKey);

                // set controller route
                $controllerRoute = '/' . strtolower($controllerName);

                // create route
                $childRoutes[$routingKey] = array(
                    'type' => 'segment',
                    'options' => array(
                        'route'    => $controllerRoute . '[/:action[/:id]]',
                        'defaults' => array(
                            'controller' => $controllerName,
                        ),
                        'constraints' => array(
                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'id'         => '[0-9_-]*',
                        ),
                    ),
                );
            }
        }

        // set controller keys
        $controllerKeys = array();

        // merge controller keys
        foreach ($moduleConfigNew['controllers'] as $group) {
            $controllerKeys = array_merge(
                $controllerKeys,
                array_keys($group)
            );
        }

        // identify default controller
        if (count($controllerKeys) == 1) {
            $defaultController = reset($controllerKeys);
        } else {
            $indexController = $controllerNamespace . '\Index';
            $moduleController = $controllerNamespace . '\\' . $moduleName;

            if (in_array($indexController, $controllerKeys)) {
                $defaultController = $indexController;
            } elseif (in_array($moduleController, $controllerKeys)) {
                $defaultController = $moduleController;
            } else {
                $defaultController = reset($controllerKeys);
            }
        }

        // clear leading namespace
        if (stripos($defaultController, $controllerNamespace) === 0) {
            $defaultController = str_replace(
                $controllerNamespace . '\\', '', $defaultController
            );
        }

        // set routing key
        $routingKey = strtolower($moduleName);

        // create route
        $moduleConfigNew['router']['routes'] = array(
            $routingKey => array(
                'type' => 'Literal',
                'options' => array(
                    'route'    => $moduleRoute,
                    'defaults' => array(
                        '__NAMESPACE__' => $controllerNamespace,
                        'controller' => $defaultController,
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => $childRoutes,
            )
        );

        // set config flag
        $configFlag = false;

        // check for module config updates
        if ($moduleConfigNew !== $moduleConfigOld) {

            // update module configuration
            $configFlag = $this->moduleGenerator->updateConfiguration(
                $moduleConfigNew, $modulePath . '/config/module.config.php'
            );

            // success message
            $this->console->writeLine(
                'Module configuration was updated for module "'
                . $moduleName . '".',
                Color::WHITE
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

    /**
     * Send an error message to the console
     *
     * @param  string $msg
     * @return ConsoleModel
     */
    protected function sendError($msg)
    {
        $this->console->writeLine($msg, Color::RED);

        $m = new ConsoleModel();
        $m->setErrorLevel(2);
        $m->setResult('---> aborted' . PHP_EOL);
        return $m;
    }
}
