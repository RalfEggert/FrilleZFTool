<?php
namespace ZFTool\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConsoleHeaderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $controllerPluginManager
     *
     * @return mixed
     */
    public function createService(
        ServiceLocatorInterface $controllerPluginManager
    ) {
        $serviceLocator = $controllerPluginManager->getServiceLocator();

        $console = $serviceLocator->get('console');

        $controller = new ConsoleHeader($console);

        return $controller;
    }
}