<?php
return array(
    'ZFTool' => array(
        'disable_usage' => false,    // set to true to disable showing available ZFTool commands in Console.
    ),

    // -----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=

    'controllers' => array(
        'factories' => array(
            'ZFTool\Controller\Classmap'    => 'ZFTool\Controller\ClassmapControllerFactory',
            'ZFTool\Controller\Config'      => 'ZFTool\Controller\ConfigControllerFactory',
            'ZFTool\Controller\Create'      => 'ZFTool\Controller\CreateControllerFactory',
            'ZFTool\Controller\Diagnostics' => 'ZFTool\Controller\DiagnosticsControllerFactory',
            'ZFTool\Controller\Info'        => 'ZFTool\Controller\InfoControllerFactory',
            'ZFTool\Controller\Install'     => 'ZFTool\Controller\InstallControllerFactory',
        ),
    ),

    'controller_plugins' => array(
        'factories' => array(
            'sendError'     => 'ZFTool\Controller\Plugin\SendErrorFactory',
            'consoleHeader' => 'ZFTool\Controller\Plugin\ConsoleHeaderFactory',
            'consoleFooter' => 'ZFTool\Controller\Plugin\ConsoleFooterFactory',
        ),
    ),

    'service_manager' => array(
        'factories' => array(
            'ZFTool\Options\RequestOptions'       => 'ZFTool\Options\RequestOptionsFactory',
            'ZFTool\Generator\ModuleConfigurator' => 'ZFTool\Generator\ModuleConfiguratorFactory',
            'ZFTool\Generator\ModuleGenerator'    => 'ZFTool\Generator\ModuleGeneratorFactory',
        ),
    ),

    'view_manager' => array(
        'template_map' => array(
            'zf-tool/diagnostics/run' => __DIR__ . '/../view/diagnostics/run.phtml',
        )
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'zftool-version' => array(
                    'options' => array(
                        'route'    => 'version [<path>]',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'version',
                        ),
                    ),
                ),
                'zftool-version2' => array(
                    'options' => array(
                        'route'    => '--version [<path>]',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'version',
                        ),
                    ),
                ),
                'zftool-config-set' => array(
                    'options' => array(
                        'route'    => 'config set <configName> [<configvalue>] [<path>] [--local|-l]:local',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Config',
                            'action'     => 'set',
                        ),
                    ),
                ),
                'zftool-config-get' => array(
                    'options' => array(
                        'route'    => 'config get <configName> [<path>] [--local|-l]:local',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Config',
                            'action'     => 'get',
                        ),
                    ),
                ),
                'zftool-config-list' => array(
                    'options' => array(
                        'route'    => 'config list [<path>] [--local|-l]:local',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Config',
                            'action'     => 'list',
                        ),
                    ),
                ),
                'zftool-classmap-generate' => array(
                    'options' => array(
                        'route'    => 'classmap generate [<directory>] [<destination>]',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Classmap',
                            'action'     => 'generate',
                        ),
                    ),
                ),
                'zftool-modules-list' => array(
                    'options' => array(
                        'route'    => 'modules [<path>]',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'modules',
                        ),
                    ),
                ),
                'zftool-controllers-list' => array(
                    'options' => array(
                        'route'    => 'controllers <moduleName> [<path>]',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'controllers',
                        ),
                    ),
                ),
                'zftool-actions-list' => array(
                    'options' => array(
                        'route'    => 'actions <controllerName> <moduleName> [<path>]',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'actions',
                        ),
                    ),
                ),
                'zftool-create-project' => array(
                    'options' => array(
                        'route'    => 'create project <path>',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'project',
                        ),
                    ),
                ),
                'zftool-create-module' => array(
                    'options' => array(
                        'route'    => 'create module <moduleName> [<path>] [--ignore-conventions|-i]:ignoreConventions [--no-docblocks|-d]:noDocblocks',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'module',
                        ),
                    ),
                ),
                'zftool-create-controller' => array(
                    'options' => array(
                        'route'    => 'create controller <controllerName> <moduleName> [<path>] [--factory|-f]:factory [--ignore-conventions|-i]:ignoreConventions [--no-config|-n]:noConfig [--no-docblocks|-d]:noDocblocks',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'controller',
                        ),
                    ),
                ),
                'zftool-create-action' => array(
                    'options' => array(
                        'route'    => 'create action <actionName> <controllerName> <moduleName> [<path>] [--ignore-conventions|-i]:ignoreConventions [--no-docblocks|-d]:noDocblocks',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'method',
                        ),
                    ),
                ),
                'zftool-create-routing' => array(
                    'options' => array(
                        'route'    => 'create routing <moduleName> [<path>] [--single-route|-s]:singleRoute',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'routing',
                        ),
                    ),
                ),
                'zftool-create-controller-factory' => array(
                    'options' => array(
                        'route'    => 'create controller-factory <controllerName> <moduleName> [<path>] [--no-config|-n]:noConfig [--no-docblocks|-d]:noDocblocks',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'controller-factory',
                        ),
                    ),
                ),
                'zftool-install-zf' => array(
                    'options' => array(
                        'route'    => 'install zf <path> [<version>]',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Install',
                            'action'     => 'zf',
                        ),
                    ),
                ),
                'zftool-diagnostics' => array(
                    'options' => array(
                        'route'    => '(diagnostics|diag) [-v|--verbose]:verbose [-d|--debug]:debug [-q|--quiet]:quiet [-b|--break]:break [<testGroupName>]',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Diagnostics',
                            'action'     => 'run',
                        ),
                    ),
                ),
            ),
        ),
    ),

    'diagnostics' => array(
        'ZF' => array(
            'PHP Version' => array('ZFTool\Diagnostics\Test\PhpVersion', '5.3.3'),
        )
    ),
);
