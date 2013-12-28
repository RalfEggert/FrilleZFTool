<?php

namespace ZFTool\Controller\Plugin;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ConsoleModel;
use ZFTool\Module;

/**
 * Class ClassmapController
 *
 * @package ZFTool\Controller
 */
class ConsoleFooter extends AbstractPlugin
{
    /**
     * @var AdapterInterface
     */
    protected $console;

    /**
     * @var int
     */
    protected $width = 80;

    /**
     * @param AdapterInterface $console
     */
    function __construct(AdapterInterface $console)
    {
        // setup dependencies
        $this->console = $console;
        $this->width   = $console->getWidth();
    }

    /**
     * Send an error message to the console
     *
     * @param  string $msg
     * @return ConsoleModel
     */
    public function __invoke($msg, $success = true)
    {
        if ($success) {
            $line1    = '  OK  ';
            $line2    = ' (' . $msg . ')';
            $bgColor = Color::GREEN;
        } else {
            $line1    = ' FAIL ';
            $line2    = ' (' . $msg . ')';
            $bgColor = Color::RED;
        }

        $this->console->writeLine();
        $this->console->write(
            $line1,
            Color::NORMAL,
            $bgColor
        );
        $this->console->write(' ');
        $this->console->writeLine(
            str_pad($line2, $this->width - 8, ' ', STR_PAD_RIGHT),
            Color::NORMAL,
            $bgColor
        );
        $this->console->writeLine();
    }
}
