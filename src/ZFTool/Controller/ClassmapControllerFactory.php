<?php
namespace ZFTool\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClassmapControllerFactory implements FactoryInterface
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

        $console            = $serviceLocator->get('console');
        $requestOptions     = $serviceLocator->get(
            'ZFTool\Options\RequestOptions'
        );
        $moduleGenerator    = $serviceLocator->get(
            'ZFTool\Generator\ModuleGenerator'
        );
        $moduleConfigurator = $serviceLocator->get(
            'ZFTool\Generator\ModuleConfigurator'
        );

        $controller = new ClassmapController(
            $console, $requestOptions, $moduleGenerator, $moduleConfigurator
        );

        return $controller;
    }
}