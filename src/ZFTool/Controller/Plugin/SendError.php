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
        $this->console->write(' Oops ', Color::NORMAL, Color::RED);
        $this->console->write(' ');

        if (is_array($msg)) {
            foreach ($msg as $msgBlock) {
                $this->console->write(current($msgBlock), key($msgBlock));
            }
        } else {
            $this->console->write($msg);
        }

        $this->console->writeLine();

        $this->getController()->consoleFooter('an error occured', false);
    }
}
