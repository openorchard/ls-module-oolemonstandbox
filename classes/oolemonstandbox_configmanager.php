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

	}

	public static function updateCMS()
	{

		if(Phpr::$config->get('CMS_FILEBASED_TEMPLATES', false) && !Cms_SettingsManager::get()->enable_filebased_templates)
		{

			Cms_SettingsManager::get()
							  ->save(array(	'enable_filebased_templates'=>	Phpr::$config->get('CMS_FILEBASED_TEMPLATES')
										,	'templates_dir_path'		=>	Phpr::$config->get('TEMPLATE_PATH')
										,	'content_file_extension'	=>	Phpr::$config->get('CMS_CONTENT_FILE_EXT', 'php')
										,	'resources_dir_path'		=>	Phpr::$config->get('CMS_RESOURCES_DIR', 'resources' )
										)
									);
			
			$res_dir 	= Cms_SettingsManager::get()->resources_dir_path;
			$theme_dir	= Cms_SettingsManager::get()->templates_dir_path;
			$themes 	= Cms_Theme::list_themes();
			foreach ($themes as $theme)
			{
				$og_res  = PATH_APP . '/themes/'.$theme->code.'/' . $res_dir;
				$new_res = $theme_dir . '/'.$theme->code.'/' . $res_dir;

				Phpr_Files::copyDir($og_res,$new_res);
			}
		}
	}
}