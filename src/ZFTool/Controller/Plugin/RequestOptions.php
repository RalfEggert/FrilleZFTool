<?php
namespace ZFTool\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Stdlib\Parameters;
use ZFTool\Options\RequestOptions as RequestOptionsObject;

class RequestOptions extends AbstractPlugin
{
    /**
     * @var RequestOptionsObject;
     */
    protected $requestOptions = null;

    /**
     * @param $requestOptions
     */
    function __construct()
    {
        $this->requestOptions = new RequestOptionsObject();
    }

    /**
     * Pass back RequestOptionsObject
     *
     * @param Parameters $param
     * @return mixed
     */
    public function __invoke(Parameters $parameters = null)
    {
        if ($parameters !== null) {
            $this->requestOptions->setFromRequest($parameters);
        }

        return $this->requestOptions;
    }
}