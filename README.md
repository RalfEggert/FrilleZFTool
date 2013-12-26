  Zend Framework 2 Tool
=========================

This is a **fork** of the official ZFTool module:
https://github.com/zendframework/ZFTool


**ZFTool** is an utility module for maintaining modular Zend Framework 2 applications.
It runs from the command line and can be installed as ZF2 module or as PHAR (see below).

## Features
 * Class-map generator
 * Listing of loaded modules
 * Create a new project (install the ZF2 skeleton application)
 * Create a new module
 * Create a new controller
 * Create a new action in a controller
 * [Application diagnostics](docs/DIAGNOSTICS.md)

## Requirements
 * Zend Framework 2.0.0 or later.
 * PHP 5.3.3 or later.
 * Console access to the application being maintained (shell, command prompt)

## Installation using [Composer](http://getcomposer.org)
 1. Open console (command prompt)
 2. Go to your application's directory.
 3. Run `composer require zendframework/zftool:dev-master`
 4. Execute the `vendor/bin/zf.php` as reported below

## Using the PHAR file (zftool.phar)

 1. Download the [zftool.phar from packages.zendframework.com](http://packages.zendframework.com/zftool.phar)
 2. Execute the `zftool.phar` with one of the options reported below (`zftool.phar` replace the `zf.php`)

You can also generate the zftool.phar using the `bin/create-phar` command as reported below

## Usage

### Basic information

    zf.php modules [list]           show loaded modules
    zf.php version | --version      display current Zend Framework version

### Diagnostics

    zf.php diag [options] [module name]

    [module name]       (Optional) name of module to test
    -v --verbose        Display detailed information.
    -b --break          Stop testing on first failure.
    -q --quiet          Do not display any output unless an error occurs.
    --debug             Display raw debug info from tests.

### Project creation

    zf.php create project <path>

    <path>              The path of the project to be created

### Module creation

    zf.php create module <moduleName> [<path>] [--ignore-conventions|-i] [--no-docblocks|-d]

    <moduleName>                The name of the module to be created
    <path>                      The path to the root folder of the ZF2 application (optional)
    --ignore-conventions | -i   Ignore coding conventions
    --no-docblocks | -d         Prevent the doc block generation

### Controller creation:
	zf.php create controller <controllerName> <moduleName> [<path>] [--ignore-conventions|-i] [--no-config|-n] [--no-docblocks|-d]

	<controllerName>            The name of the controller to be created
	<moduleName>                The module in which the controller should be created
	<path>                      The root path of a ZF2 application where to create the controller
    --ignore-conventions | -i   Ignore coding conventions
    --no-config | -n            Prevent that module configuration is updated
    --no-docblocks | -d         Prevent the doc block generation

### Action creation:
	zf.php create action <actionName> <controllerName> <moduleName> [<path>] [--ignore-conventions|-i] [--no-docblocks|-d]

	<actionName>                The name of the action to be created
	<controllerName>            The name of the controller in which the action should be created
	<moduleName>                The module containing the controller
	<path>                      The root path of a ZF2 application where to create the action
    --ignore-conventions | -i   Ignore coding conventions
    --no-docblocks | -d         Prevent the doc block generation

### Routing creation:
	zf.php create routing <moduleName> [<path>] [--single-route|-s]

	<moduleName>                The module containing the controller
	<path>                      The root path of a ZF2 application where to create the action
    --single-route | -s         Create a single standard route for the module

### Application configuration

    zf.php config list                  list all configuration option
    zf.php config get <name>            display a single config value, i.e. "config get db.host"
    zf.php config set <name> <value>    set a single config value (use only to change scalar values)

### Classmap generator

    zf.php classmap generate <directory> <classmap file> [--append|-a] [--overwrite|-w]

    <directory>         The directory to scan for PHP classes (use "." to use current directory)
    <classmap file>     File name for generated class map file  or - for standard output. If not supplied, defaults to
                        autoload_classmap.php inside <directory>.
    --append | -a       Append to classmap file if it exists
    --overwrite | -w    Whether or not to overwrite existing classmap file

### ZF library installation

    zf.php install zf <path> [<version>]

    <path>              The directory where to install the ZF2 library
    <version>           The version to install, if not specified uses the last available

### Compile the PHAR file

You can create a .phar file containing the ZFTool project. In order to compile ZFTool in a .phar file you need
to execute the following command:

    bin/create-phar

This command will create a *zftool.phar* file in the bin folder.
You can use and ship only this file to execute all the ZFTool functionalities.
After the *zftool.phar* creation, we suggest to add the folder bin of ZFTool in your PATH environment. In this
way you can execute the *zftool.phar* script wherever you are, for instance executing the command:

    mv zftool.phar /usr/local/bin/zftool.phar

Note: If the above fails due to permissions, run the mv line again with sudo.


## Todo for the fork rework
 * Generate all code with Zend\Code [DONE]
 * Make doc block generation optional [DONE]
 * Add basic routing (optional) [DONE]
 * Create Configurator class for configuration changes [DONE]
 * Create plugin for manipulating request parameters [DONE]
 * Create Factory class for given controller [NOT STARTED YET]
 * Add configuration for classmap generation [NOT STARTED YET]
 * Refactor controllers [NOT STARTED YET]
 * Add module inspections (e.g. check Module.php class) [NOT STARTED YET]
 * Write tests for Generator, Configurator, Controller [NOT STARTED YET]
 * Create configuration for Zend\Translate [NOT STARTED YET]
 * Turn module caching on/off, check writable caching dir [NOT STARTED YET]
 * Create Skeletons and configuration for view helper, controller plugin. form, input filter [NOT STARTED YET]

