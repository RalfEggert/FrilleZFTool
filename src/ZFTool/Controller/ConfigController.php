<?php

namespace ZFTool\Controller;

use Zend\Config\Writer\Ini as IniWriter;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Mvc\Controller\AbstractActionController;
use ZFTool\Model\Config as ModuleConfig;
use ZFTool\Options\RequestOptions;

/**
 * Class ConfigController
 *
 * @package ZFTool\Controller
 */
class ConfigController extends AbstractActionController
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
     * @param AdapterInterface $console
     * @param ModuleGenerator  $moduleGenerator
     */
    function __construct(
        AdapterInterface $console, RequestOptions $requestOptions
    ) {
        // setup dependencies
        $this->console        = $console;
        $this->requestOptions = $requestOptions;
    }

    /**
     * List configuration
     */
    public function listAction()
    {
        // output header
        $this->consoleHeader('Fetching requested configuration');

        // get needed options to shorten code
        $path      = realpath($this->requestOptions->getPath());
        $flagLocal = $this->requestOptions->getFlagLocal();

        // check for local file
        if ($flagLocal) {
            $configFile = $path . '/config/autoload/local.php';

            // check if local config file exists
            if (!file_exists($configFile)) {
                return $this->sendError(
                    'Local config file ' . $configFile . ' does not exist.'
                );
            }
        } else {
            $configFile = $path . '/config/application.config.php';
        }

        // fetch config data
        $configData = include $configFile;

        // check if local config file exists
        if (empty($configData)) {
            return $this->sendError(
                'Config file ' . $configFile . ' is empty.'
            );
        }

        // start output
        $this->console->write('       => Reading configuration file ');
        $this->console->writeLine($configFile, Color::GREEN);
        $this->console->writeLine();

        // continue output
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');
        $this->console->write('Configuration data');
        $this->console->writeLine(PHP_EOL);

        // output configuration as ini
        $iniWriter = new IniWriter;
        $this->console->writeLine(
            str_pad('', $this->console->getWidth() - 1, '=', STR_PAD_RIGHT)
        );
        $this->console->writeLine(trim($iniWriter->toString($configData)));
        $this->console->writeLine(
            str_pad('', $this->console->getWidth() - 1, '=', STR_PAD_RIGHT)
        );

        // output footer
        $this->consoleFooter('requested configuration was successfully displayed');

    }

    /**
     * Get configuration by key
     */
    public function getAction()
    {
        // output header
        $this->consoleHeader('Fetching requested configuration');

        // get needed options to shorten code
        $path       = realpath($this->requestOptions->getPath());
        $configName = $this->requestOptions->getConfigName();
        $flagLocal  = $this->requestOptions->getFlagLocal();

        // check for config name
        if (!$configName) {
            return $this->sendError(
                'config get <configName> was not provided'
            );
        }

        // check for local file
        if ($flagLocal) {
            $configFile = $path . '/config/autoload/local.php';

            // check if local config file exists
            if (!file_exists($configFile)) {
                return $this->sendError(
                    'Local config file ' . $configFile . ' does not exist.'
                );
            }
        } else {
            $configFile = $path . '/config/application.config.php';
        }

        // fetch config data
        $configData = include $configFile;

        // check if local config file exists
        if (empty($configData)) {
            return $this->sendError(
                'Config file ' . $configFile . ' is empty.'
            );
        }

        // start output
        $this->console->write('       => Reading configuration file ');
        $this->console->writeLine($configFile, Color::GREEN);
        $this->console->writeLine();

        // find value in array
        $configValue = ModuleConfig::findValueInArray($configName, $configData);

        // start output
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');
        $this->console->write('Configuration data for key ');
        $this->console->writeLine($configName, Color::GREEN);
        $this->console->writeLine();

        // check config value
        if (is_array($configValue)) {
            $iniWriter = new IniWriter;
            $this->console->writeLine(
                str_pad('', $this->console->getWidth() - 1, '=', STR_PAD_RIGHT)
            );
            $this->console->writeLine(trim($iniWriter->toString($configValue)));
            $this->console->writeLine(
                str_pad('', $this->console->getWidth() - 1, '=', STR_PAD_RIGHT)
            );
        } elseif (is_null($configValue)) {
            $this->console->writeLine('       => NULL');
        } else {
            $this->console->writeLine('       => ' . $configValue);
        }

        // output footer
        $this->consoleFooter('requested configuration was successfully displayed');

    }

    /**
     * Set configuration by key
     */
    public function setAction()
    {
        // output header
        $this->consoleHeader('Setting requested configuration');

        // get needed options to shorten code
        $path        = realpath($this->requestOptions->getPath());
        $configName  = $this->requestOptions->getConfigName();
        $configValue = $this->requestOptions->getConfigValue();
        $configFile  = $path . '/config/autoload/local.php';

        // check for config name
        if (!$configName) {
            return $this->sendError(
                'config get <configName> was not provided'
            );
        }

        // start output
        $this->console->write('       => Reading configuration file ');
        $this->console->writeLine($configFile, Color::GREEN);
        $this->console->write('       => Changing configuration data for key ');
        $this->console->write($configName, Color::GREEN);
        $this->console->write(' to value ');
        $this->console->writeLine($configValue, Color::GREEN);
        $this->console->write('       => Writing configuration file ');
        $this->console->writeLine($configFile, Color::GREEN);
        $this->console->writeLine();

        // check for value
        if ($configValue === 'null') {
            $configValue = null;
        }

        // write local config file
        $configData = new ModuleConfig($configFile);
        $configData->write($configName, $configValue);

        // continue output
        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');
        $this->console->writeLine('Configuration data was changed.');

        // output footer
        $this->consoleFooter('requested configuration was successfully changed');

    }
}
