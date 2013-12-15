<?php

namespace ZFTool\Controller;

use Zend\Code\Generator\Exception\RuntimeException as GeneratorException;
use Zend\Code\Generator;
use Zend\Code\Reflection;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Filter\StaticFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;
use Zend\View\Model\ConsoleModel;
use ZFTool\Generator\ModuleGenerator;
use ZFTool\Model\Skeleton;
use ZFTool\Model\Utility;

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
     * @var Parameters
     */
    protected $requestParams;

    /**
     * @param AdapterInterface $console
     * @param ModuleGenerator  $moduleGenerator
     */
    function __construct(
        AdapterInterface $console, ModuleGenerator $moduleGenerator
    ) {
        $this->moduleGenerator = $moduleGenerator;
        $this->console         = $console;
    }

    /**
     * Setup request params
     */
    protected function setupParams()
    {
        // get request
        $request = $this->getRequest();

        // set path
        $path = rtrim($request->getParam('path'), '/');

        if (empty($path)) {
            $path = '.';
        }

        // initialize requestParams
        $this->requestParams = new Parameters(
            array(
                'tmpDir' => sys_get_temp_dir(),
                'path'   => $path,
            )
        );

        // ignore conventions
        $ignoreConventions = $request->getParam('ignore-conventions', false)
            || $request->getParam('i', false);

        // no config writing
        $noConfig = $request->getParam('no-config', false)
            || $request->getParam('n', false);

        // check for moduleName param
        if ($request->getParam('moduleName')) {
            $moduleName = $request->getParam('moduleName');

            if (!$ignoreConventions) {
                $moduleName = StaticFilter::execute(
                    $moduleName, 'Word\UnderscoreToCamelCase'
                );
                $moduleName = StaticFilter::execute(
                    $moduleName, 'Word\DashToCamelCase'
                );
            } else {
                $moduleName = StaticFilter::execute(
                    $moduleName, 'Word\DashToUnderscore'
                );
            }

            // set path for new module
            $modulePath = $path . '/module/' . $moduleName;

            // setup module view dir
            $moduleViewDir = StaticFilter::execute(
                $moduleName, 'Word\CamelCaseToDash'
            );
            $moduleViewDir = StaticFilter::execute($moduleViewDir, 'StringToLower');

            // set params
            $this->requestParams->set('moduleName', $moduleName);
            $this->requestParams->set('modulePath', $modulePath);
            $this->requestParams->set('moduleViewDir', $moduleViewDir);
        }

        // check for controllerName param
        if ($request->getParam('controllerName')) {
            $controllerName = $request->getParam('controllerName');

            if (!$ignoreConventions) {
                $controllerName = StaticFilter::execute(
                    $controllerName, 'Word\UnderscoreToCamelCase'
                );
                $controllerName = StaticFilter::execute(
                    $controllerName, 'Word\DashToCamelCase'
                );
            } else {
                $controllerName = StaticFilter::execute(
                    $controllerName, 'Word\DashToUnderscore'
                );
            }

            // set controller path
            $controllerPath  = $modulePath . '/src/'
                . $moduleName . '/Controller/';

            // set controller class
            $controllerClass = $controllerName . 'Controller';

            // set controller identifier
            $controllerKey = $moduleName . '\Controller\\' . $controllerClass;

            // set controller file
            $controllerFile = $controllerClass . '.php';

            // setup controller view dir
            $controllerViewDir = StaticFilter::execute(
                $controllerName, 'Word\CamelCaseToDash'
            );
            $controllerViewDir = StaticFilter::execute(
                $controllerViewDir, 'StringToLower'
            );

            // set controller view path
            $controllerViewPath = $modulePath . '/view/' . $moduleViewDir . '/'
                . $controllerViewDir;

            // set action name
            $actionName = 'Index';

            // set action method
            $actionMethod = lcfirst($actionName) . 'Action';

            // setup action view file
            $actionViewFile = StaticFilter::execute(
                $actionName . '.phtml', 'Word\CamelCaseToDash'
            );
            $actionViewFile = StaticFilter::execute(
                $actionViewFile, 'StringToLower'
            );

            // set action view path
            $actionViewPath = $controllerViewPath . '/' . $actionViewFile;

            // set params
            $this->requestParams->set('controllerName', $controllerName);
            $this->requestParams->set('controllerPath', $controllerPath);
            $this->requestParams->set('controllerClass', $controllerClass);
            $this->requestParams->set('controllerKey', $controllerKey);
            $this->requestParams->set('controllerFile', $controllerFile);
            $this->requestParams->set('controllerViewPath', $controllerViewPath);
            $this->requestParams->set('actionName', $actionName);
            $this->requestParams->set('actionMethod', $actionMethod);
            $this->requestParams->set('actionViewFile', $actionViewFile);
            $this->requestParams->set('actionViewPath', $actionViewPath);
        }

        // check for actionName param
        if ($request->getParam('actionName')) {
            $actionName = $request->getParam('actionName');

            if (!$ignoreConventions) {
                $actionName = StaticFilter::execute(
                    $actionName, 'Word\UnderScoreToDash'
                );
                $actionName = StaticFilter::execute(
                    $actionName, 'Word\DashToCamelCase'
                );
            } else {
                $actionName = StaticFilter::execute(
                    $actionName, 'Word\DashToUnderscore'
                );
            }

            // set action method
            $actionMethod = lcfirst($actionName) . 'Action';

            // setup action view file
            $actionViewFile = StaticFilter::execute(
                $actionName . '.phtml', 'Word\CamelCaseToDash'
            );
            $actionViewFile = StaticFilter::execute(
                $actionViewFile, 'StringToLower'
            );

            // set action view path
            $actionViewPath = $controllerViewPath . '/' . $actionViewFile;

            // set params
            $this->requestParams->set('actionName', $actionName);
            $this->requestParams->set('actionMethod', $actionMethod);
            $this->requestParams->set('actionViewFile', $actionViewFile);
            $this->requestParams->set('actionViewPath', $actionViewPath);
        }

        // no config writing
        $noConfig = $request->getParam('no-config', false)
            || $request->getParam('n', false);

        // set param
        $this->requestParams->set('noConfig', $noConfig);
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

        // setup params
        $this->setupParams();

        // get needed params
        $path   = $this->requestParams->get('path');
        $tmpDir = $this->requestParams->get('tmpDir');

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
        // setup params
        $this->setupParams();

        // get needed params
        $path               = $this->requestParams->get('path');
        $noConfig           = $this->requestParams->get('noConfig');
        $moduleName         = $this->requestParams->get('moduleName');
        $modulePath         = $this->requestParams->get('modulePath');
        $controllerName     = $this->requestParams->get('controllerName');
        $controllerPath     = $this->requestParams->get('controllerPath');
        $controllerKey      = $this->requestParams->get('controllerKey');
        $controllerClass    = $this->requestParams->get('controllerClass');
        $controllerFile     = $this->requestParams->get('controllerFile');
        $controllerViewPath = $this->requestParams->get('controllerViewPath');
        $actionName         = $this->requestParams->get('actionName');
        $actionMethod       = $this->requestParams->get('actionMethod');
        $actionViewPath     = $this->requestParams->get('actionViewPath');
        $actionViewFile     = $this->requestParams->get('actionViewFile');

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
        if (!$noConfig) {
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
                    = $controllerKey;
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
                // loop through path stack and add path again due to constant resolution problems
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
        // setup params
        $this->setupParams();

        // get needed params
        $path               = $this->requestParams->get('path');
        $moduleName         = $this->requestParams->get('moduleName');
        $controllerName     = $this->requestParams->get('controllerName');
        $controllerPath     = $this->requestParams->get('controllerPath');
        $controllerClass    = $this->requestParams->get('controllerClass');
        $controllerKey      = $this->requestParams->get('controllerKey');
        $controllerFile     = $this->requestParams->get('controllerFile');
        $controllerViewPath = $this->requestParams->get('controllerViewPath');
        $actionName         = $this->requestParams->get('actionName');
        $actionMethod       = $this->requestParams->get('actionMethod');
        $actionViewPath     = $this->requestParams->get('actionViewPath');
        $actionViewFile     = $this->requestParams->get('actionViewFile');

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
        // setup params
        $this->setupParams();

        // get needed params
        $path          = $this->requestParams->get('path');
        $moduleName    = $this->requestParams->get('moduleName');
        $modulePath    = $this->requestParams->get('modulePath');
        $moduleViewDir = $this->requestParams->get('moduleViewDir');

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
        file_put_contents(
            $modulePath . '/Module.php',
            ModuleGenerator::getModule($moduleName)
        );

        // Create the module.config.php
        file_put_contents(
            $modulePath . '/config/module.config.php',
            ModuleGenerator::getModuleConfig($moduleName)
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
