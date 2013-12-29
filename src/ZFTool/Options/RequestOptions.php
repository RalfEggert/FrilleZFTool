<?php
namespace ZFTool\Options;

use Zend\Filter\StaticFilter;
use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\Exception\InvalidArgumentException;
use Zend\Stdlib\Parameters;

/**
 * Class RequestOptions
 *
 * @package ZFTool\Generator
 */
class RequestOptions extends AbstractOptions
{
    /**
     * Method for action
     *
     * @var string
     */
    protected $actionMethod;
    /**
     * Name of action
     *
     * @var string
     */
    protected $actionName;
    /**
     * View file for action
     *
     * @var string
     */
    protected $actionViewFile;
    /**
     * View path for action
     *
     * @var string
     */
    protected $actionViewPath;
    /**
     * Config name
     *
     * @var string
     */
    protected $configName;
    /**
     * Config value
     *
     * @var string
     */
    protected $configValue;
    /**
     * Class name of controller
     *
     * @var string
     */
    protected $controllerClass;
    /**
     * File name of controller
     *
     * @var string
     */
    protected $controllerFile;
    /**
     * Configuration key of controller
     *
     * @var string
     */
    protected $controllerKey;
    /**
     * Name of controller
     *
     * @var string
     */
    protected $controllerName;
    /**
     * Path to controller
     *
     * @var string
     */
    protected $controllerPath;
    /**
     * View directory of controller
     *
     * @var string
     */
    protected $controllerViewPath;
    /**
     * Destination file
     *
     * @var string
     */
    protected $destination;
    /**
     * Directory to work in
     *
     * @var string
     */
    protected $directory;
    /**
     * Flag for break mode
     *
     * @var boolean
     */
    protected $flagBreak;
    /**
     * Flag for debug mode
     *
     * @var boolean
     */
    protected $flagDebug;
    /**
     * Flag for help mode
     *
     * @var boolean
     */
    protected $flagHelp;
    /**
     * Flag to ignore coding standard conventions
     *
     * @var boolean
     */
    protected $flagIgnoreConventions;
    /**
     * Flag to local
     *
     * @var boolean
     */
    protected $flagLocal;
    /**
     * Flag to create no doc blocks
     *
     * @var boolean
     */
    protected $flagNoApiDocs;
    /**
     * Flag to create no configuration
     *
     * @var boolean
     */
    protected $flagNoConfig;
    /**
     * Flag for quiet mode
     *
     * @var boolean
     */
    protected $flagQuiet;
    /**
     * Flag to create a single route for a module
     *
     * @var boolean
     */
    protected $flagSingleRoute;
    /**
     * Flag for verbose mode
     *
     * @var boolean
     */
    protected $flagVerbose;
    /**
     * Flag to create a factory for the class
     *
     * @var boolean
     */
    protected $flagWithFactory;
    /**
     * Name of module
     *
     * @var string
     */
    protected $moduleName;
    /**
     * Path to module
     *
     * @var string
     */
    protected $modulePath;
    /**
     * Base route for module
     *
     * @var string
     */
    protected $moduleRoute;
    /**
     * View directory of module
     *
     * @var string
     */
    protected $moduleViewDir;
    /**
     * Current path to work in
     *
     * @var string
     */
    protected $path;
    /**
     * Test group name for diagnostics
     *
     * @var string
     */
    protected $testGroupName;
    /**
     * Current temporary directory
     *
     * @var string
     */
    protected $tmpDir;
    /**
     * ZF2 version
     *
     * @var string
     */
    protected $version;
    /**
     * Class name of view helper
     *
     * @var string
     */
    protected $viewHelperClass;
    /**
     * File name of view helper
     *
     * @var string
     */
    protected $viewHelperFile;
    /**
     * Configuration key of view helper
     *
     * @var string
     */
    protected $viewHelperKey;
    /**
     * Name of view helper
     *
     * @var string
     */
    protected $viewHelperName;
    /**
     * Path to view helper
     *
     * @var string
     */
    protected $viewHelperPath;

    /**
     * @return string
     */
    public function getActionMethod()
    {
        return $this->actionMethod;
    }

