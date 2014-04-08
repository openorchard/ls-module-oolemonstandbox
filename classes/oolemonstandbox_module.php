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
		
		public function subscribe_events()
		{
			Backend::$events->addEvent('cms:onBeforeResourceCombine', $this, 'combine_resources');
			Backend::$events->addEvent('onLogin', $this, 'on_admin_login');
			Backend::$events->addEvent('backend:onControllerReady', $this, 'backend_controller_ready');
			
			// core:onBeforeSoftwareUpdate
			// core:onAfterSoftwareUpdate
		}		


		public function backend_controller_ready($controller)
		{
			$module_id = $controller->getModuleId();

			if(in_array($module_id, explode('|', 'blog|backend|users|system|shop|session|oolemonstandbox|core|cms'))) return;

			$controller->viewPath = PATH_PROTOBOX .'/modules/'. $module_id .'/controllers/'.strtolower(get_class($controller));

		}
		/**
		 * Admin Login event handler
		 */
		public function on_admin_login()
		{
			ooLemonStandBox_UpdateManager::update();
		}
		/**
		 * Excerpt from original combine resources. 
		 * @param  array $args  argument array
		 * @return string       url or script tag
		 * @see CMS_Controller
		 */
		public function combine_resources($args)
		{
			extract($args);

			$files = Phpr_Util::splat($files);
			
			$current_theme = null;
			if (Cms_Theme::is_theming_enabled() && ($theme = Cms_Theme::get_active_theme()))
				$current_theme = $theme;
			
			$files_array = array();
			foreach ($files as $file)
			{
				$file = trim($file);
				
				if (substr($file, 0, 1) == '@')
				{
					$file = substr($file, 1);
					if (strpos($file, '/') !== 0)
						$file = '/'.$file;

					if ($current_theme)
						$file = '/..' . $theme->get_resources_path().$file;
					else 
						$file = '/'.Cms_SettingsManager::get()->resources_dir_path.$file;
				}
					
				$files_array[] = 'file%5B%5D='. urlencode(trim($file));
			}
				
			$options_str = array();
			foreach ($options as $option=>$value)
			{
				if ($value)
					$options_str[] = $option.'=1';
			}
			
			$options_str = implode('&amp;', $options_str);
			if ($options_str)
				$options_str = '&amp;'.$options_str;
			
			if ($type == 'javascript') {
				$url = root_url('ls_javascript_combine/?'.implode('&amp;', $files_array).$options_str);
				
				return $show_tag ? '<script type="text/javascript" src="'.$url.'"></script>'."\n" : $url;
			}
			else {
				$url = root_url('ls_css_combine/?'.implode('&amp;', $files_array).$options_str);
				
				return $show_tag ? '<link rel="stylesheet" type="text/css" href="'.$url.'" />' : $url;
			}

		}
		
		/*
		 * Awaiting Deprecation
		 */
		// public function listSettingsItems()
		// {
		// 	return $this->list_settings_items();
		// }

		protected function createModuleInfo()
		{
			return $this->create_module_info();
		}
		
		public function subscribeEvents()
		{
			return $this->subscribe_events();
		}


	}
