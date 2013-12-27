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
 * Class ClassmapController
 *
 * @package ZFTool\Controller
 */
class ClassmapController extends AbstractActionController
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
     * Generate class map
     *
     * @return ConsoleModel
     */
    public function generateAction()
    {
        // get needed options to shorten code
        $directory     = $this->requestOptions->getDirectory();
        $destination   = $this->requestOptions->getDestination();
        $relativePath  = '';
        $usingStdout   = false;

        // Validate directory
        if (!is_dir($directory)) {
            return $this->sendError(
                'Invalid library directory provided "' . $directory . '".'
            );
        }

        // check that destination file is not a directory
        if (is_dir($destination)) {
            return $this->sendError(
                'Invalid output file provided.'
            );
        }

        // check that destination file is writable
        if (!is_writeable(dirname($destination))) {
            return $this->sendError(
                'Cannot write to "' . $directory . '".'
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
            $this->console->writeLine(
                'Creating classmap file for library in ' . $directory,
                Color::YELLOW
            );
            $this->console->write('Scanning for files containing PHP classes ');
        }

        // generate new class map
        $classMap = $this->moduleConfigurator->buildClassmapConfig(
            $relativePath
        );

        // Check if we have found any PHP classes.
        if (!$classMap) {
            $this->console->writeLine(' DONE', Color::RED);

            return $this->sendError(
                'Cannot find any PHP classes in "' . $directory . '".'
            );
        } else {
            foreach ($classMap as $file) {
                $this->console->write('.');
            }
        }

        // continue output
        if (!$usingStdout) {
            $this->console->writeLine(' DONE', Color::GREEN);
            $this->console->write('Found ');
            $this->console->write(count($classMap), Color::GREEN);
            $this->console->writeLine(' PHP classes');
            $this->console->write('Creating classmap code ');
            foreach ($classMap as $file) {
                $this->console->write('.');
            }
        }

        // continue output
        if (!$usingStdout) {
            $this->console->writeLine(' DONE', Color::GREEN);
            $this->console->write('Writing classmap to '. $destination);
        }

        // update module configuration
        $this->moduleGenerator->updateConfiguration(
            $classMap, $destination, true
        );

        // end output
        if (!$usingStdout) {
            $this->console->writeLine(' DONE', Color::GREEN);
        }
        // update module class with class map autoloading
        $this->moduleGenerator->updateModuleWithClassmapAutoloader();

        // end output
        if (!$usingStdout) {
            $this->console->writeLine('Wrote classmap to ' . $destination, Color::GREEN);
        }
    }


    /**
     * Send an error message to the console
     *
     * @param  string $msg
     * @return ConsoleModel
     */
    protected function sendError($msg)
    {
        $this->console->writeLine($msg, Color::RED);

        $m = new ConsoleModel();
        $m->setErrorLevel(2);
        $m->setResult('---> aborted' . PHP_EOL);
        return $m;
    }
}