    /**
     * @param string $actionMethod
     */
    public function setActionMethod($actionMethod)
    {
        $this->actionMethod = $actionMethod;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param string $actionName
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * @return string
     */
    public function getActionViewFile()
    {
        return $this->actionViewFile;
    }

    /**
     * @param string $actionViewFile
     */
    public function setActionViewFile($actionViewFile)
    {
        $this->actionViewFile = $actionViewFile;
    }

    /**
     * @return string
     */
    public function getActionViewPath()
    {
        return $this->actionViewPath;
    }

    /**
     * @param string $actionViewPath
     */
    public function setActionViewPath($actionViewPath)
    {
        $this->actionViewPath = $actionViewPath;
    }

    /**
     * @return string
     */
    public function getConfigName()
    {
        return $this->configName;
    }

    /**
     * @param string $configName
     */
    public function setConfigName($configName)
    {
        $this->configName = $configName;
    }

    /**
     * @return string
     */
    public function getConfigValue()
    {
        return $this->configValue;
    }

    /**
     * @param string $configValue
     */
    public function setConfigValue($configValue)
    {
        $this->configValue = $configValue;
    }

    /**
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * @param string $controllerClass
     */
    public function setControllerClass($controllerClass)
    {
        $this->controllerClass = $controllerClass;
    }

    /**
     * @return string
     */
    public function getControllerFile()
    {
        return $this->controllerFile;
    }

    /**
     * @param string $controllerFile
     */
    public function setControllerFile($controllerFile)
    {
        $this->controllerFile = $controllerFile;
    }

    /**
     * @return string
     */
    public function getControllerKey()
    {
        return $this->controllerKey;
    }

    /**
     * @param string $controllerKey
     */
    public function setControllerKey($controllerKey)
    {
        $this->controllerKey = $controllerKey;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @param string $controllerName
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
    }

    /**
     * @return string
     */
    public function getControllerPath()
    {
        return $this->controllerPath;
    }

    /**
     * @param string $controllerPath
     */
    public function setControllerPath($controllerPath)
    {
        $this->controllerPath = $controllerPath;
    }

    /**
     * @return string
     */
    public function getControllerViewPath()
    {
        return $this->controllerViewPath;
    }

    /**
     * @param string $controllerViewPath
     */
    public function setControllerViewPath($controllerViewPath)
    {
        $this->controllerViewPath = $controllerViewPath;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param string $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return boolean
     */
    public function getFlagBreak()
    {
        return $this->flagBreak;
    }

    /**
     * @param boolean $flagBreak
     */
    public function setFlagBreak($flagBreak)
    {
        $this->flagBreak = (boolean)$flagBreak;
    }

    /**
     * @return boolean
     */
    public function getFlagDebug()
    {
        return $this->flagDebug;
    }

    /**
     * @param boolean $flagDebug
     */
    public function setFlagDebug($flagDebug)
    {
        $this->flagDebug = (boolean)$flagDebug;
    }

    /**
     * @return boolean
     */
    public function getFlagHelp()
    {
        return $this->flagHelp;
    }

    /**
     * @param boolean $flagHelp
     */
    public function setFlagHelp($flagHelp)
    {
        $this->flagHelp = $flagHelp;
    }

    /**
     * @return boolean
     */
    public function getFlagIgnoreConventions()
    {
        return $this->flagIgnoreConventions;
    }

    /**
     * @param boolean $flagIgnoreConventions
     */
    public function setFlagIgnoreConventions($flagIgnoreConventions)
    {
        $this->flagIgnoreConventions = (boolean)$flagIgnoreConventions;
    }

    /**
     * @return boolean
     */
    public function getFlagLocal()
    {
        return $this->flagLocal;
    }

    /**
     * @param boolean $flagLocal
     */
    public function setFlagLocal($flagLocal)
    {
        $this->flagLocal = (boolean)$flagLocal;
    }

    /**
     * @return boolean
     */
    public function getFlagNoApiDocs()
    {
        return $this->flagNoApiDocs;
    }

    /**
     * @param boolean $flagNoApiDocs
     */
    public function setFlagNoApiDocs($flagNoApiDocs)
    {
        $this->flagNoApiDocs = (boolean)$flagNoApiDocs;
    }

    /**
     * @return boolean
     */
    public function getFlagNoConfig()
    {
        return $this->flagNoConfig;
    }

    /**
     * @param boolean $flagNoConfig
     */
    public function setFlagNoConfig($flagNoConfig)
    {
        $this->flagNoConfig = (boolean)$flagNoConfig;
    }

    /**
     * @return boolean
     */
    public function getFlagQuiet()
    {
        return $this->flagQuiet;
    }

    /**
     * @param boolean $flagQuiet
     */
    public function setFlagQuiet($flagQuiet)
    {
        $this->flagQuiet = (boolean)$flagQuiet;
    }

    /**
     * @return boolean
     */
    public function getFlagSingleRoute()
    {
        return $this->flagSingleRoute;
    }

    /**
     * @param boolean $flagSingleRoute
     */
    public function setFlagSingleRoute($flagSingleRoute)
    {
        $this->flagSingleRoute = (boolean)$flagSingleRoute;
    }

    /**
     * @return boolean
     */
    public function getFlagVerbose()
    {
        return $this->flagVerbose;
    }

    /**
     * @param boolean $flagVerbose
     */
    public function setFlagVerbose($flagVerbose)
    {
        $this->flagVerbose = (boolean)$flagVerbose;
    }

    /**
     * @return boolean
     */
    public function getFlagWithFactory()
    {
        return $this->flagWithFactory;
    }

    /**
     * @param boolean $flagWithFactory
     */
    public function setFlagWithFactory($flagWithFactory)
    {
        $this->flagWithFactory = (boolean)$flagWithFactory;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @param string $moduleName
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * @return string
     */
    public function getModulePath()
    {
        return $this->modulePath;
    }

    /**
     * @param string $modulePath
     */
    public function setModulePath($modulePath)
    {
        $this->modulePath = $modulePath;
    }

    /**
     * @return string
     */
    public function getModuleRoute()
    {
        return $this->moduleRoute;
    }

    /**
     * @param string $moduleRoute
     */
    public function setModuleRoute($moduleRoute)
    {
        $this->moduleRoute = $moduleRoute;
    }

    /**
     * @return string
     */
    public function getModuleViewDir()
    {
        return $this->moduleViewDir;
    }

    /**
     * @param string $moduleViewDir
     */
    public function setModuleViewDir($moduleViewDir)
    {
        $this->moduleViewDir = $moduleViewDir;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getTestGroupName()
    {
        return $this->testGroupName;
    }

    /**
     * @param string $testGroupName
     */
    public function setTestGroupName($testGroupName)
    {
        $this->testGroupName = $testGroupName;
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        return $this->tmpDir;
    }

    /**
     * @param string $tmpDir
     */
    public function setTmpDir($tmpDir)
    {
        $this->tmpDir = $tmpDir;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getViewHelperClass()
    {
        return $this->viewHelperClass;
    }

    /**
     * @param string $viewHelperClass
     */
    public function setViewHelperClass($viewHelperClass)
    {
        $this->viewHelperClass = $viewHelperClass;
    }

    /**
     * @return string
     */
    public function getViewHelperFile()
    {
        return $this->viewHelperFile;
    }

    /**
     * @param string $viewHelperFile
     */
    public function setViewHelperFile($viewHelperFile)
    {
        $this->viewHelperFile = $viewHelperFile;
    }

    /**
     * @return string
     */
    public function getViewHelperKey()
    {
        return $this->viewHelperKey;
    }

    /**
     * @param string $viewHelperKey
     */
    public function setViewHelperKey($viewHelperKey)
    {
        $this->viewHelperKey = $viewHelperKey;
    }

    /**
     * @return string
     */
    public function getViewHelperName()
    {
        return $this->viewHelperName;
    }

    /**
     * @param string $viewHelperName
     */
    public function setViewHelperName($viewHelperName)
    {
        $this->viewHelperName = $viewHelperName;
    }

    /**
     * @return string
     */
    public function getViewHelperPath()
    {
        return $this->viewHelperPath;
    }

    /**
     * @param string $viewHelperPath
     */
    public function setViewHelperPath($viewHelperPath)
    {
        $this->viewHelperPath = $viewHelperPath;
    }

    /**
     * Set options from request parameters
     *
     * @param  Parameters $parameters
     *
     * @throws Exception\InvalidArgumentException
     * @return RequestOptions Provides fluent interface
     */
    public function setFromRequest(Parameters $parameters)
    {
        if (!$parameters instanceof Parameters) {
            throw new InvalidArgumentException(sprintf(
                'Parameter provided to %s must be an %s',
                __METHOD__, 'Zend\Stdlib\Parameters'
            ));
        }

        // set path
        $path = rtrim($parameters['path'], '/');

        if (empty($path)) {
            $path = '.';
        }

        // initialize requestParams
        $this->setTmpDir(sys_get_temp_dir());
        $this->setPath($path);

        // set params
        $this->setFlagWithFactory($parameters['factory']);
        $this->setFlagIgnoreConventions($parameters['ignore']);
        $this->setFlagNoConfig($parameters['config']);
        $this->setFlagNoApiDocs($parameters['apidocs']);
        $this->setFlagSingleRoute($parameters['single']);
        $this->setFlagLocal($parameters['local']);
        $this->setFlagDebug($parameters['debug']);
        $this->setFlagVerbose($parameters['verbose']);
        $this->setFlagQuiet($parameters['quiet']);
        $this->setFlagBreak($parameters['break']);
        $this->setFlagHelp($parameters['help']);

        // correct quiet mode of debug or verbose is set
        if (($this->getFlagDebug() || $this->getFlagVerbose())
            && $this->getFlagQuiet()
        ) {
            $this->setFlagQuiet(false);
        }

        // check for module_name param
        if ($parameters['module_name']) {
            $moduleName = $parameters['module_name'];

            if (!$this->getFlagIgnoreConventions()) {
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
            $moduleViewDir = StaticFilter::execute(
                $moduleViewDir, 'StringToLower'
            );

            // setup module route
            $moduleRoute = '/' . $moduleName;
            $moduleRoute = StaticFilter::execute(
                $moduleRoute, 'Word\CamelCaseToDash'
            );
            $moduleRoute = StaticFilter::execute($moduleRoute, 'StringToLower');

            // set params
            $this->setModuleName($moduleName);
            $this->setModulePath($modulePath);
            $this->setModuleViewDir($moduleViewDir);
            $this->setModuleRoute($moduleRoute);
        } else {
            $moduleName    = '';
            $modulePath    = '';
            $moduleViewDir = '';
        }

        // check for controller_name param
        if ($parameters['controller_name']) {
            $controllerName = $parameters['controller_name'];

            if (!$this->getFlagIgnoreConventions()) {
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
            $controllerPath = $modulePath . '/src/'
                . $moduleName . '/Controller/';

            // set controller class
            $controllerClass = $controllerName . 'Controller';

            // set controller identifier
            $controllerKey = $moduleName . '\Controller\\' . $controllerName;

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
            $this->setControllerName($controllerName);
            $this->setControllerPath($controllerPath);
            $this->setControllerClass($controllerClass);
            $this->setControllerKey($controllerKey);
            $this->setControllerFile($controllerFile);
            $this->setControllerViewPath($controllerViewPath);
            $this->setActionName($actionName);
            $this->setActionMethod($actionMethod);
            $this->setActionViewFile($actionViewFile);
            $this->setActionViewPath($actionViewPath);
        } else {
            $controllerViewPath = '';
        }

        // check for action_name param
        if ($parameters['action_name']) {
            $actionName = $parameters['action_name'];

            if (!$this->getFlagIgnoreConventions()) {
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
            $this->setActionName($actionName);
            $this->setActionMethod($actionMethod);
            $this->setActionViewFile($actionViewFile);
            $this->setActionViewPath($actionViewPath);
        }

        // check for directory param
        if ($parameters['directory']) {
            $directory = realpath($parameters['directory']);

            // set param
            $this->setDirectory($directory);
        } else {
            $directory = '';
        }

        // check for destination param
        if ($parameters['destination']) {
            $destination = $parameters['destination'];

        } else {
            // set default if destination not set
            $destination = $directory . '/autoload_classmap.php';
        }

        // set param
        $this->setDestination($destination);

        // check for config_name param
        if ($parameters['config_name']) {
            $configName = $parameters['config_name'];

            // set param
            $this->setConfigName($configName);
        }

        // check for config_value param
        if ($parameters['config_value']) {
            $configValue = $parameters['config_value'];

            // set param
            $this->setConfigValue($configValue);
        }

        // check for version param
        if ($parameters['version']) {
            $version = $parameters['version'];

            // set param
            $this->setVersion($version);
        }

        // check for testGroupName param
        if ($parameters['test_group_name']) {
            $testGroupName = $parameters['test_group_name'];

            // set param
            $this->setTestGroupName($testGroupName);
        }

        // check for helper_name param
        if ($parameters['helper_name']) {
            $viewHelperName = $parameters['helper_name'];

            if (!$this->getFlagIgnoreConventions()) {
                $viewHelperName = StaticFilter::execute(
                    $viewHelperName, 'Word\UnderscoreToCamelCase'
                );
                $viewHelperName = StaticFilter::execute(
                    $viewHelperName, 'Word\DashToCamelCase'
                );
            } else {
                $viewHelperName = StaticFilter::execute(
                    $viewHelperName, 'Word\DashToUnderscore'
                );
            }

            // set controller path
            $viewHelperPath = $modulePath . '/src/'
                . $moduleName . '/View/Helper/';

            // set controller class
            $viewHelperClass = $viewHelperName;

            // set controller identifier
            $viewHelperKey = $moduleName . '\View\Helper\\' . $viewHelperName;

            // set controller file
            $viewHelperFile = $viewHelperClass . '.php';

            // set params
            $this->setViewHelperName($viewHelperName);
            $this->setViewHelperPath($viewHelperPath);
            $this->setViewHelperClass($viewHelperClass);
            $this->setViewHelperKey($viewHelperKey);
            $this->setViewHelperFile($viewHelperFile);
        } else {
            $controllerViewPath = '';
        }

        return $this;
    }
}
