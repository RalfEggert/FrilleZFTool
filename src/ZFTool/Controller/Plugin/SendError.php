<?php

namespace ZFTool\Controller\Plugin;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ConsoleModel;

/**
 * Class ClassmapController
 *
 * @package ZFTool\Controller
 */
class SendError extends AbstractPlugin
{
    /**
     * @var AdapterInterface
     */
    protected $console;

    /**
     * @param AdapterInterface $console
     */
    function __construct(AdapterInterface $console)
    {
        // setup dependencies
        $this->console = $console;
    }

    /**
     * Send an error message to the console
     *
     * @param  string $msg
     * @return ConsoleModel
     */
    public function __invoke($msg)
    {
        $this->console->writeLine($msg, Color::RED);

        $m = new ConsoleModel();
        $m->setErrorLevel(2);
        $m->setResult('---> aborted' . PHP_EOL);
        return $m;
    }
}
