<?php
namespace ZFTool\Generator;

use Zend\Code\Generator\AbstractGenerator;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\Exception\RuntimeException as GeneratorException;
use Zend\Code\Generator\ValueGenerator;
use Zend\Code\Reflection\FileReflection;

/**
 * Class ModuleGenerator
 *
 * @package ZFTool\Generator
 */
class ModuleGenerator
{
    /**
     * @var boolean
     */
    protected $createDocBlocks = true;

    /**
     * @param boolean $createDocBlocks
     */
    public function setCreateDocBlocks($createDocBlocks)
    {
        $this->createDocBlocks = $createDocBlocks;
    }

    /**
     * @param $description
     *
     * @return Tag
     */
    protected function createPackageTag($description)
    {
        return new Tag(
            array(
                'name'        => 'package',
                'description' => $description,
            )
        );
    }

    /**
     * @param $description
     *
     * @return Tag
     */
    protected function createReturnTag($description)
    {
        return new Tag(
            array(
                'name'        => 'return',
                'description' => $description,
            )
        );
    }

    /**
     * @return Tag
     */
    protected function createSeeTag()
    {
        return new Tag(
            array(
                'name'        => 'see',
                'description' => 'https://github.com/RalfEggert/ZFTool',
            )
        );
    }

    /**
     * Create the getConfig() method
     *
     * @return MethodGenerator
     */
    protected function createGetConfigMethod()
    {
        // create method body
        $body = new ValueGenerator;
        $body->initEnvironmentConstants();
        $body->setValue(
            'include __DIR__ . \'/config/module.config.php\''
        );

        // create method
        $method = new MethodGenerator();
        $method->setName('getConfig');
        $method->setBody(
            'return ' . $body->generate() . ';'
            . AbstractGenerator::LINE_FEED
        );

        // add optional doc block
        if ($this->createDocBlocks) {
            $method->setDocBlock(
                new DocBlockGenerator(
                    'Get module configuration',
                    null,
                    array(
                        $this->createReturnTag('array'),
                    )
                )
            );
        }

        return $method;
    }

    /**
     * Create the getAutoloaderConfig() method
     *
     * @return MethodGenerator
     */
    protected function createGetAutoloaderConfigMethod()
    {
        // create method body
        $body = new ValueGenerator;
        $body->initEnvironmentConstants();
        $body->setValue(
            array(
                'Zend\Loader\StandardAutoloader' => array(
                    'namespaces' => array(
                        '__NAMESPACE__ => __DIR__ . \'/src/\' . __NAMESPACE__',
                    ),
                ),
            )
        );

        // create method
        $method = new MethodGenerator();
        $method->setName('getAutoloaderConfig');
        $method->setBody(
            'return ' . $body->generate() . ';'
            . AbstractGenerator::LINE_FEED
        );

        // add optional doc block
        if ($this->createDocBlocks) {
            $method->setDocBlock(
                new DocBlockGenerator(
                    'Get autoloader configuration',
                    null,
                    array(
                        $this->createReturnTag('array'),
                    )
                )
            );
        }

        return $method;
    }

    /**
     * Create the Module.php content
     *
     * @param  string $name
     *
     * @return string
     */
    public function createModule($moduleName, $moduleFile)
    {
        // create controller class with class generator
        $code = new ClassGenerator();
        $code->setNamespaceName($moduleName);
        $code->setName('Module');
        $code->addMethodFromGenerator($this->createGetConfigMethod());
        $code->addMethodFromGenerator($this->createGetAutoloaderConfigMethod());

        // add optional doc block
        if ($this->createDocBlocks) {
            $code->setDocBlock(
                new DocBlockGenerator(
                    'Module',
                    'Please add a proper description for the '
                    . $moduleName . ' module',
                    array(
                        $this->createPackageTag($moduleName),
                    )
                )
            );
        }

        // create file with file generator
        $file = new FileGenerator();
        $file->setClass($code);

        // add optional doc block
        if ($this->createDocBlocks) {
            $file->setDocBlock(
                new DocBlockGenerator(
                    'This file was generated by ZFTool.',
                    null,
                    array(
                        $this->createPackageTag($moduleName),
                        $this->createSeeTag(),
                    )
                )
            );
        }

        // write controller class
        if (!file_put_contents($moduleFile, $file->generate())) {
            return false;
        }

        return true;
    }

    /**
     * @param array $configData
     * @param       $configFile
     *
     * @return bool
     */
    public function createConfiguration(array $configData, $configFile)
    {
        // create config array
        $array = new ValueGenerator();
        $array->initEnvironmentConstants();
        $array->setValue($configData);
        $array->setArrayDepth(0);

        // create file with file generator
        $file = new FileGenerator();
        $file->setBody(
            'return ' . $array->generate() . ';' . AbstractGenerator::LINE_FEED
        );

        // add optional doc block
        if ($this->createDocBlocks) {
            $file->setDocBlock(
                new DocBlockGenerator(
                    'Configuration file generated by ZFTool',
                    null,
                    array(
                        $this->createSeeTag(),
                    )
                )
            );
        }

        // write application configuration
        if (!file_put_contents($configFile, $file->generate())) {
            return false;
        }

        return true;
    }

    /**
     * Create the action method
     *
     * @return MethodGenerator
     */
    protected function createActionMethod($methodName)
    {
        // create method
        $method = new MethodGenerator();
        $method->setName($methodName);
        $method->setBody(
            'return new ViewModel();'
        );

        // add optional doc block
        if ($this->createDocBlocks) {
            $method->setDocBlock(
                new DocBlockGenerator(
                    'Method ' . $methodName,
                    'Please add a proper description for this action',
                    array(
                        $this->createReturnTag('ViewModel'),
                    )
                )
            );
        }

        return $method;
    }

