<?php
namespace ZFTool\Generator;

use ZFTool\Options\RequestOptions;

/**
 * Class ModuleConfigurator
 *
 * @package ZFTool\Generator
 */
class ModuleConfigurator
{
    /**
     * @var boolean
     */
    protected $flagNoConfig = false;

    /**
     * @var RequestOptions
     */
    protected $requestOptions;

    /**
     * @var array
     */
    protected $ignoreKeys = array(
        'controllers',
        'router',
    );

    /**
     * @param $requestOptions
     */
    function __construct(RequestOptions $requestOptions)
    {
        $this->requestOptions = $requestOptions;

        // change no config flag
        $this->flagNoConfig= $this->requestOptions->getFlagNoConfig();
    }

    /**
     * Add configuration for a new module
     *
     * @return bool|mixed
     */
    public function addModuleConfig()
    {
        // get needed options to shorten code
        $path       = $this->requestOptions->getPath();
        $moduleName = $this->requestOptions->getModuleName();

        // set file name
        $configFile = $path . '/config/application.config.php';

        // read application configuration
        $applicationConfigOld = require $path . '/config/application.config.php';
        $applicationConfigNew = $applicationConfigOld;

        // Add the module in application.config.php
        if (!in_array($moduleName, $applicationConfigNew['modules'])) {
            $applicationConfigNew['modules'][] = $moduleName;
        }

        // set config dir
        $configDir = realpath($path . '/config');

        // reset constant compilation
        $applicationConfigNew = $this->resetConfigDirCompilation(
            $applicationConfigNew, $configDir
        );

        // check for application config updates
        if ($applicationConfigNew !== $applicationConfigOld) {
            return $applicationConfigNew;
        } else {
            return false;
        }
    }

    /**
     * Add configuration for a new controller
     *
     * @return bool|mixed
     */
    public function addControllerConfig()
    {
        // check for no config flag
        if ($this->flagNoConfig) {
            return false;
        }

        // get needed options to shorten code
        $modulePath    = $this->requestOptions->getModulePath();
        $controllerKey = $this->requestOptions->getControllerKey();

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

        // check for controllers factories configuration
        if (!isset($moduleConfigNew['controllers']['factories'])) {
            $moduleConfigNew['controllers']['factories'] = array();
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

        // check for template path stack
        if (!in_array($configDir . '/../view', $moduleConfigNew['view_manager']['template_path_stack'])) {
            $moduleConfigNew['view_manager']['template_path_stack'][]
                = '__DIR__ . \'/../view\'';
        }

        // reset constant compilation
        $moduleConfigNew = $this->resetConfigDirCompilation(
            $moduleConfigNew, $configDir
        );

        // check for module config updates
        if ($moduleConfigNew !== $moduleConfigOld) {
            return $moduleConfigNew;
        } else {
            return false;
        }
    }

    /**
     * Add configuration for a new controller factory
     *
     * @return bool|mixed
     */
    public function addControllerFactoryConfig()
    {
        // check for no config flag
        if ($this->flagNoConfig) {
            return false;
        }

        // get needed options to shorten code
        $modulePath    = $this->requestOptions->getModulePath();
        $controllerKey = $this->requestOptions->getControllerKey();

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

        // check for controllers invokables configuration
        if (!isset($moduleConfigNew['controllers']['factories'])) {
            $moduleConfigNew['controllers']['factories'] = array();
        }

        // check if factory key is already there
        if (!in_array(
            $controllerKey, $moduleConfigNew['controllers']['factories']
        )
        ) {
            $moduleConfigNew['controllers']['factories'][$controllerKey]
                = $controllerKey . 'ControllerFactory';
        }

        // check if invokable key is there
        if (isset($moduleConfigNew['controllers']['invokables'])
            && isset($moduleConfigNew['controllers']['invokables'][$controllerKey])
        ) {
            unset($moduleConfigNew['controllers']['invokables'][$controllerKey]);
        }

        // set config dir
        $configDir = realpath($modulePath . '/config');

        // reset constant compilation
        $moduleConfigNew = $this->resetConfigDirCompilation(
            $moduleConfigNew, $configDir
        );

        // check for module config updates
        if ($moduleConfigNew !== $moduleConfigOld) {
            return $moduleConfigNew;
        } else {
            return false;
        }
    }

    /**
     * Add configuration for the routing of a module
     *
     * @return bool|mixed
     */
    public function addRouterConfig()
    {
        // get needed options to shorten code
        $flagSingleRoute = $this->requestOptions->getFlagSingleRoute();
        $moduleName      = $this->requestOptions->getModuleName();
        $modulePath      = $this->requestOptions->getModulePath();
        $moduleRoute     = $this->requestOptions->getModuleRoute();

        // Read module configuration
        $moduleConfigOld = require $modulePath . '/config/module.config.php';
        $moduleConfigNew = $moduleConfigOld;

        // check if controller exists
        if (!isset($moduleConfigNew['controllers'])
            || count($moduleConfigNew['controllers']) == 0
        ) {
            throw new GeneratorException(
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

        // set config dir
        $configDir = realpath($modulePath . '/config');

        // reset constant compilation
        $moduleConfigNew = $this->resetConfigDirCompilation(
            $moduleConfigNew, $configDir
        );

        // check for module config updates
        if ($moduleConfigNew !== $moduleConfigOld) {
            return $moduleConfigNew;
        } else {
            return false;
        }
    }

    /**
     * @param array  $configData
     * @param string $configDir
     * @param int    $level
     *
     * @return array
     */
    protected function resetConfigDirCompilation(array $configData, $configDir, $level = 1)
    {
        // loop config data
        foreach ($configData as $key => $value) {
            // ignore configuration keys on first level
            if ($level == 1 && in_array($key, $this->ignoreKeys)) {
                continue;
            }

            // check for array
            if (is_array($value)) {
                $configData[$key] = $this->resetConfigDirCompilation(
                    $value, $configDir, $level + 1
                ) ;
            } else {
                if (strpos($value, $configDir) === 0) {
                    $configData[$key] = '__DIR__ . \''
                        . str_replace($configDir, '', $value) . '\'';
                }
            }
        }

        return $configData;
    }
}
