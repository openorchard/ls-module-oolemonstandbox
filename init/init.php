<?php
/**
 * Load the modules from protobox directory
 * 
 */

define('PATH_PROTOBOX', realpath(PATH_APP."/.."));
PHPr::$classLoader->add_application_directory(PATH_PROTOBOX);

ooLemonStandBox_ConfigManager::updateConfig();

ooLemonStandBox_ModuleManager::listModules(false);