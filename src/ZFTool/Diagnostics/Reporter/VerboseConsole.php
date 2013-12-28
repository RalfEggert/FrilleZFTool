<?php
namespace ZFTool\Diagnostics\Reporter;

use ZFTool\Diagnostics\Result\Failure;
use ZFTool\Diagnostics\Result\Success;
use ZFTool\Diagnostics\Result\Warning;
use ZFTool\Diagnostics\RunEvent;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Stdlib\StringUtils;

class VerboseConsole extends AbstractReporter
{
    /**
     * @var \Zend\Console\Adapter\AdapterInterface
     */
    protected $console;

    protected $width = 80;
    protected $total = 0;
    protected $iter = 1;
    protected $countLength;
    protected $displayData = false;
    protected $stopped = false;

    public function __construct(Console $console, $displayData = false)
    {
        $this->console = $console;
        $this->stringUtils = StringUtils::getWrapper();
        $this->displayData = $displayData;
    }

    public function onStart(RunEvent $e)
    {
        $this->stopped = false;
        $this->width = $this->console->getWidth();
        $this->total = count($e->getParam('tests'));

        $this->console->writeLine('Running diagnostics:');
        $this->console->writeLine('');
    }

    public function onAfterRun(RunEvent $e)
    {
        $test = $e->getTarget();
        $result = $e->getLastResult();

        $descr = ' ' . $test->getLabel();
        if ($message = $result->getMessage()) {
            $descr .= ': ' . $result->getMessage();
        }

        if ($this->displayData && ($data = $result->getData())) {
            $descr .= PHP_EOL . str_repeat('-', $this->width - 15);
            $data = $result->getData();
            if(is_object($data) && $data instanceof \Exception){
                $descr .= PHP_EOL . get_class($data) . PHP_EOL . $data->getMessage() . $data->getTraceAsString();
            }else{
                $descr .= PHP_EOL . @var_export($result->getData(), true);
            }

            $descr .= PHP_EOL . str_repeat('-', $this->width - 15);
        }

        // Draw status line
        if ($result instanceof Success) {
            $this->console->write('       ');
            $this->console->write('  OK  ', Color::NORMAL, Color::GREEN);
            $this->console->writeLine(
                $this->strColPad(
                    $descr,
                    $this->width - 15,
                    '              '
                ), Color::GREEN
            );
        } elseif ($result instanceof Failure) {
            $this->console->write('       ');
            $this->console->write(' FAIL ', Color::WHITE, Color::RED);
            $this->console->writeLine(
                $this->strColPad(
                    $descr,
                    $this->width - 15,
                    '              '
                ), Color::RED
            );
        } elseif ($result instanceof Warning) {
            $this->console->write('       ');
            $this->console->write(' WARN ', Color::NORMAL, Color::YELLOW);
            $this->console->writeLine(
                $this->strColPad(
                    $descr,
                    $this->width - 15,
                    '              '
                ), Color::YELLOW
            );
        } else {
            $this->console->write('       ');
            $this->console->write(' ???? ', Color::NORMAL, Color::YELLOW);
            $this->console->writeLine(
                $this->strColPad(
                    $descr,
                    $this->width - 7,
                    '              '
                ), Color::YELLOW
            );
        }
        $this->console->writeLine();
    }

    public function onFinish(RunEvent $e)
    {
        /* @var $results \ZFTool\Diagnostics\Result\Collection */
        $results = $e->getResults();

        // Display information that the test has been aborted.
        if ($this->stopped) {
            $this->console->writeLine('Diagnostics aborted because of a failure.', Color::RED);
        }


        // Display a summary line
        if ($results->getFailureCount() == 0 && $results->getWarningCount() == 0 && $results->getUnknownCount() == 0) {
            $this->console->write(
                '  OK  ',
                Color::NORMAL,
                Color::GREEN
            );
            $this->console->write(' ');
            $this->console->writeLine(
                str_pad(' (' . $this->total . ' diagnostic tests)', $this->width - 8, ' ', STR_PAD_RIGHT),
                Color::NORMAL,
                Color::GREEN
            );
        } elseif ($results->getFailureCount() == 0) {
            $line = ' (' . $results->getWarningCount() . ' warnings, ';
            $line .= $results->getSuccessCount() . ' successful tests';

            if ($results->getUnknownCount() > 0) {
                $line .= ', ' . $results->getUnknownCount() . ' unknown test results';
            }

            $line .= ')';

            $this->console->write(
                ' WARN ',
                Color::NORMAL,
                Color::YELLOW
            );
            $this->console->write(' ');
            $this->console->writeLine(
                str_pad($line, $this->width - 8, ' ', STR_PAD_RIGHT),
                Color::NORMAL, Color::YELLOW
            );
        } else {
            $line = ' (' . $results->getFailureCount() . ' failures, ';
            $line .= $results->getWarningCount() . ' warnings, ';
            $line .= $results->getSuccessCount() . ' successful tests';

            if ($results->getUnknownCount() > 0) {
                $line .= ', ' . $results->getUnknownCount() . ' unknown test results';
            }

            $line .= ')';

            $this->console->write(
                ' FAIL ',
                Color::NORMAL,
                Color::RED
            );
            $this->console->write(' ');
            $this->console->writeLine(
                str_pad($line, $this->width - 8, ' ', STR_PAD_RIGHT),
                Color::NORMAL, Color::RED
            );
        }

        $this->console->writeLine();

    }

    public function onStop(RunEvent $e)
    {
        $this->stopped = true;
    }


    /**
     * @param \Zend\Console\Adapter\AdapterInterface $console
     */
    public function setConsole($console)
    {
        $this->console = $console;

        // Update width
        $this->width = $console->getWidth();
    }

    /**
     * @return \Zend\Console\Adapter\AdapterInterface
     */
    public function getConsole()
    {
        return $this->console;
    }

    public function setDisplayData($displayData)
    {
        $this->displayData = $displayData;
    }

    public function getDisplayData()
    {
        return $this->displayData;
    }



    public function strColPad($string, $width, $padding)
    {
        $string = $this->stringUtils->wordWrap($string, $width, PHP_EOL, true);
        $lines = explode(PHP_EOL, $string);
        for ($x = 1; $x < count($lines); $x++) {
            $lines[$x] = $padding . $lines[$x];
        }

        return join(PHP_EOL, $lines);
    }


}
