<?php

	class ooLemonStandBox_Module extends Core_ModuleBase
	{

		/**
		 * Creates the module information object
		 * @return Core_ModuleInfo
		 */
		protected function create_module_info()
		{
			return new Core_ModuleInfo(
				"ooLemonStandBox",
				"LemonStand Protobox companion module to help with development",
				"OpenOrchard" );
		}
		
		

		/*
		 * Awaiting Deprecation
		 */
		public function listSettingsItems()
		{
			return $this->list_settings_items();
		}

		protected function createModuleInfo()
		{
			return $this->create_module_info();
		}
		
		public function subscribeEvents()
		{
			return $this->subscribe_events();
		}


	}
