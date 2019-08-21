<?php 

// Required helpers,config, composer loader...
require_once APPPATH."/core/Autoloader.php";
require_once APPPATH."/helpers/helpers.php";
require_once APPPATH."/config/config.php";
require_once BASEPATH."/vendor/autoload.php";

// instantiate the loader
$loader = new Autoloader;

// register the autoloader
$loader->register();

// register the base directories for auto loading
$loader->addBaseDir(APPPATH.'/libraries');
$loader->addBaseDir(APPPATH.'/core');
$loader->addBaseDir(APPPATH.'/controllers');
$loader->addBaseDir(APPPATH.'/models');