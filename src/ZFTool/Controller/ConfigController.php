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
        $this->console->writeLine(
            'Configuration file ' . $configFile,
            Color::GREEN
        );

        // output configuration as ini
        $iniWriter = new IniWriter;
        $this->console->writeLine($iniWriter->toString($configData));
    }

    /**
     * Get configuration by key
     */
    public function getAction()
    {
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

        // find value in array
        $configValue = ModuleConfig::findValueInArray($configName, $configData);

        // start output
        $this->console->writeLine(
            'Configuration file '
            . $configFile,
            Color::GREEN
        );

        $this->console->writeLine(
            'Config Name "' . $configName . '":',
            Color::LIGHT_GREEN
        );

        // check config value
        if (is_array($configValue)) {
            $iniWriter = new IniWriter;
            $this->console->writeLine($iniWriter->toString($configValue));
        } elseif (is_null($configValue)) {
            $this->console->writeLine('NULL');
        } else {
            $this->console->writeLine($configValue);
        }
    }

    /**
     * Set configuration by key
     */
    public function setAction()
    {
        // get needed options to shorten code
        $path        = realpath($this->requestOptions->getPath());
        $configName  = $this->requestOptions->getConfigName();
        $configValue = $this->requestOptions->getConfigValue();

        // check for config name
        if (!$configName) {
            return $this->sendError(
                'config get <configName> was not provided'
            );
        }

        // check for value
        if ($configValue === 'null') {
            $configValue = null;
        }

        // set local config file
        $configFile = $path . '/config/autoload/local.php';

        // write local config file
        $configData = new ModuleConfig($configFile);
        $configData->write($configName, $configValue);

        // start output
        $this->console->writeLine(
            'Configuration file written at ' . $configFile,
            Color::GREEN
        );
    }
}
