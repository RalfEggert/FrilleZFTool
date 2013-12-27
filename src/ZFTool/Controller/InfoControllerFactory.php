<?php
namespace ZFTool\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class InfoControllerFactory implements FactoryInterface
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

        $controller = new InfoController($console, $requestOptions);

        return $controller;
    }
}