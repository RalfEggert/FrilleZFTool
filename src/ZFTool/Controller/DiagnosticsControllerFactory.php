<?php
namespace ZFTool\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DiagnosticsControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $controllerLoader
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $controllerLoader)
    {
        $serviceLocator = $controllerLoader->getServiceLocator();

        $console        = $serviceLocator->get('console');
        $requestOptions = $serviceLocator->get('ZFTool\Options\RequestOptions');
        $configuration  = $serviceLocator->get('Configuration');
        $moduleManager  = $serviceLocator->get('ModuleManager');

        $controller = new DiagnosticsController(
            $console, $requestOptions, $configuration, $moduleManager
        );

        return $controller;
    }
}