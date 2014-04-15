<?php
/**
 * Load the modules from protobox directory
 * 
 */

define('PATH_PROTOBOX', realpath(PATH_APP."/.."));
PHPr::$classLoader->add_application_directory(PATH_PROTOBOX);

ooLemonStandBox_Module::configure();

ooLemonStandBox_ModuleManager::listModules(false);

