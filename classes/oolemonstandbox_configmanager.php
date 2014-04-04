<?php

/**
* Config manager
*/
class ooLemonStandBox_ConfigManager
{
	public static function updateConfig()
	{
		if( Phpr::$config->get('LOADED_LEMONSTANDBOX', false) ||  !Phpr::$config->get('ENABLE_LEMONSTANDBOX', TRUE))
			return;	
		
		$configPath = realpath( PATH_APP ."/"."config/config.php" );
		if ( $configPath && is_readable($configPath) && is_writeable($configPath))
			$configFile = file_get_contents($configPath);

		if( $configFile )
		{
			$configFile = preg_replace('/\?>\s*$/', '', $configFile);

			$configFile .= "\n// Added by LemonStandBox DO NOT REMOVE";
			$configFile .= "\n\t\$CONFIG['LOADED_LEMONSTANDBOX'] = true;";
			$configFile .=  "\n\tinclude('" . PATH_PROTOBOX . "/config/config.php');";
			$configFile .=  "\n\tinclude('" . PATH_PROTOBOX . "/config/lemonstandbox.php');";

			file_put_contents($configPath,$configFile);
		}

		// Update modules the first time.
		ooLemonStandBox_UpdateManager::update();
	}
}