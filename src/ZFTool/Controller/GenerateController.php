<?php

namespace ZFTool\Controller;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Version;
use Zend\View\Model\ConsoleModel;
use ZFTool\Generator\ModuleGenerator;
use ZFTool\Generator\ModuleConfigurator;
use ZFTool\Options\RequestOptions;

/**
 * Class GenerateController
 *
 * @package ZFTool\Controller
 */
class GenerateController extends AbstractActionController
{
    /**
     * @var AdapterInterface
     */
    protected $console;

    /**
     * @var RequestOptions
     */
    protected $requestOptions;

    /**
     * @var ModuleGenerator
     */
    protected $moduleGenerator;

    /**
     * @var ModuleConfigurator
     */
    protected $moduleConfigurator;

    /**
     * @param AdapterInterface $console
     * @param ModuleGenerator  $moduleGenerator
     */
    function __construct(
        AdapterInterface $console, RequestOptions $requestOptions,
        ModuleGenerator $moduleGenerator, ModuleConfigurator $moduleConfigurator
    ) {
        // setup dependencies
        $this->console            = $console;
        $this->requestOptions     = $requestOptions;
        $this->moduleGenerator    = $moduleGenerator;
        $this->moduleConfigurator = $moduleConfigurator;
    }

    /**
     * Generate classmap
     *
     * @return ConsoleModel
     */
    public function classmapAction()
    {
        // check for help mode
        if ($this->requestOptions->getFlagHelp()) {
            return $this->classmapHelp();
        }

        // output header
        $this->consoleHeader('Generating classmap');

        // get needed options to shorten code
        $directory     = $this->requestOptions->getDirectory();
        $destination   = $this->requestOptions->getDestination();
        $relativePath  = '';
        $usingStdout   = false;

        // check if directory provided
        if (is_null($directory)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Please provide the directory to create the classmap in'),
                )
            );
        }

        // Validate directory
        if (!is_dir($directory)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Invalid library directory provided '),
                    array(Color::RED    => $directory),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        // check that destination file is not a directory
        if (is_dir($destination)) {
            return $this->sendError(
                array(
                    array(Color::RED    => $destination),
                    array(Color::NORMAL => ' is not a valid output file.'),
                )
            );
        }

        // check that destination file is writable
        if (!is_writeable(dirname($destination))) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Cannot write to '),
                    array(Color::RED    => $destination),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        // Determine output
        if ('-' == $destination) {
            $destination = STDOUT;
            $usingStdout = true;
        } else {
            // We need to add the $libraryPath into the relative path that is created in the classmap file.
            $classmapPath = str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname($destination)));

            // Simple case: $libraryPathCompare is in $classmapPathCompare
            if (strpos($directory, $classmapPath) === 0) {
                if ($directory !== $classmapPath) { // prevent double dash in filepaths when using "." as directory
                    $relativePath = substr($directory, strlen($classmapPath) + 1) . '/';
                }
            } else {
                $libraryPathParts  = explode('/', $directory);
                $classmapPathParts = explode('/', $classmapPath);

                // Find the common part
                $count = count($classmapPathParts);
                for ($i = 0; $i < $count; $i++) {
                    if (!isset($libraryPathParts[$i]) || $libraryPathParts[$i] != $classmapPathParts[$i]) {
                        // Common part end
                        break;
                    }
                }

                // Add parent dirs for the subdirs of classmap
                $relativePath = str_repeat('../', $count - $i);

                // Add library subdirs
                $count = count($libraryPathParts);
                for (; $i < $count; $i++) {
                    $relativePath .= $libraryPathParts[$i] . '/';
                }
            }
        }

        // start output
        if (!$usingStdout) {
            $this->console->writeLine('       => Scanning for files containing PHP classes ');
        }

        // generate new classmap
        $classMap = $this->moduleConfigurator->buildClassmapConfig(
            $relativePath
        );

        // Check if we have found any PHP classes.
        if (!$classMap) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'Cannot find any PHP classes in '),
                    array(Color::RED    => $directory),
                    array(Color::NORMAL => '.'),
                )
            );
        }

        // continue output
        if (!$usingStdout) {
            $this->console->write('       => Found ');
            $this->console->write(count($classMap), Color::GREEN);
            $this->console->writeLine(' PHP classes');
            $this->console->writeLine('       => Writing classmap');
        }

        // update module configuration
        $this->moduleGenerator->updateConfiguration(
            $classMap, $destination, true
        );

        // update module class with classmap autoloading
        $updateFlag = $this->moduleGenerator->updateModuleWithClassmapAutoloader();

        // continue output
        if (!$usingStdout) {
            if ($updateFlag) {
                $this->console->writeLine('       => Update module class to use classmap for autoloading');
            } else {
                $this->console->writeLine();
                $this->console->write(' Warn ', Color::NORMAL, Color::RED);
                $this->console->write(' ');
                $this->console->writeLine('=> Could not find a module class in directory to update the autoloading for the new classmap', Color::RED);
            }
        }

        // end output
        if (!$usingStdout) {
            $this->console->writeLine();
            $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
            $this->console->write(' ');
            $this->console->write('Wrote classmap to ');
            $this->console->writeLine($destination, Color::GREEN);
        }

        // output footer
        $this->consoleFooter('classmap was successfully generated');

    }

    /**
     * Generate a Classmap help
     */
    public function classmapHelp()
    {
        // output header
        $this->consoleHeader('Generate a Classmap for a directory / module', ' Help ');

        $this->console->writeLine(
            '       zf.php generate classmap <directory> [<destination>]',
            Color::GREEN
        );

        $this->console->writeLine();

        $this->console->writeLine('       Parameters:');
        $this->console->writeLine();
        $this->console->write(
            '       <directory>     ',
            Color::CYAN
        );
        $this->console->writeLine(
            'Directory to scan for PHP classes (use "." to use current directory).',
            Color::NORMAL
        );
        $this->console->write(
            '       [<destination>] ',
            Color::CYAN
        );
        $this->console->writeLine(
            '(Optional) File name for class map file or - for standard output.',
            Color::NORMAL
        );
        $this->console->writeLine(
            '                       Defaults to autoload_classmap.php inside <directory>.',
            Color::NORMAL
        );

        // output footer
        $this->consoleFooter('requested help was successfully displayed');

    }
}
