<?php

namespace ZFTool\Controller;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Request as ConsoleRequest;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Zend\View\Model\ViewModel;
use ZFTool\Diagnostics\Exception\RuntimeException;
use ZFTool\Diagnostics\Reporter\BasicConsole;
use ZFTool\Diagnostics\Reporter\VerboseConsole;
use ZFTool\Diagnostics\Runner;
use ZFTool\Diagnostics\Test\Callback;
use ZFTool\Diagnostics\Test\TestInterface;
use ZFTool\Options\RequestOptions;

/**
 * Class DiagnosticsController
 *
 * @package ZFTool\Controller
 */
class DiagnosticsController extends AbstractActionController
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
     * @var array
     */
    protected $configuration;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @param AdapterInterface $console
     * @param ModuleGenerator  $moduleGenerator
     */
    function __construct(
        AdapterInterface $console, RequestOptions $requestOptions,
        array $configuration, ModuleManager $moduleManager
    ) {
        // setup dependencies
        $this->console        = $console;
        $this->requestOptions = $requestOptions;
        $this->configuration  = $configuration;
        $this->moduleManager  = $moduleManager;
    }

    /**
     * @return ConsoleModel|ViewModel
     * @throws \ZFTool\Diagnostics\Exception\RuntimeException
     */
    public function runAction()
    {
        // get needed options to shorten code
        $flagVerbose   = $this->requestOptions->getFlagVerbose();
        $flagDebug     = $this->requestOptions->getFlagDebug();
        $flagQuiet     = $this->requestOptions->getFlagQuiet();
        $flagBreak     = $this->requestOptions->getFlagBreak();
        $testGroupName = $this->requestOptions->getTestGroupName();

        // output header
        if (!$flagQuiet) {
            $this->consoleHeader('Starting diagnostics for Zend Framework 2 project');
        }

        // start output
        if (!$flagQuiet) {
            $this->console->writeLine('       => Get basic diag configuration');
        }

        // Get basic diag configuration
        $config = isset($this->configuration['diagnostics']) ? $this->configuration['diagnostics'] : array();

        // start output
        if (!$flagQuiet) {
            $this->console->writeLine('       => Collect diag tests from modules ');
        }

        // Collect diag tests from modules
        $modules = $this->moduleManager->getLoadedModules(false);

        foreach ($modules as $moduleName => $module) {
            if (is_callable(array($module, 'getDiagnostics'))) {
                $tests = $module->getDiagnostics();
                if (is_array($tests)) {
                    $config[$moduleName] = $tests;
                }

                // Exit the loop early if we found test definitions for
                // the only test group that we want to run.
                if ($testGroupName && $moduleName == $testGroupName) {
                    break;
                }
            }
        }

        // Filter array if a test group name has been provided
        if ($testGroupName) {
            $config = array_intersect_key($config, array($testGroupName => 1));
        }

        // start output
        if (!$flagQuiet) {
            $this->console->writeLine('       => Analyze test definitions and construct test instances');
        }

        // Analyze test definitions and construct test instances
        $testCollection = array();
        foreach ($config as $testGroupName => $tests) {
            foreach ($tests as $testLabel => $test) {
                // Do not use numeric labels.
                if (!$testLabel || is_numeric($testLabel)) {
                    $testLabel = false;
                }

                // Handle a callable.
                if (is_callable($test)) {
                    $test = new Callback($test);
                    if ($testLabel) {
                        $test->setLabel($testGroupName . ': ' . $testLabel);
                    }

                    $testCollection[] = $test;
                    continue;
                }

                // Handle test object instance.
                if (is_object($test)) {
                    if (!$test instanceof TestInterface) {
                        throw new RuntimeException(
                            'Cannot use object of class "' . get_class($test). '" as test. '.
                            'Expected instance of ZFTool\Diagnostics\Test\TestInterface'
                        );

                    }

                    if ($testLabel) {
                        $test->setLabel($testGroupName . ': ' . $testLabel);
                    }
                    $testCollection[] = $test;
                    continue;
                }

                // Handle an array containing callback or identifier with optional parameters.
                if (is_array($test)) {
                    if (!count($test)) {
                        throw new RuntimeException(
                            'Cannot use an empty array() as test definition in "'.$testGroupName.'"'
                        );
                    }

                    // extract test identifier and store the remainder of array as parameters
                    $testName = array_shift($test);
                    $params = $test;

                } elseif (is_scalar($test)) {
                    $testName = $test;
                    $params = array();

                } else {
                    throw new RuntimeException(
                        'Cannot understand diagnostic test definition "' . gettype($test). '" in "'.$testGroupName.'"'
                    );
                }

                // Try to expand test identifier using Service Locator
                if (is_string($testName) && $this->getServiceLocator()->has($testName)) {
                    $test = $this->getServiceLocator()->get($testName);

                // Try to use the built-in test class
                } elseif (is_string($testName) && class_exists('ZFTool\Diagnostics\Test\\' . $testName)) {
                    $class = new \ReflectionClass('ZFTool\Diagnostics\Test\\' . $testName);
                    $test = $class->newInstanceArgs($params);

                // Check if provided with a callable inside the array
                } elseif (is_callable($testName)) {
                    $test = new Callback($testName, $params);
                    if ($testLabel) {
                        $test->setLabel($testGroupName . ': ' . $testLabel);
                    }

                    $testCollection[] = $test;
                    continue;

                // Try to expand test using class name
                } elseif (is_string($testName) && class_exists($testName)) {
                    $class = new \ReflectionClass($testName);
                    $test = $class->newInstanceArgs($params);

                } else {
                    throw new RuntimeException(
                        'Cannot find test class or service with the name of "' . $testName . '" ('.$testGroupName.')'
                    );
                }

                if (!$test instanceof TestInterface) {
                    // not a real test
                    throw new RuntimeException(
                        'The test object of class '.get_class($test).' does not implement '.
                        'ZFTool\Diagnostics\Test\TestInterface'
                    );
                }

                // Apply label
                if ($testLabel) {
                    $test->setLabel($testGroupName . ': ' . $testLabel);
                }

                $testCollection[] = $test;
            }
        }

        if (!$flagQuiet) {
            $this->console->writeLine();
            $this->console->write(' Diag ', Color::NORMAL, Color::CYAN);
            $this->console->write(' ');
        }

        // Configure test runner
        $runner = new Runner();
        $runner->addTests($testCollection);
        $runner->getConfig()->setBreakOnFailure($flagBreak);

        if (!$flagQuiet && $this->getRequest() instanceof ConsoleRequest) {
            if ($flagVerbose || $flagDebug) {
                $runner->addReporter(new VerboseConsole($this->console, $flagDebug));
            } else {
                $runner->addReporter(new BasicConsole($this->console));
            }
        }

        // Run tests
        $results = $runner->run();

        // Return result
        if ($this->getRequest() instanceof ConsoleRequest) {
            // Return appropriate error code in console
            $model = new ConsoleModel();
            $model->setVariable('results', $results);

            if ($results->getFailureCount() > 0) {
                $model->setErrorLevel(1);
            } else {
                $model->setErrorLevel(0);
            }
        } else {
            // Display results as a web page
            $model = new ViewModel();
            $model->setVariable('results', $results);
        }

        return $model;
    }

}
