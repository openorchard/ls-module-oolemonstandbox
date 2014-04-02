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
			$configFile = fopen($configPath, 'a+');

		if( $configFile )
		{

			fwrite($configFile, "\n// Added by LemonStandBox DO NOT REMOVE");
			fwrite($configFile, "\n\t\$CONFIG['LOADED_LEMONSTANDBOX'] = true;");
			fwrite($configFile, "\n\tinclude('" . PATH_PROTOBOX . "/config/config.php');");
			fwrite($configFile, "\n\tinclude('" . PATH_PROTOBOX . "/config/lemonstandbox.php');");

			fclose($configFile);
		}
	}
}