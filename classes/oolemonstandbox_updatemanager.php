<?php

/**
* LemonStandBox Update Manager
*/
class ooLemonStandBox_UpdateManager extends db_UpdateManager
{
	/**
	 * Updates all development modules
	 */
	public static function update()
	{
		self::createMetadata();

		$db_updated = false;

		/*
		 * Update development modules
		 */
		$modules = ooLemonStandBox_ModuleManager::listModules(false);
		$module_ids = array();
		
		foreach ($modules as $module)
		{
			$id = mb_strtolower($module->getModuleInfo()->id);
			$module_ids[$id] = 1;
		}
		
		$sequence = array_flip(Phpr::$config->get('UPDATE_SEQUENCE', array()));
		if (count($sequence))
		{
			$updated_module_ids = $sequence;
			foreach ($module_ids as $module_id=>$value)
			{
				if (!array_key_exists($module_id, $sequence))
					$updated_module_ids[$module_id] = 1;
			}
			
			$module_ids = $updated_module_ids;
		}

		$module_ids = array_keys($module_ids);
		foreach ($module_ids as $module_id)
		{
			$module_updated = self::updateModule($module_id,PATH_PROTOBOX);
			$db_updated = $db_updated || $module_updated;
		}
			
		if ($db_updated)
			Db_ActiveRecord::clear_describe_cache();
	}
}