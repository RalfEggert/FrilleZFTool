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

#### Display current Zend Framework 2 version

    zf.php version [<path>]
    zf.php --version [<path>]

    [<path>]            (Optional) path to a ZF2 application

#### Show all modules within a ZF2 application

    zf.php modules [<path>]

    [<path>]            (Optional) path to a ZF2 application

#### Show all controllers for a module

    zf.php controllers <module_name> [<path>]

    <module_name>       Name of module
    [<path>]            (Optional) path to a ZF2 application

#### Show all actions for a controller in a module

    zf.php actions <module_name> <controller_name> [<path>]

    <module_name>       Name of module
    <controller_name>   Name of controller
    [<path>]            (Optional) path to a ZF2 application

### Diagnostics

    zf.php diag [<test_group_name>] [options]

    [<test_group_name>] (Optional) name of module to test
    --verbose | -v      Display detailed information.
    --break   | -b      Stop testing on first failure.
    --quiet   | -q      Do not display any output unless an error occurs.
    --debug   | -d      Display raw debug info from tests.

### Creation

#### Project creation

    zf.php create project <path>

    <path>              Path of the project to be created

#### Module creation

    zf.php create module <module_name> [<path>] [options]

    <module_name>       Name of module to be created
    [<path>]            (Optional) path to a ZF2 application
    --ignore  | -i      Ignore coding conventions
    --apidocs | -a      Prevent the api doc block generation

#### Controller creation:

	zf.php create controller <controller_name> <module_name> [<path>] [options]

	<controller_name>   Name of controller to be created
	<module_name>       Module in which controller should be created
	[<path>]            (Optional) path to a ZF2 application
    --factory | -f      Create a factory for the controller
    --ignore  | -i      Ignore coding conventions
    --config  | -c      Prevent that module configuration is updated
    --apidocs | -a      Prevent the api doc block generation

#### Controller factory creation:

	zf.php create controller-factory <controller_name> <module_name> [<path>] [options]

	<controller_name>   Name of controller the factory has to be created
	<module_name>       Module in which the controller factory should be created
	[<path>]            (Optional) path to a ZF2 application
    --config  | -c      Prevent that module configuration is updated
    --apidocs | -a      Prevent the api doc block generation

#### Action creation:

	zf.php create action <action_name> <controller_name> <module_name> [<path>] [options]

	<action_name>       Name of action to be created
	<controller_name>   Name of controller in which action should be created
	<module_name>       Module containing the controller
	[<path>]            (Optional) path to a ZF2 application
    --ignore  | -i      Ignore coding conventions
    --apidocs | -a      Prevent the api doc block generation

#### Routing creation:

	zf.php create routing <module_name> [<path>] [options]

	<module_name>       Name of module to create the routing for
	[<path>]            (Optional) path to a ZF2 application
    --single  | -s      Create single standard route for the module

### Application configuration

#### List all configuration option

    zf.php config list [<path>] [options]

    [<path>]            (Optional) path to a ZF2 application
    --local   | -l      Use local configuration file

#### Display a single config value

    zf.php config get <config_name> [<path>] [options]

    <config_name>       Configuration key, i.e. db.host
    [<path>]            (Optional) path to a ZF2 application
    --local   | -l      Use local configuration file

#### Set a single config value (to change scalar values in local configuration file)

    zf.php config set <config_name> <config_value> [<path>]

    <config_name>       Configuration key, i.e. db.host
    <config_value>      Configuration value, i.e. localhost
    [<path>]            (Optional) path to a ZF2 application

### Generate a Classmap for a directory / module

    zf.php generate classmap <directory> [<destination>]

    <directory>         Directory to scan for PHP classes (use "." to use current directory)
    [<destination>]     (Optional) File name for class map file or - for standard output.
                        Defaults to autoload_classmap.php inside <directory>.

### ZF library installation

    zf.php install <path> [<version>]

    <path>              Path where to install the ZF2 library
    [<version>]         (Optional) Version to install, defaults to the last version available

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
 * Generate all code with `Zend\Code` [DONE]
 * Make doc block generation optional [DONE]
 * Add basic routing (optional) [DONE]
 * Create Configurator class for configuration changes [DONE]
 * Create plugin for manipulating request parameters [DONE]
 * Create Factory class for given controller [DONE]
 * Add configuration for classmap generation [DONE]
 * Refactor controllers [DONE]
 * Re-organize help page [DONE]
 * Create skeleton classes and configuration for  [IN PROGRESS]
   * view helpers
   * controller plugins
   * forms
   * input filters
   * hydrators
 * Write tests for [NOT STARTED YET]
   * generator
   * configurator
   * controller
   * option class
 * Create skeleton configuration for [NOT STARTED YET]
   * `Zend\Translate`
 * Turn module caching on/off, check writable caching dir [NOT STARTED YET]
 * Add module inspections  [NOT STARTED YET]
   * Ensuring that `Module.php` has a zero-argument constructor
   * Ensuring that `getConfig()` returns serializable config
   * Ensuring that `Module.php` does not retain global state (no statics)
   * Ensuring that config returned by `get[A-Za-z]+Config()` produces arrays compatible with `Zend\ServiceManager\Config`
 * to be continued
