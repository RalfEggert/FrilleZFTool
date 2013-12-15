<?php
namespace ZFTool\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CreateControllerFactory implements FactoryInterface
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

        $console         = $serviceLocator->get('console');
        $moduleGenerator = $serviceLocator->get('ZFTool\Generator\ModuleGenerator');

        $controller = new CreateController($console, $moduleGenerator);

        return $controller;

    }

} 