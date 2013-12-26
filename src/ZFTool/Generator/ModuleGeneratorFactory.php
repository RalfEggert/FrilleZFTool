<?php
namespace ZFTool\Generator;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Parameters;

class ModuleGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $requestOptions = $serviceLocator->get('ZFTool\Options\RequestOptions');

        $configurator = new ModuleGenerator($requestOptions);

        return $configurator;
    }

} 