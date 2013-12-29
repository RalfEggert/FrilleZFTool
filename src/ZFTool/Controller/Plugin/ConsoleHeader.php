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
class ConsoleHeader extends AbstractPlugin
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
    public function __invoke($msg, $badge = '  Go  ')
    {
        $this->console->writeLine();
        $this->console->writeLine(
            str_pad('', $this->width - 1, ' ', STR_PAD_RIGHT),
            Color::NORMAL,
            Color::GREEN
        );
        $this->console->writeLine(
            str_pad(' ' . Module::NAME, $this->width - 1, ' ', STR_PAD_BOTH),
            Color::NORMAL,
            Color::GREEN
        );
        $this->console->writeLine(
            str_pad('', $this->width - 1, ' ', STR_PAD_RIGHT),
            Color::NORMAL,
            Color::GREEN
        );
        $this->console->writeLine();

        $this->console->write($badge, Color::NORMAL, Color::YELLOW);
        $this->console->write(' ');
        $this->console->writeLine($msg . ' ...');
        $this->console->writeLine();

    }
}
