<?php
namespace ZFTool\Options;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Parameters;

class RequestOptionsFactory implements FactoryInterface
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
        $mvcEvent   = $serviceLocator->get('application')->getMvcEvent();
        $parameters = new Parameters($mvcEvent->getRouteMatch()->getParams());

        $options = new RequestOptions();
        $options->setFromRequest($parameters);

        return $options;

    }

} 