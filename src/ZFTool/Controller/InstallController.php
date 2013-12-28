<?php

namespace ZFTool\Controller;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Zend\Mvc\Controller\AbstractActionController;
use ZFTool\Model\Utility;
use ZFTool\Model\Zf;
use ZFTool\Options\RequestOptions;

/**
 * Class InstallController
 *
 * @package ZFTool\Controller
 */
class InstallController extends AbstractActionController
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
     * @return mixed
     */
    public function zfAction()
    {
        // output header
        $this->consoleHeader('Installing Zend Framework 2 library');

        // check for zip extension
        if (!extension_loaded('zip')) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'You need to install the ZIP extension of PHP.'),
                )
            );
        }

        // get needed options to shorten code
        $path    = $this->requestOptions->getPath();
        $tmpDir  = $this->requestOptions->getTmpDir();
        $version = $this->requestOptions->getVersion();

        // check if path exists
        if (file_exists($path)) {
            return $this->sendError(
                array(
                    array(Color::NORMAL => 'The directory '),
                    array(Color::RED    => realpath($path)),
                    array(Color::NORMAL => ' already exists. '),
                    array(Color::NORMAL => 'You cannot install the ZF2 library here.'),
                )
            );
        }

        // check version
        if (empty($version)) {
            $version = Zf::getLastVersion();
            if (false === $version) {
                return $this->sendError (
                    array(
                        array(Color::NORMAL => 'I cannot connect to the Zend Framework website.'),
                    )
                );
            }
        } else {
            if (!Zf::checkVersion($version)) {
                return $this->sendError (
                    array(
                        array(Color::NORMAL => 'The specified ZF version, '),
                        array(Color::RED    => $version),
                        array(Color::NORMAL => ' does not exist.'),
                    )
                );
            }
        }

        // get tmp file and check it
        $tmpFile = ZF::getTmpFileName($tmpDir, $version);
        if (!file_exists($tmpFile)) {
            if (!Zf::downloadZip($tmpFile, $version)) {
                return $this->sendError (
                    array(
                        array(Color::NORMAL => 'I cannot download the ZF2 library from GitHub.'),
                    )
                );
            }
        }

        // unzip archive
        $zip = new \ZipArchive;
        if ($zip->open($tmpFile)) {
            $zipFolders = $zip->statIndex(0);
            $zipFolder = $tmpDir . '/' . rtrim($zipFolders['name'], "/");

            if (!$zip->extractTo($tmpDir)) {
                return $this->sendError(
                    array(
                        array(Color::NORMAL => 'Error during the unzip of '),
                        array(Color::RED    => $tmpFile),
                    )
                );
            }

            $result = Utility::copyFiles($zipFolder, $path);
            if (file_exists($zipFolder)) {
                Utility::deleteFolder($zipFolder);
            }

            $zip->close();

            if (false === $result) {
                return $this->sendError(
                    array(
                        array(Color::NORMAL => 'Error during the copy of the files in '),
                        array(Color::RED    => realpath($path)),
                    )
                );
            }
        }

        $this->console->write(' Done ', Color::NORMAL, Color::CYAN);
        $this->console->write(' ');

        $this->console->write('The ZF library ');
        $this->console->write($version, Color::GREEN);
        $this->console->write(' has been installed in ');
        $this->console->writeLine(realpath($path), Color::GREEN);

        // output footer
        $this->consoleFooter('library was successfully installed');

    }
}