    /**
     * @param $controllerClass
     * @param $moduleName
     * @param $controllerFilePath
     *
     * @return bool
     */
    public function createController(
        $controllerClass, $moduleName, $controllerFilePath
    ) {
        // create controller class with class generator
        $code = new ClassGenerator();
        $code->setNamespaceName($moduleName . '\Controller');
        $code->addUse('Zend\Mvc\Controller\AbstractActionController');
        $code->addUse('Zend\View\Model\ViewModel');
        $code->setName($controllerClass);
        $code->setExtendedClass('AbstractActionController');
        $code->addMethodFromGenerator($this->createActionMethod('indexAction'));

        // add optional doc block
        if ($this->createDocBlocks) {
            $code->setDocBlock(
                new DocBlockGenerator(
                    'Class ' . $controllerClass,
                    'Please add a proper description for the ' . $controllerClass,
                    array(
                        $this->createPackageTag($moduleName),
                    )
                )
            );
        }

        // create file with file generator
        $file = new FileGenerator();
        $file->setClass($code);

        // add optional doc block
        if ($this->createDocBlocks) {
            $file->setDocBlock(
                new DocBlockGenerator(
                    'This file was generated by ZFTool.',
                    null,
                    array(
                        $this->createPackageTag($moduleName),
                        $this->createSeeTag(),
                    )
                )
            );
        }

        // write controller class
        if (!file_put_contents($controllerFilePath, $file->generate())) {
            return false;
        }

        return true;
    }

    /**
     * @param $actionName
     * @param $controllerName
     * @param $moduleName
     * @param $viewPath
     *
     * @return bool
     */
    public function createViewScript(
        $actionName, $controllerName, $moduleName, $viewFile
    ) {
        // setup view script body
        $viewBody   = array();
        $viewBody[] = '?>';
        $viewBody[] = '<div class="jumbotron">';
        $viewBody[] = '<h1>Action "' . $actionName . '"</h1>';
        $viewBody[] = '<p>Created for Controller "' . $controllerName
            . '" in Module "' . $moduleName . '"</p>';
        $viewBody[] = '</div>';

        // create file with file generator
        $file = new FileGenerator();
        $file->setBody(
            implode(AbstractGenerator::LINE_FEED, $viewBody)
        );

        // add optional doc block
        if ($this->createDocBlocks) {
            $file->setDocBlock(
                new DocBlockGenerator(
                    'View script generated by ZFTool',
                    null,
                    array(
                        $this->createPackageTag($moduleName),
                    )
                )
            );
        }

        // write view script
        if (!file_put_contents($viewFile, $file->generate())) {
            return false;
        }

        return true;
    }

    /**
     * @param array $configData
     * @param       $configFile
     *
     * @return bool
     */
    public function updateConfiguration(array $configData, $configFile)
    {
        // set old file
        $oldFile = str_replace('.php', '.old', $configFile);

        // copy to old file
        copy($configFile, $oldFile);

        // create config array
        $array = new ValueGenerator();
        $array->initEnvironmentConstants();
        $array->setValue($configData);
        $array->setArrayDepth(0);

        // create file with file generator
        $file = new FileGenerator();
        $file->setBody(
            'return ' . $array->generate() . ';' . AbstractGenerator::LINE_FEED
        );

        // add optional doc block
        if ($this->createDocBlocks) {
            $file->setDocBlock(
                new DocBlockGenerator(
                    'Configuration file generated by ZFTool',
                    'The previous configuration file is stored in ' . $oldFile,
                    array(
                        $this->createSeeTag(),
                    )
                )
            );
        }

        // write application configuration
        if (!file_put_contents($configFile, $file->generate())) {
            return false;
        }

        return true;
    }

    /**
     * @param $actionMethod
     * @param $controllerKey
     * @param $moduleName
     * @param $controllerFilePath
     *
     * @return bool
     * @throws \Zend\Code\Generator\Exception
     */
    public function updateController(
        $actionMethod, $controllerKey, $moduleName, $controllerFilePath
    ) {
        // get file and class reflection
        $fileReflection  = new FileReflection(
            $controllerFilePath,
            true
        );
        $classReflection = $fileReflection->getClass(
            $controllerKey . 'Controller'
        );

        // setup class generator with reflected class
        $code = ClassGenerator::fromReflection($classReflection);

        // check for action method
        if ($code->hasMethod($actionMethod)) {
            throw new GeneratorException(
                'New action already exists within controller'
            );
        }

        // fix namespace usage
        $code->addUse('Zend\Mvc\Controller\AbstractActionController');
        $code->addUse('Zend\View\Model\ViewModel');
        $code->setExtendedClass('AbstractActionController');
        $code->addMethodFromGenerator($this->createActionMethod($actionMethod));

        // create file with file generator
        $file = new FileGenerator();
        $file->setClass($code);

        // add optional doc block
        if ($this->createDocBlocks) {
            $file->setDocBlock(
                new DocBlockGenerator(
                    'Configuration file was generated by ZFTool.',
                    null,
                    array(
                        $this->createPackageTag($moduleName),
                        $this->createSeeTag(),
                    )
                )
            );
        }

        // write controller class
        if (!file_put_contents($controllerFilePath, $file->generate())) {
            return false;
        }

        return true;
    }
}
