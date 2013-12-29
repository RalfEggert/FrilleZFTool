<?php
return array(
    'ZFTool' => array(
        'disable_usage' => false,    // set to true to disable showing available ZFTool commands in Console.
    ),

    // -----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=-----=

    'controllers' => array(
        'factories' => array(
            'ZFTool\Controller\Generate'    => 'ZFTool\Controller\GenerateControllerFactory',
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
                        'route'    => 'version [<path>] [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'version',
                        ),
                    ),
                ),
                'zftool-version2' => array(
                    'options' => array(
                        'route'    => '--version [<path>] [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'version',
                        ),
                    ),
                ),
                'zftool-config-set' => array(
                    'options' => array(
                        'route'    => 'config set [<config_name>] [<config_value>] [<path>] [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Config',
                            'action'     => 'set',
                        ),
                    ),
                ),
                'zftool-config-get' => array(
                    'options' => array(
                        'route'    => 'config get [<config_name>] [<path>] [--local|-l]:local [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Config',
                            'action'     => 'get',
                        ),
                    ),
                ),
                'zftool-config-list' => array(
                    'options' => array(
                        'route'    => 'config list [<path>] [--local|-l]:local [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Config',
                            'action'     => 'list',
                        ),
                    ),
                ),
                'zftool-generate-classmap' => array(
                    'options' => array(
                        'route'    => 'generate classmap [<directory>] [<destination>] [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Generate',
                            'action'     => 'Classmap',
                        ),
                    ),
                ),
                'zftool-modules-list' => array(
                    'options' => array(
                        'route'    => 'modules [<path>] [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'modules',
                        ),
                    ),
                ),
                'zftool-controllers-list' => array(
                    'options' => array(
                        'route'    => 'controllers [<module_name>] [<path>] [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'controllers',
                        ),
                    ),
                ),
                'zftool-actions-list' => array(
                    'options' => array(
                        'route'    => 'actions [<controller_name>] [<module_name>] [<path>] [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Info',
                            'action'     => 'actions',
                        ),
                    ),
                ),
                'zftool-create-project' => array(
                    'options' => array(
                        'route'    => 'create project [<path>] [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'project',
                        ),
                    ),
                ),
                'zftool-create-module' => array(
                    'options' => array(
                        'route'    => 'create module [<module_name>] [<path>] [--ignore|-i]:ignore [--apidocs|-a]:apidocs [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'module',
                        ),
                    ),
                ),
                'zftool-create-controller' => array(
                    'options' => array(
                        'route'    => 'create controller [<controller_name>] [<module_name>] [<path>] [--factory|-f]:factory [--ignore|-i]:ignore [--config|-c]:config [--apidocs|-a]:apidocs [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'controller',
                        ),
                    ),
                ),
                'zftool-create-action' => array(
                    'options' => array(
                        'route'    => 'create action [<action_name>] [<controller_name>] [<module_name>] [<path>] [--ignore|-i]:ignore [--apidocs|-a]:apidocs [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'method',
                        ),
                    ),
                ),
                'zftool-create-routing' => array(
                    'options' => array(
                        'route'    => 'create routing [<module_name>] [<path>] [--single|-s]:single [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'routing',
                        ),
                    ),
                ),
                'zftool-create-view-helper' => array(
                    'options' => array(
                        'route'    => 'create view-helper [<helper_name>] [<module_name>] [<path>] [--factory|-f]:factory [--ignore|-i]:ignore [--config|-c]:config [--apidocs|-a]:apidocs [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Create',
                            'action'     => 'view-helper',
                        ),
                    ),
                ),
                'zftool-install-zf' => array(
                    'options' => array(
                        'route'    => 'install [<path>] [<version>] [--help|-h]:help',
                        'defaults' => array(
                            'controller' => 'ZFTool\Controller\Install',
                            'action'     => 'zf',
                        ),
                    ),
                ),
                'zftool-diagnostics' => array(
                    'options' => array(
                        'route'    => '(diagnostics|diag) [-v|--verbose]:verbose [-d|--debug]:debug [-q|--quiet]:quiet [-b|--break]:break [<testGroupName>] [--help|-h]:help',
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
