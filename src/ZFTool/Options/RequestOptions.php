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
     * Flag to ignore coding standard conventions
     *
     * @var boolean
     */
    protected $flagIgnoreConventions;

    /**
     * Flag to create no configuration
     *
     * @var boolean
     */
    protected $flagNoConfig;

    /**
     * Flag to create no doc blocks
     *
     * @var boolean
     */
    protected $flagNoDocBlocks;

    /**
     * Flag to create a single route for a module
     *
     * @var boolean
     */
    protected $flagSingleRoute;

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
     * Current temporary directory
     *
     * @var string
     */
    protected $tmpDir;

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
        $this->flagIgnoreConventions = $flagIgnoreConventions;
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
        $this->flagNoConfig = $flagNoConfig;
    }

    /**
     * @return boolean
     */
    public function getFlagNoDocBlocks()
    {
        return $this->flagNoDocBlocks;
    }

    /**
     * @param boolean $flagNoDocBlocks
     */
    public function setFlagNoDocBlocks($flagNoDocBlocks)
    {
        $this->flagNoDocBlocks = $flagNoDocBlocks;
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
        $this->flagSingleRoute = $flagSingleRoute;
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

        // ignore conventions
        $flagIgnoreConventions = $parameters['ignore-conventions']
            || $parameters['i'];

        // no docblock
        $flagNoDocBlocks = $parameters['no-docblocks'] || $parameters['d'];

        // no config writing
        $flagNoConfig = $parameters['no-config'] || $parameters['n'];

        // single route
        $flagSingleRoute = $parameters['single-route'] || $parameters['s'];

        // set params
        $this->setFlagIgnoreConventions($flagIgnoreConventions);
        $this->setFlagNoConfig($flagNoConfig);
        $this->setFlagNoDocBlocks($flagNoDocBlocks);
        $this->setFlagSingleRoute($flagSingleRoute);

        // check for moduleName param
        if ($parameters['moduleName']) {
            $moduleName = $parameters['moduleName'];

            if (!$flagIgnoreConventions) {
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
        }

        // check for controllerName param
        if ($parameters['controllerName']) {
            $controllerName = $parameters['controllerName'];

            if (!$flagIgnoreConventions) {
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
        }

        // check for actionName param
        if ($parameters['actionName']) {
            $actionName = $parameters['actionName'];

            if (!$flagIgnoreConventions) {
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

        return $this;
    }
}
