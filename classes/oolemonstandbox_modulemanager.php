<?php

/**
* OpenOrchard Module Manager
*/
class ooLemonStandBox_ModuleManager extends Core_ModuleManager
{
	
	/**
	 * Returns a list of modules
	 *
	 * 	Excerpt of the original code.
	 * 	
	 *  TODO: Improve beyond original by removing extra code.
	 *  
	 * @return array
	 */
	public static function listModules($allow_caching = true, $return_disabled_only = false)
	{
		if( !ooLemonStandBox_Module::$configured ) return;
		
		global $Phpr_DisableEvents;
		
		$Phpr_DisableEvents = isset($Phpr_DisableEvents) && $Phpr_DisableEvents;
		
		if ($allow_caching && !$return_disabled_only)
		{
			if ( self::$_modules !== null )
				return self::$_modules;
		}

		if (!$return_disabled_only && self::$_modules == null )
				self::$_modules = array();

		$disabled_module_list = array();

		$disabledModules = Phpr::$config->get('DISABLE_MODULES', array());

		//Modifies module path to point to /web/modules
		$modulesPath = PATH_PROTOBOX . '/modules';

		$iterator = new DirectoryIterator( $modulesPath );
		foreach ( $iterator as $dir )
		{
			if ( $dir->isDir() && !$dir->isDot() )
			{
				$dirPath = $modulesPath."/".$dir->getFilename();
				$moduleId = $dir->getFilename();

				$disabled = in_array($moduleId, $disabledModules);

				if (($disabled && !$return_disabled_only) || (!$disabled && $return_disabled_only))
					continue;

				if ( isset(self::$_modules[$moduleId]) )
					continue;

				$modulePath = $dirPath."/classes/".$moduleId."_module.php";

				if (!file_exists($modulePath))
					continue;

				if ( Phpr::$classLoader->load($className = $moduleId."_Module", true) )
				{
					if ($disabled)
						$disabled_module_list[$moduleId] = new $className($return_disabled_only);
					else
					{
						self::$_modules[$moduleId] = new $className($return_disabled_only);
						
						if (!Backend::$events->events_disabled && !$Phpr_DisableEvents)
							self::$_modules[$moduleId]->subscribeEvents();
					}
				}
			}
		}

		self::$_eventsSubscribed = true;

		if ($return_disabled_only)
		{
			$result = $disabled_module_list;
			uasort( $result, array('Core_ModuleManager', 'compareModuleInfo') );
		}
		else
		{
			$result = self::$_modules;
			uasort( $result, array('Core_ModuleManager', 'compareModuleInfo') );
			self::$_modules = $result;
		}

		return $result;
	}


}